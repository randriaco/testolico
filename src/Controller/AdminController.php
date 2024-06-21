<?php

namespace App\Controller;

use App\Entity\Information;
use App\Entity\Restaurant;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_profil')]
    // #[IsGranted('ROLE_ADMIN')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $restaurant = $entityManager->getRepository(Restaurant::class)->find(1);
        $information = $entityManager->getRepository(Information::class)->find(1);
        
        return $this->render('admin/profil.html.twig', 
        [
            'restaurant' => $restaurant,
            'information' => $information
        ]);
    }

    #[Route('/admin-profil-restaurant', name: 'admin_profil_restaurant')]
    #[IsGranted('ROLE_ADMIN')]
    public function modifierProfilRestaurant(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Puisqu'il n'y a qu'un seul restaurant, vous pouvez le récupérer directement
        $restaurant = $entityManager->getRepository(Restaurant::class)->find(1); // Supposons id=1 pour simplifier
        $information = $entityManager->getRepository(Information::class)->find(1);
    
        if ($request->isMethod('POST')) 
        {
            // Mise à jour de l'entité Restaurant avec les valeurs du formulaire
            $restaurant->setNom($request->request->get('nom'))
                            ->setAdresse($request->request->get('adresse'))
                            ->setCode($request->request->get('code'))
                            ->setVille($request->request->get('ville'))
                            ->setTelephone($request->request->get('telephone'))
                            ->setEmail($request->request->get('email'))
                            ->setSite($request->request->get('site'));
    
            $entityManager->flush();
    
            $this->addFlash('success', 'La modification du profil du restaurant a été mise à jour avec succès.');
    
            return $this->redirectToRoute('admin_profil_restaurant');
        }
    
        return $this->render('admin/profil.html.twig', [
            'restaurant' => $restaurant,
            'information' => $information,
        ]);
    }

    #[Route('/admin-profil-information', name: 'admin_profil_information')]
    #[IsGranted('ROLE_ADMIN')]
    public function modifierProfilInformation(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Supposons qu'il n'y a qu'une seule instance d'Information (id = 1 pour l'exemple)
        $information = $entityManager->getRepository(Information::class)->find(1);
        $restaurant = $entityManager->getRepository(Restaurant::class)->find(1);

        if (!$information) 
        {
            // Gérer le cas où l'information n'existe pas encore
            $information = new Information();
        }

        if ($request->isMethod('POST')) 
        {
            
            $information->setMessage($request->request->get('message'))
                        ->setDescription($request->request->get('description'))
                        ->setSpecialite($request->request->get('specialite'))
                        ->setTransport($request->request->get('transport'))
                        ->setParking($request->request->get('parking'))
                        ->setLangues($request->request->get('langues'))
                        ->setPaiement($request->request->get('paiement'));

            $entityManager->persist($information);
            $entityManager->flush();

            $this->addFlash('success', 'La modification du profil Information a été mises à jour avec succès.');

            return $this->redirectToRoute('admin_profil_information');
        }

        return $this->render('admin/profil.html.twig', 
        [
            'information' => $information,
            'restaurant' => $restaurant,
            
        ]);
    }

    #[Route('/admin-modifier-mot-de-passe', name: 'admin_modifier_mot_de_passe')]
    #[IsGranted('ROLE_ADMIN')]
    public function modifierPass(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher, // Utilisez UserPasswordHasherInterface ici
        EntityManagerInterface $entityManager): Response
    {
        
        $restaurant = $entityManager->getRepository(Restaurant::class)->find(1);
        $information = $entityManager->getRepository(Information::class)->find(1);
        
        $user = $this->getUser();

        if (!$user instanceof PasswordAuthenticatedUserInterface) 
        {
            throw new \LogicException('L\'utilisateur actuel n\'est pas un PasswordAuthenticatedUserInterface');
        }

        if ($request->isMethod('POST')) 
        {
            $currentPassword = $request->request->get('currentPassword');
            $newPassword = $request->request->get('newPassword');
            $confirmNewPassword = $request->request->get('confirmNewPassword');

            // Vérifiez d'abord si les champs du nouveau mot de passe et de sa confirmation ne sont pas vides
            if (empty($newPassword) || empty($confirmNewPassword)) 
            {
                $this->addFlash('danger', 'Le nouveau mot de passe et la confirmation du mot de passe ne peuvent pas être vides.');
                return $this->redirectToRoute('admin_profil');
            }

            // Ensuite, vérifiez si les nouveaux mots de passe correspondent
            if ($newPassword !== $confirmNewPassword) 
            {
                $this->addFlash('danger', 'Les nouveaux mots de passe ne correspondent pas.');
                return $this->redirectToRoute('admin_profil');
            }

            // Continuer si les nouveaux mots de passe correspondent et le mot de passe actuel est valide

            if (!($passwordHasher->isPasswordValid($user, $currentPassword)))
            {
                $this->addFlash('danger', 'Le mot de passe actuel est incorrect.');
                return $this->redirectToRoute('admin_profil');                               
            } 
            else 
            {
                /** @var \App\Entity\User $user */
                $newEncodedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($newEncodedPassword);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Votre mot de passe a été modifié avec succès.');
                return $this->redirectToRoute('admin_profil');
            }
        }

        return $this->render('admin/profil.html.twig', 
        [
            'user' => $user,
            'information' => $information,
            'restaurant' => $restaurant,
        ]);
    }
}
