<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Entity\Restaurant;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CategorieController extends AbstractController
{
    #[Route('/creer-categorie', name: 'creer_categorie')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
		{
            $entityManager->persist($categorie);
            $entityManager->flush();
			
			//message flash
            $this->addFlash
            ('success','La categorie a été bien enregistrée avec succès.' );

            return $this->redirectToRoute('creer_categorie'); 
        }

        // Récupérer la liste des fermetures existantes..
        $listeCategories = $entityManager->getRepository(Categorie::class)->findAll();

        return $this->render('creation_categorie.html.twig', 
		[
            'form' => $form->createView(),
            'listeCategories' => $listeCategories,
        ]);
    }

    #[Route('/modifier-categorie/{id}', name: 'modifier_categorie')]
    public function modifier(Request $request, Categorie $categorie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $entityManager->persist($categorie);
            $entityManager->flush();

            $this->addFlash('success', 'La modification de la categorie ont été enregistrée avec succès.');

            return $this->redirectToRoute('creer_categorie'); // Redirection vers la liste des fermetures
        }

        return $this->render('modifier_categorie.html.twig', 
        [
            'form' => $form->createView(),
            'categorie' => $categorie,
        ]);
    }
	
	#[Route('/supprimer-categorie/{id}', name: 'supprimer_categorie')]
    public function supprimer(Categorie $categorie, EntityManagerInterface $entityManager): Response
    {   
        // Supprimez l'entité et redirigez vers la liste des fermetures
        $entityManager->remove($categorie);
        $entityManager->flush();

        // message
        $this->addFlash('success', 'La suppression de la categorie été enregistrée avec succès.');

        // Redirection après la suppression
        return $this->redirectToRoute('creer_categorie');
        
    }

}