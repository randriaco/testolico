<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Entity\Produit;
use App\Entity\Commande;
use App\Entity\Categorie;
use App\Entity\Restaurant;
use App\Entity\Reservation;

use App\Service\PanierService;
use App\Entity\CommandeProduit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Stripe\Checkout\Session as CheckoutSession;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationController extends AbstractController
{
    
    // -----------------constructeur --------------------------------

    private $panierService;
    private $router;

    public function __construct(PanierService $panierService, UrlGeneratorInterface $router)
    {
        $this->panierService = $panierService;
        $this->router = $router;
    }
    
    // -------------------------reservations -----------------------------

    #[Route('/reservations-details', name: 'reservations_details')]
    public function showReservationsWithCommandes(EntityManagerInterface $em): Response
    {
        // Utilise la méthode personnalisée du repository pour obtenir les réservations futures
        $reservations = $em->getRepository(Reservation::class)->findAllFutureReservations();

        return $this->render('admin/reservations_details.html.twig', 
		[
            'reservations' => $reservations
        ]);
    }

    #[Route('/reservations-details/{date}', name: 'reservations_details_date')]
    public function showReservationsByDate(string $date, EntityManagerInterface $em): JsonResponse
    {
        $dateObj = new \DateTime($date);
        $reservations = $em->getRepository(Reservation::class)->findByDate($dateObj);

        $responseData = [];
        foreach ($reservations as $reservation) 
        {
            $commande = $reservation->getCommande();
            $commandeProduitsDetails = [];
            if ($commande) 
            {
                foreach ($commande->getCommandeProduits() as $produit) 
                {
                    $commandeProduitsDetails[] = 
                    [
                        'nomProduit' => $produit->getProduit()->getNom(), // Assurez-vous que votre entité Produit a un getter getNom()
                        'quantite' => $produit->getQuantite(),
                        'prixUnitaire' => $produit->getPrix(),
                        'prixTotal' => $produit->getQuantite() * $produit->getPrix()
                    ];
                }
            }

            $responseData[] = 
            [
                'nom' => $reservation->getUser()->getNom(),
                'prenom' => $reservation->getUser()->getPrenom(),
                'dateReservation' => $reservation->getDateReservation()->format('Y-m-d'),
                'horaireReservation' => $reservation->getHoraireReservation(),
                'nombreCouverts' => $reservation->getNombreCouverts(),
                'montantCommande' => $commande ? $commande->getMontant() : 'Pas de commande',
                'detailsCommande' => $commandeProduitsDetails
            ];
        }

        return new JsonResponse($responseData);  
    }

    // ---------------------------reservation avec commande sur place ----------------------------

    #[Route('/commande-sur-place', name: 'commande_sur_place')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]	
    public function commandeSurPlace(EntityManagerInterface $entityManager): Response
    {
        $categories = $entityManager->getRepository(Categorie::class)->findBy([], ['nom' => 'ASC']);

        $restaurant = $entityManager->getRepository(Restaurant::class)->find(1);

        // return $this->render('home/categories.html.twig', 
        return $this->render('home/commande_sur_place.html.twig', 
        [
            'categories' => $categories,
            'restaurant' => $restaurant,

        ]); 
    }

    // -------------- reservation avec commande : afficher les produits par categorie -------------------
    
    #[Route('/reservation-produits-{nomCategorie}', name: 'afficher_produits_par_categorie_reservation')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function afficherProduitsParCategorieReservation(
        string $nomCategorie, 
        EntityManagerInterface $entityManager): Response
    {
        $categorie = $entityManager->getRepository(Categorie::class)->findOneBy(['nom' => $nomCategorie]);
        $produits = $entityManager->getRepository(Produit::class)->findBy(['categorie' => $categorie]);

        $restaurant = $entityManager->getRepository(Restaurant::class)->find(1);

        return $this->render('home/produits_par_categorie_reservation.html.twig', 
        [
            'produits' => $produits,
            'categorie' => $categorie,
            'restaurant' => $restaurant,
        ]);
    }

    // -------------------------------- panier ----------------------------------

    #[Route('/panier-reservation', name: 'panier_afficher_reservation')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function afficherPanier(EntityManagerInterface $entityManager,): Response
    {
        $contenuPanier = $this->panierService->getContenuPanier();
        $totalGeneral = $this->panierService->getTotalPanier();
 
        $restaurant = $entityManager->getRepository(Restaurant::class)->find(1);

        // return $this->render('home/afficher_panier_tableau.html.twig', 
        return $this->render('home/afficher_panier_reservation.html.twig', 
        [
            'contenuPanier' => $contenuPanier,
            'totalGeneral' => $totalGeneral,
            'restaurant' => $restaurant,
        ]);
    }

    // ----------------------------paiement stripe -------------------------------------

    #[Route('/creer-session-paiement', name: 'creer_session_paiement')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function creerSessionPaiement(PanierService $panierService): JsonResponse
    {
        Stripe::setApiKey('sk_test_51KG0R8B70WhTmRhmtnjAaylND1ngWwYozes0xzcDaTswo3LHbbcFzEqrzNlEiNA8uT15muemkhKGENo1SxUgIMsy00WTJxx7p8');

        $contenuPanier = $panierService->getContenuPanier();
        $totalPanier = $panierService->getTotalPanier(); // Assurez-vous que cette méthode renvoie le total en centimes

        $lineItems = [];

        foreach ($contenuPanier as $item) 
        {
            $lineItems[] = 
            [
                'price_data' => 
                [
                    'currency' => 'eur',
                    'product_data' => 
                    [
                        'name' => $item['produit']->getNom(),
                    ],
                    'unit_amount' => $item['produit']->getPrix() * 100, // Assurez-vous que le prix est en centimes
                ],
                'quantity' => $item['quantite'],
            ];
        }

        // generation url pour mode developpement
        $urlSucces = $this->router->generate('paiement_reussi', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $urlEchec = $this->router->generate('paiement_echec', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $session = CheckoutSession::create(
        [
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',

            // 'success_url' => 'https://votre-domaine.com/succes',
            // 'cancel_url' => 'https://votre-domaine.com/echec',

            'success_url' => $urlSucces,
            'cancel_url' => $urlEchec,
        ]);

        return new JsonResponse(['id' => $session->id]);
    }

    #[Route('/paiement-reussi', name: 'paiement_reussi')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function paiementReussi(
        Request $request,
        EntityManagerInterface $em, 
        PanierService $panierService,
        MailerInterface $mailer): Response
    {
        $user = $this->getUser(); // Assurez-vous d'avoir un utilisateur connecté

        if (!$user) 
        {
            // Gérer le cas où l'utilisateur n'est pas connecté si nécessaire
            return $this->redirectToRoute('app_login');
        }

        // Récupérer la reservationId de la session
        $reservationId = $request->getSession()->get('reservationId');
        if (!$reservationId) 
        {
            // Gérer le cas où reservationId n'est pas trouvé en session
            $this->addFlash('danger', 'Une erreur est survenue. Veuillez réessayer.');
            return $this->redirectToRoute('home');
        }

        $reservation = $em->getRepository(Reservation::class)->find($reservationId);
        if (!$reservation) 
        {
            // Gérer le cas où la réservation n'existe pas ou plus en base de données
            $this->addFlash('danger', 'Réservation non trouvée.');
            return $this->redirectToRoute('home');
        }
    
        $contenuPanier = $panierService->getContenuPanier();
        $totalGeneral = $panierService->getTotalPanier();
        $totalCommande = 0; // Initialiser la variable totalCommande
    
        // Création de la commande
        $commande = new Commande();
        $commande->setUser($user);
        $commande->setReservation($reservation);
        $commande->setDate(new \DateTime());
        $commande->setStatus('sur place'); 
        $commande->setMontant($totalGeneral);
        // enregistrement date de reservation
        $commande->setDateReservation($reservation->getDateReservation());
    
        // Ajouter chaque produit du panier à la commande
        foreach ($contenuPanier as $item) 
        {
            // ---------debut ajout -------------
            $produit = $item['produit'];
            $quantite = $item['quantite'];
            $prix = $produit->getPrix();
            $montant = $prix * $quantite;
            $totalCommande += $montant; // Calcul du total de la commande
            // ---------fin ajout -------------

            $commandeProduit = new CommandeProduit();
            $commandeProduit->setCommande($commande);
            $commandeProduit->setProduit($item['produit']);
            $commandeProduit->setQuantite($item['quantite']);
            $commandeProduit->setPrix($item['produit']->getPrix());
            $em->persist($commandeProduit);
        }
    
        $em->persist($commande);
        $em->flush();

        // ------------envoi email de confirmation reservation et commande --------------
        
        /** @var \App\Entity\User $user */
        $email = (new TemplatedEmail())
        ->from('votre@adresse.email')
        ->to($user->getEmail())
        ->subject('Confirmation de votre Réservation et Commande')
        ->htmlTemplate('client/email_detail_commande.html.twig')
        ->context([
            'commande' => $commande,
            'contenuPanier' => $contenuPanier,
            'totalCommande' => $totalCommande,
            // ----------detail reservation --------------
            'dateReservation' => $reservation->getDateReservation()->format('d/m/Y'),
            'horaireReservation' => $reservation->getHoraireReservation(),
            'nombreCouverts' => $reservation->getNombreCouverts(),
        ]);

        $mailer->send($email);
    
        // Vider le panier après enregistrement de la commande
        $panierService->viderPanier();

        // Supprimer reservationId de la session
        $request->getSession()->remove('reservationId');

        $restaurant = $em->getRepository(Restaurant::class)->find(1);
    
        // Afficher la page de succès avec un message approprié
        return $this->render('home/paiement_reussi.html.twig', 
        [
            'commande' => $commande,
            'restaurant' => $restaurant,
        ]);
    }


    #[Route('/paiement-echec', name: 'paiement_echec')]
    public function paiementEchec(EntityManagerInterface $em)
    {
        
        $restaurant = $em->getRepository(Restaurant::class)->find(1);

        // Logique pour gérer un paiement échoué
        return $this->render('home/paiement_echec.html.twig', 
        [
            'restaurant' => $restaurant,
        ]);
    }
}