<?php

namespace App\Controller;

use App\Entity\JoursMultiples;
use App\Form\JoursMultiplesType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class JoursMultiplesController extends AbstractController
{
    #[Route('/jours-multiples', name: 'jours_multiples')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $joursMultiples = new JoursMultiples();
        $form = $this->createForm(JoursMultiplesType::class, $joursMultiples);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
		{
            $entityManager->persist($joursMultiples);
            $entityManager->flush();

            // Ajouter un message flash
            $this->addFlash
			(
                'success',
                'Les dates de fermeture ont été bien enregistrées avec succès.'
            );

            return $this->redirectToRoute('jours_multiples');
        }

        // Récupérer la liste des fermetures existantes..
        $listeFermetures = $entityManager->getRepository(JoursMultiples::class)->findAll();

        return $this->render('admin/jours_multiples.html.twig', 
		[
            'form' => $form->createView(),
            'listeFermetures' => $listeFermetures,
        ]);
    }

    #[Route('/modifier-jours-multiples/{id}', name: 'modifier_jours_multiples')]
    public function modifier(Request $request, JoursMultiples $joursMultiples, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(JoursMultiplesType::class, $joursMultiples);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $entityManager->persist($joursMultiples);
            $entityManager->flush();

            $this->addFlash('success', 'Les modifications ont été enregistrées avec succès.');

            return $this->redirectToRoute('jours_multiples'); // Redirection vers la liste des fermetures
        }

        return $this->render('admin/modifier_jours_multiples.html.twig', 
        [
            'form' => $form->createView(),
            'fermeture' => $joursMultiples,
        ]);
    }

    #[Route('/supprimer-jours-multiples/{id}', name: 'supprimer_jours_multiples')]
    public function supprimer(JoursMultiples $fermeture, EntityManagerInterface $entityManager): Response
    {
        
        // Supprimez l'entité et redirigez vers la liste des fermetures
        $entityManager->remove($fermeture);
        $entityManager->flush();

        // message
        $this->addFlash('success', 'La suppression de la date de fermeture été enregistrées avec succès.');

        // Redirection après la suppression
        return $this->redirectToRoute('jours_multiples');
        
    }

    #[Route('/jours-fermetures', name: 'jours_fermetures')]
    public function joursFermetures(): Response
    {       
        return $this->render('admin/jours_fermetures.html.twig');
    }
}