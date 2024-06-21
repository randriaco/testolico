<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use ConfirmationCodeType;
use App\Entity\Restaurant;
use App\Security\EmailVerifier;
use Symfony\Component\Mime\Email;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/inscription', name: 'inscription')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        EntityManagerInterface $entityManager, 
        MailerInterface $mailer): Response
    {
        $user = new User();
        $restaurant = $entityManager->getRepository(Restaurant::class)->find(1);
        
        if ($request->isMethod('POST')) 
        {
            // Récupération des données du formulaire
            $nom = $request->request->get('nom');
            $prenom = $request->request->get('prenom');
            $telephone = $request->request->get('telephone');
            $email = $request->request->get('email');
            $confirmEmail = $request->request->get('confirmEmail');
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirmPassword');

            // Vérification si les emails de passe correspondent
            if ($email !== $confirmEmail) 
            {
                $this->addFlash('error', 
                'Les emails ne correspondent pas.');
                return $this->redirectToRoute('inscription');
            }
            
            // Vérification si les mots de passe correspondent
            if ($password !== $confirmPassword) 
            {
                $this->addFlash('error', '
                Les mots de passe ne correspondent pas.');
                return $this->redirectToRoute('inscription');
            }
            
            // Mise à jour de l'entité User avec les données reçues
            $user->setNom($nom)
                ->setPrenom($prenom)
                ->setTelephone($telephone)
                ->setEmail($email)
                ->setPassword($userPasswordHasher->hashPassword($user, $password))
                ->setRoles(['ROLE_USER']) // Définir le rôle de l'utilisateur
                ->setCreatedAt(new DateTimeImmutable()); // Enregistrer la date de création
                
            // Génération du code de confirmation
            $confirmationCode = random_int(100000, 999999);
            $user->setConfirmationCode($confirmationCode);

            $entityManager->persist($user);
            $entityManager->flush();

            // Envoi de l'e-mail de confirmation
            $email = (new Email())
                ->from('contact@netilico.fr')
                ->to($user->getEmail())
                ->subject('Confirmation de votre inscription')
                ->text("Votre code de confirmation est : $confirmationCode");

            $mailer->send($email);

            $this->addFlash('success', 
            'Un e-mail contenant votre code de confirmation a été envoyé. Veuillez vérifier votre boîte de réception.');

            // Après avoir envoyé l'email de confirmation
            $session = $request->getSession();
            $session->set('user_confirmation_email', $user->getEmail()); // Stocker l'email dans la session

            // return $this->redirectToRoute('confirmation', ['email' => $user->getEmail()]);
            return $this->redirectToRoute('confirmation');
        }

        return $this->render('home/client_inscription.html.twig', 
        [
            'restaurant' => $restaurant,
        ]);
    }

    #[Route('/confirmation', name: 'confirmation')]
    public function confirm(
            Request $request, 
            EntityManagerInterface $entityManager, 
            SessionInterface $session,
            TokenStorageInterface $tokenStorage,
            AuthorizationCheckerInterface $authorizationChecker,
            EventDispatcherInterface $eventDispatcher): Response
    {
        
        $restaurant = $entityManager->getRepository(Restaurant::class)->find(1);
        
        $email = $session->get('user_confirmation_email');
        if (!$email) 
        {
            // Gérer l'erreur : session expirée ou accès direct à la page de confirmation
            return $this->redirectToRoute('inscription');
        }
        
        $form = $this->createForm(ConfirmationCodeType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) 
        {
            $code = $form->get('confirmationCode')->getData();
            
            // Récupérer l'utilisateur par email et vérifier le code
            $user = $entityManager->getRepository(User::class)->findOneByEmail($email);
            
            if ($user && $user->getConfirmationCode() == $code) 
            {
                // Authentifier l'utilisateur programmatically
                $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
                $tokenStorage->setToken($token);
                
                // Déclenchez l'événement de connexion interactive pour informer le système de sécurité
                $event = new InteractiveLoginEvent($request, $token);
                $eventDispatcher->dispatch($event, InteractiveLoginEvent::class);
                
                // --------------------- debut ----------------------
                
                // Marquer l'utilisateur comme confirmé
                $user->setIsVerified(true); // Supposons que vous avez un champ `isVerified` pour cela
                $user->setConfirmationCode(null); // Optionnellement, effacer le code de confirmation

                // Enregistrez les modifications dans la base de données
                $entityManager->persist($user);
                $entityManager->flush();

                // Nettoyer la session
                $session->remove('user_confirmation_email');
                
                // ---------------------- fin ----------------------

                // Ajoutez un message flash pour informer l'utilisateur
                $this->addFlash('success', 
                'Votre compte a été confirmé avec succès.');

                // Rediriger vers la page de compte de l'utilisateur
                return $this->redirectToRoute('mon_profil');
            } 
            else 
            {
                // Gérer l'erreur : code incorrect
                $this->addFlash('error', 
                'Le code de confirmation est incorrect. Veuillez réessayer.');
                
                // Rediriger à nouveau vers la page de confirmation ou afficher un formulaire pour retenter
                return $this->redirectToRoute('confirmation');
            }
        }
        
        return $this->render('confirmation.html.twig', 
        [
            'confirmationForm' => $form->createView(),
            'restaurant' => $restaurant,
        ]);
    }


    // -----------------------------route non utilisée --------------------------

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id) 
        {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) 
        {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try 
        {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } 
        catch (VerifyEmailExceptionInterface $exception) 
        {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Votre email a été bien verifié.');

        return $this->redirectToRoute('app_register');
    }
}
