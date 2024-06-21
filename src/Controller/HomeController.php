<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\Restaurant;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;

use Twig\Environment as TwigEnvironment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $restaurant = $entityManager->getRepository(Restaurant::class)->find(1);
        
        return $this->render('home/index.html.twig', 
        [
            'restaurant' => $restaurant,
        ]);
    }

    #[Route('/client-connexion', name: 'client_connexion')]
    public function clientConnexion(EntityManagerInterface $entityManager): Response
    {
        $restaurant = $entityManager->getRepository(Restaurant::class)->find(1);
        
        return $this->render('home/client_connexion.html.twig', 
        [
            'restaurant' => $restaurant,
        ]);
    }

    #[Route('/pro-connexion', name: 'pro_connexion')]
    public function proConnexion(EntityManagerInterface $entityManager): Response
    {
        $restaurant = $entityManager->getRepository(Restaurant::class)->find(1);
        
        return $this->render('home/pro_connexion.html.twig', 
        [
            'restaurant' => $restaurant,
        ]);
    }

    #[Route('/client-inscription', name: 'client_inscription')]
    public function clientInscription(EntityManagerInterface $entityManager): Response
    {
        $restaurant = $entityManager->getRepository(Restaurant::class)->find(1);
        
        return $this->render('home/inscription.html.twig', 
        [
            'restaurant' => $restaurant,
        ]);
    }

    #[Route('/mot-de-passe-oublie', name: 'mot_de_passe_oublie')]
    public function motDePasse(EntityManagerInterface $entityManager): Response
    {
        $restaurant = $entityManager->getRepository(Restaurant::class)->find(1);
        
        return $this->render('home/mot_de_passe_oublie.html.twig', 
        [
            'restaurant' => $restaurant,
        ]);
    }

    #[Route('/nous-contacter', name: 'contact', methods: ['GET', 'POST'])]
    public function contact(
        Request $request, 
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        TwigEnvironment $twig): Response
    {
        $restaurant = $entityManager->getRepository(Restaurant::class)->find(1);
        
        if ($request->isMethod('POST')) 
		{
            $contact = new Contact();
            $contact->setNom($request->request->get('nom'));
            $contact->setEmail($request->request->get('email'));
            $contact->setMessage($request->request->get('message'));

            $entityManager->persist($contact);
            $entityManager->flush();

            // Préparation du contenu de l'email à partir du template Twig
            $emailContent = $twig->render('home/email_confirmation.html.twig', 
            [
                'nom' => $contact->getNom(),
                'restaurant' => $restaurant
            ]);

            // Construire et envoyer l'email
            $email = (new Email())
            ->from('contact@restolico.fr') // Remplacez par votre adresse email d'envoi
            ->to($contact->getEmail()) // L'email du contact
            ->subject('Confirmation de réception de votre message')
            ->html($emailContent);

            $mailer->send($email);

            // Envoi d'un email au gérant avec les détails du message
            $this->envoyerEmailAuGerant($contact, $mailer);

            $this->addFlash('success', 
            'Votre message a été envoyé avec succès, un email vient de vous être envoyé.');

            return $this->redirectToRoute('contact');
        }

        return $this->render('home/contact.html.twig', 
        [
            'restaurant' => $restaurant,
        ]);
    }

    private function envoyerEmailAuGerant(Contact $contact, MailerInterface $mailer): void
    {
        // Préparez le contenu de l'email pour le gérant
        $emailGerant = (new Email())
            ->from('contact@restolico.fr')
            ->to('gerant@sfr.fr') // Remplacez par l'email du gérant
            ->subject('Nouveau message de contact')
            ->html("
                <h1>Nouveau message de : {$contact->getNom()}</h1>
                <p>Email du contact : {$contact->getEmail()}</p>
                <p>Message : {$contact->getMessage()}</p>
            ");

        // Essayez d'envoyer l'email et gérez les éventuelles exceptions
        try 
        {
            $mailer->send($emailGerant);
            // Vous pouvez ajouter un flash message pour confirmer l'envoi de l'email au gérant
            $this->addFlash('success', 'Un email a été envoyé au gérant avec les détails du contact.');
        } 
        catch (\Exception $e) 
        {
            // Gérer l'exception si l'email n'a pas pu être envoyé
            $this->addFlash('error', 'Problème lors de l\'envoi de l\'email au gérant.');
        }
    }
}