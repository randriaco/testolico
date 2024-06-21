<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Commande;
use App\Entity\Place;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

use Psr\Log\LoggerInterface;

class CompteController extends AbstractController
{
    #[Route('/compte', name: 'compte')]
    public function index(Request $request, CommandeRepository $commandeRepository): Response
    {
        $onglet = $request->query->get('onglet');
        $data = [];
    
        if ($onglet == 'mes_commandes') 
        {
            $user = $this->getUser(); // Assurez-vous que cette méthode récupère l'utilisateur connecté
            $data['commandes'] = $commandeRepository->findByUser($user);
        }
    
        // Ajoutez d'autres conditions ici pour d'autres onglets si nécessaire
    
        return $this->render('compte.html.twig', array_merge(
            ['onglet' => $onglet],
            $data
        ));
    }
    
    #[Route('/mon-profil', name: 'mon_profil')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function updateProfil(
            Request $request,
            EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        // if (!$user) 
        // {
        //     return $this->redirectToRoute('app_login');
        // }

        if ($request->isMethod('POST')) 
        {          
           /** @var \App\Entity\User $user */
            // Mise à jour de l'entité User avec les données reçues
           $user->setNom($request->request->get('nom'))
           ->setPrenom($request->request->get('prenom'))
           ->setTelephone($request->request->get('telephone'));

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 
            'Votre profil a été modifié avec succès.');

            // Rediriger l'utilisateur vers la route souhaitée
            return $this->redirectToRoute('mon_profil'); 
        }

        return $this->render('client/profil.html.twig', 
        [
            'user' => $user
        ]);
    }

    #[Route('/modifier-mot-de-passe', name: 'modifier_mot_de_passe')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function modifierPass(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher, // Utilisez UserPasswordHasherInterface ici
        EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user instanceof PasswordAuthenticatedUserInterface) 
        {
            throw new \LogicException('L\'utilisateur actuel n\'est pas un PasswordAuthenticatedUserInterface');
        }

        // ---------------------debut --------------

        if ($request->isMethod('POST')) 
        {
            $currentPassword = $request->request->get('currentPassword');
            $newPassword = $request->request->get('newPassword');
            $confirmNewPassword = $request->request->get('confirmNewPassword');

            // Vérifiez d'abord si les champs du nouveau mot de passe et de sa confirmation ne sont pas vides
            if (empty($newPassword) || empty($confirmNewPassword)) 
            {
                $this->addFlash('danger', 'Le nouveau mot de passe et la confirmation du mot de passe ne peuvent pas être vides.');
                return $this->redirectToRoute('mon_profil');
            }

            // Ensuite, vérifiez si les nouveaux mots de passe correspondent
            if ($newPassword !== $confirmNewPassword) 
            {
                $this->addFlash('danger', 'Les nouveaux mots de passe ne correspondent pas.');
                return $this->redirectToRoute('mon_profil');
            }

            // Continuer si les nouveaux mots de passe correspondent et le mot de passe actuel est valide

            if (!($passwordHasher->isPasswordValid($user, $currentPassword)))
            {
                $this->addFlash('danger', 'Le mot de passe actuel est incorrect.');
                return $this->redirectToRoute('mon_profil');                               
            } 
            else 
            {
                /** @var \App\Entity\User $user */
                $newEncodedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($newEncodedPassword);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 
                'Votre mot de passe a été modifié avec succès.');

                return $this->redirectToRoute('mon_profil');
            }
        }

        // -------------------------fin -----------------

        return $this->render('client/profil.html.twig', 
        [
            'user' => $user,
        ]);
    }

    #[Route('/mes-commandes', name: 'mes_commandes')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function mesCommandes( EntityManagerInterface $entityManager, CommandeRepository $commandeRepository): Response
    {
        $user = $this->getUser(); // Récupère l'utilisateur connecté

        // $commandes = $commandeRepository->findByUser($user); // Récupère les commandes de l'utilisateur
        
        // Récupère les commandes de l'utilisateur triées par date de réservation
        $commandes = $entityManager->getRepository(Commande::class)->findBy(
            ['user' => $user],
            ['dateReservation' => 'ASC'] // Tri par date de réservation en ordre croissant
        );

        return $this->render('client/mes_commandes.html.twig', 
        [
            'commandes' => $commandes, 
        ]);
    }


    #[Route('/commande/{id}', name: 'commande_detail')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function detail(int $id, CommandeRepository $commandeRepository): Response
    {
        $commande = $commandeRepository->find($id);

        if (!$commande) {
            throw $this->createNotFoundException('La commande demandée n\'existe pas.');
        }

        return $this->render('client/commande_detail.html.twig', [
            'commande' => $commande,
        ]);
    }
    
    #[Route('/mes_reservations', name: 'mes_reservations')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function mesReservations(ReservationRepository $reservationRepository)
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser(); // Assurez-vous que votre système d'authentification est configuré pour retourner l'utilisateur actuel
        $userId = $user->getId();

        $reservations = $reservationRepository->findReservationsByUser($userId);

        return $this->render('client/mes_reservations.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/annuler-reservation/{id}', name: 'annuler_reservation')]
    public function annulerReservation(
        Request $request, 
        EntityManagerInterface $entityManager, 
        $id, 
        bool $setFlashMessage = true): Response
    {
        // Récupération de la réservation à partir de son ID
        $reservation = $entityManager->getRepository(Reservation::class)->find($id);

        // Vérification si la réservation existe et appartient à l'utilisateur courant
        if (!$reservation || $reservation->getUser() !== $this->getUser()) 
        {
            $this->addFlash('danger', 'Réservation non trouvée ou accès non autorisé.');
            return $this->redirectToRoute('mes_reservations');
        }

        // Récupération de l'entité Place correspondante à la date de la réservation
        $place = $entityManager->getRepository(Place::class)->findOneBy(['date' => $reservation->getDateReservation()]);

        // Vérifier si l'entité Place existe pour cette date
        if ($place) 
        {
            // Ajuster le nombre de places réservées et disponibles
            $nombreCouverts = $reservation->getNombreCouverts();
            $place->setPlacesReservees(max(0, $place->getPlacesReservees() - $nombreCouverts));
            $place->setPlacesDispo($place->getPlacesDispo() + $nombreCouverts);
        }

        // Suppression de la réservation
        $entityManager->remove($reservation);
        $entityManager->flush();

        // Ajout d'un message flash pour notifier l'utilisateur de la suppression
        // $this->addFlash('success-reservation', 'La réservation a été annulée avec succès.');

        if ($setFlashMessage) 
        {
            // Ajout d'un message flash pour notifier l'utilisateur de la suppression
            $this->addFlash('success-reservation', 
            'La réservation a été annulée avec succès.');
        }

        // Redirection vers la liste des réservations ou une autre page pertinente
        return $this->redirectToRoute('mes_reservations');
    }

    #[Route('/modifier_reservation/{id}', name: 'modifier_reservation')]
    public function modifierReservation(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        // Annuler d'abord la réservation existante
        // Note : Cette étape suppose que l'annulation ne supprime pas définitivement la réservation mais la marque comme annulée
        $reservation = $entityManager->getRepository(Reservation::class)->find($id);

        if (!$reservation || $reservation->getUser() !== $this->getUser()) 
        {
            $this->addFlash('danger', 
            'Réservation non trouvée ou vous n\'avez pas le droit de la modifier.');

            return $this->redirectToRoute('mes_reservations');
        }

        // Ici, vous pouvez choisir d'annuler la réservation sans la supprimer définitivement, par exemple :
        // $reservation->setStatus('annulée');
        // $entityManager->flush();

        // Ou appeler la méthode annulerReservation si elle supprime la réservation
        $this->annulerReservation($request, $entityManager, $id, false);

        // Ajouter un message flash pour notifier l'utilisateur que la modification est en cours
        $this->addFlash('success-reservation', 
        'La modification de votre réservation a été effectué avec succès');

        // Rediriger l'utilisateur vers le formulaire de réservation avec les détails de la réservation précédente pour modification
        // Vous devez passer l'ID de la réservation ou les détails pertinents en tant que paramètres si nécessaire
        return $this->redirectToRoute('reserver', ['id' => $id]);
    }
}