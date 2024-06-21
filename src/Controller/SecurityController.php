<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use App\Entity\Restaurant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(
        AuthenticationUtils $authenticationUtils,
        EntityManagerInterface $entityManager,
        Request $request): Response
    {
        // si user ou admin connecté
        if ($this->getUser()) 
        {
            if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) 
            {
                return $this->redirectToRoute('admin_profil'); 
            } 
            else 
            {
                return $this->redirectToRoute('mon_profil'); 
            }
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

         // declaration du restaurant pour le footer									
         $restaurant = $entityManager->getRepository(Restaurant::class)->find(1);

        // return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
        return $this->render('home/client_connexion.html.twig', 
        [
            'last_username' => $lastUsername, 
            'error' => $error,
            'restaurant' => $restaurant,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
