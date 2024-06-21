<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\PanierService;
use Symfony\Component\Mailer\MailerInterface;

use App\Entity\Categorie;
use App\Entity\Commande;
use App\Entity\Produit;
use App\Entity\Restaurant;
use App\Entity\CommandeProduit;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Stripe\Stripe;
use Stripe\Checkout\Session as CheckoutSession;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class CommandeController extends AbstractController
{
    private $panierService;
    private $router;

    public function __construct(PanierService $panierService, UrlGeneratorInterface $router)
    {
        $this->panierService = $panierService;
        $this->router = $router;
    }

    // -------------------------commandes -----------------------------

    #[Route('/details-commandes-emporter', name: 'details_commandes_emporter')]
    public function showReservationsWithCommandes(EntityManagerInterface $em): Response
    {
        // Utilise la méthode personnalisée du repository pour obtenir les réservations futures
        $commandes = $em->getRepository(Commande::class)->findByStatus('a emporter');

        return $this->render('admin/details_commandes_emporter.html.twig', 
		[
            'commandes' => $commandes
        ]);
    }

    #[Route('/details-commandes-emporter/{date}', name: 'details_commandes_emporter_date')]
    public function showCommandesEmporter(string $date, EntityManagerInterface $em): JsonResponse
    {        
        $dateObj = new \DateTime($date);
        $commandes = $em->getRepository(Commande::class)->findByDateAndStatus($dateObj, 'a emporter');

        $responseData = [];
        foreach ($commandes as $commande) 
        {
            $produitDetails = [];
            foreach ($commande->getCommandeProduits() as $produit) 
            {
                $produitDetails[] = 
                [
                    'nomProduit' => $produit->getProduit()->getNom(),
                    'quantite' => $produit->getQuantite(),
                    'prixUnitaire' => $produit->getPrix(),
                    'prixTotal' => $produit->getQuantite() * $produit->getPrix()
                ];
            }

            $responseData[] =
            [
                'nom' => $commande->getUser()->getNom(),
                'prenom' => $commande->getUser()->getPrenom(),
                'dateCommande' => $commande->getDate()->format('Y-m-d'),
                'montantCommande' => $commande->getMontant() . ' €',
                'detailsCommande' => $produitDetails
            ];
        }

        return new JsonResponse($responseData);
    }


    // ---------------------commande a emporter sans reservation---------------------

    #[Route('/commande-a-emporter', name: 'commande_emporter')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]	
    public function commandeEmporter(EntityManagerInterface $entityManager): Response
    {
        $categories = $entityManager->getRepository(Categorie::class)->findBy([], ['nom' => 'ASC']);

        $restaurant = $entityManager->getRepository(Restaurant::class)->find(1);

        return $this->render('home/commande_emporter.html.twig', 
        [
            'categories' => $categories,
            'restaurant' => $restaurant,

        ]); 
    }

    // ----------------------commande à emporter : afficher produits par categorie ----------------

    #[Route('/commande-produits-{nomCategorie}', name: 'afficher_produits_par_categorie_commande')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function afficherProduitsParCategorieCommande(
        string $nomCategorie, 
        EntityManagerInterface $entityManager): Response
    {
        $categorie = $entityManager->getRepository(Categorie::class)->findOneBy(['nom' => $nomCategorie]);
        $produits = $entityManager->getRepository(Produit::class)->findBy(['categorie' => $categorie]);

        $restaurant = $entityManager->getRepository(Restaurant::class)->find(1);

        return $this->render('home/produits_par_categorie_commande.html.twig', 
        [
            'produits' => $produits,
            'categorie' => $categorie,
            'restaurant' => $restaurant,
        ]);
    }

    // ------------------------------- panier -----------------------------------

    #[Route('/panier-commande', name: 'panier_afficher_commande')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function afficherPanier(EntityManagerInterface $entityManager,): Response
    {
        $contenuPanier = $this->panierService->getContenuPanier();
        $totalGeneral = $this->panierService->getTotalPanier();
 
        $restaurant = $entityManager->getRepository(Restaurant::class)->find(1);

        return $this->render('home/afficher_panier_commande.html.twig', 
        [
            'contenuPanier' => $contenuPanier,
            'totalGeneral' => $totalGeneral,
            'restaurant' => $restaurant,
        ]);
    }

    // ----------------------stripe : paiement -------------------------------
    
    #[Route('/creer-session-paiement-commande', name: 'creer_session_paiement_commande')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function creerSessionPaiement(PanierService $panierService): JsonResponse
    {
        //Stripe::setApiKey('votre_clé_secrète_stripe');
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
        $urlSucces = $this->router->generate('paiement_reussi_commande', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $urlEchec = $this->router->generate('paiement_echec_commande', [], UrlGeneratorInterface::ABSOLUTE_URL);

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
    
    #[Route('/paiement-reussi-commande', name: 'paiement_reussi_commande')]
    public function paiementReussiCommande(
        Request $request,
        EntityManagerInterface $em, 
        PanierService $panierService,
        MailerInterface $mailer): Response
    {
        $user = $this->getUser();
        if (!$user) 
        {
            return $this->redirectToRoute('app_login');
        }

        $contenuPanier = $panierService->getContenuPanier();
        if (empty($contenuPanier)) 
		{
            $this->addFlash('danger', 'Votre panier est vide.');
            return $this->redirectToRoute('panier');
        }

        $contenuPanier = $panierService->getContenuPanier();
        $totalGeneral = $panierService->getTotalPanier();
        $totalCommande = 0; // Initialiser la variable totalCommande

        $commande = new Commande();
        $commande->setUser($user);
        $commande->setDate(new \DateTime());
        $commande->setStatus('a emporter'); 
        $commande->setMontant($panierService->getTotalPanier());

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

        $panierService->viderPanier();
		
		// -------------envoi email--------------
		
		/** @var \App\Entity\User $user */
        $email = (new TemplatedEmail())
        ->from('votre@adresse.email')
        ->to($user->getEmail())
        ->subject('Confirmation de votre commande')
        ->htmlTemplate('client/email_detail_commande_emporter.html.twig')
        ->context([
            'commande' => $commande,
            'contenuPanier' => $contenuPanier,
            'totalCommande' => $totalCommande,
        ]);

        $mailer->send($email);
		
        $restaurant = $em->getRepository(Restaurant::class)->find(1);
		
        return $this->render('home/paiement_reussi_commande.html.twig', 
		[
            'commande' => $commande,
            'restaurant' => $restaurant,
        ]);
    }

    #[Route('/paiement-echec-commande', name: 'paiement_echec_commande')]
    public function paiementEchec(EntityManagerInterface $em)
    {
        
        $restaurant = $em->getRepository(Restaurant::class)->find(1);

        // Logique pour gérer un paiement échoué
        return $this->render('home/paiement_echec_commande.html.twig', 
        [
            'restaurant' => $restaurant,
        ]);
    }
}