<?php

namespace App\Controller;

use App\Entity\Frequence;
use App\Entity\JourSpecifique;
use App\Form\JourSpecifiqueType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class JourSpecifiqueController extends AbstractController
{
    #[Route('/jour-specifique', name: 'jour_specifique')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $jourSpecifique = new JourSpecifique();
        $form = $this->createForm(JourSpecifiqueType::class, $jourSpecifique);

        // Récupération des jours spécifiques
        $joursSpecifiques = $entityManager->getRepository(JourSpecifique::class)->findAll();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            
            // Récupérer une instance de Frequence (ici, exemple avec l'ID 1)
            $frequence = $entityManager->getRepository(Frequence::class)->find(1);

            // Si aucune fréquence n'est trouvée, vous pouvez gérer l'erreur ou définir une valeur par défaut
            if (!$frequence) 
            {
                // Gérer l'erreur ou utiliser une valeur par défaut
                $this->addFlash('error', 'La fréquence spécifiée n\'existe pas.');
                return $this->redirectToRoute('jour_specifique');
            }

            // Associer la fréquence au JourSpecifique
            $jourSpecifique->setFrequence($frequence);
            
            
            
            $entityManager->persist($jourSpecifique);
            $entityManager->flush();

            // Ajouter un message flash
            $this->addFlash(
                'success',
                'Les informations du jour spécifique ont été enregistrées avec succès.'
            );

            return $this->redirectToRoute('jour_specifique');
        }

        return $this->render('admin/jour_specifique.html.twig', 
        [
            'form' => $form->createView(),
            'joursSpecifiques' => $joursSpecifiques, // Ajoutez cette ligne:
        ]);
    }

    #[Route('/supprimer-jour-specifique/{id}', name: 'supprimer_jour_specifique')]
    public function delete(JourSpecifique $jourSpecifique, EntityManagerInterface $entityManager): Response
    {
        // $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($jourSpecifique);
        $entityManager->flush();
        $this->addFlash('success', 'Le jour spécifique a été supprimé.');
        return $this->redirectToRoute('jour_specifique');
    }

    #[Route('/modifier-jour-specifique/{id}', name: 'modifier_jour_specifique')]
    public function edit(Request $request, JourSpecifique $jourSpecifique, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(JourSpecifiqueType::class, $jourSpecifique);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $entityManager->flush();

            $this->addFlash('success', 'Les modifications ont été enregistrées avec succès.');
            
            return $this->redirectToRoute('jour_specifique');
        }

        return $this->render('admin/modifier_jour_specifique.html.twig', [
            'jourSpecifique' => $jourSpecifique,
            'form' => $form->createView(),
        ]);
    }
}