<?php

namespace App\Controller;

use App\Entity\Horaire;
use App\Entity\Frequence;
use App\Form\HoraireFrequenceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HoraireFrequenceController extends AbstractController
{
    #[Route('/admin-horaires-frequence', name: 'admin_horaires_frequences')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Créez une nouvelle instance de Frequence
        $frequence = new Frequence();

        // Créez une nouvelle instance de Horaire et associez-la à la fréquence
        $horaire = new Horaire();
        $horaire->setFrequence($frequence);

        // Créez le formulaire
        $form = $this->createForm(HoraireFrequenceType::class, $horaire);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            // Persistez d'abord la fréquence, puis l'horaire
            $entityManager->persist($frequence);
            $entityManager->persist($horaire);
            $entityManager->flush();

            // Ajoutez un message de succès
            $this->addFlash(
                'success',
                'Les horaires et la fréquence ont été enregistrés avec succès.'
            );

            // Redirection ou autre traitement après succès
            return $this->redirectToRoute('admin_horaires_frequences');
        }

        return $this->render('horaire_frequence/index.html.twig', 
        [
            'form' => $form->createView(),
        ]);
    }

    // #[Route('/', name: 'edit_horaires')]
    #[Route('/horaires-frequence', name: 'horaires_frequence')]
    public function editHoraires(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Charger tous les horaires
        $horaires = $entityManager->getRepository(Horaire::class)->findAll();

        // Supposons qu'il y ait une seule fréquence pour simplifier
        $frequence = $entityManager->getRepository(Frequence::class)->findOneBy([]);

        // Créer le formulaire
        $form = $this->createForm(HoraireFrequenceType::class, 
        [
            'horaires' => $horaires,
            'frequence' => $frequence,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
			// Extrait les données du formulaire
			$formData = $form->getData();

			// Mise à jour de la fréquence (si nécessaire)
			$frequence->setIntervalle($formData['frequence']);

            // Si vous avez ajouté un champ pour la date butoir dans votre formulaire
            if(isset($formData['dateButoir']))
            {
                $frequence->setDateButoir($formData['dateButoir']);
            }
            
			$entityManager->persist($frequence);

			// Mise à jour des horaires
			foreach ($horaires as $horaire) 
            {
				$jour = $horaire->getJour();

				$horaire->setOuvertureMatin($formData["ouvertureMatin_$jour"]);
				$horaire->setFermetureMatin($formData["fermetureMatin_$jour"]);
				$horaire->setOuvertureSoir($formData["ouvertureSoir_$jour"]);
				$horaire->setFermetureSoir($formData["fermetureSoir_$jour"]);
                $horaire->setStatus($formData["status_$jour"]);

				// Persiste les changements pour chaque horaire
				$entityManager->persist($horaire);
			}

			// Sauvegarde tous les changements en base de données
			$entityManager->flush();

			// Ajouter un message flash
            $this->addFlash(
                'success',
                'Les modifications ont été enregistrées avec succès.'
            );

            return $this->redirectToRoute('horaires_frequence');
		}

        // Afficher le formulaire
        // return $this->render('edit_horaires.html.twig', 
        return $this->render('admin/horaires_frequence.html.twig', 
        [
            'form' => $form->createView(),
        ]);
    }
}
