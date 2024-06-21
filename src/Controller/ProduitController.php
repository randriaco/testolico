<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Entity\Restaurant;
use App\Service\PanierService;

use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProduitController extends AbstractController
{
    private $panierService;
    private $router;

    public function __construct(PanierService $panierService, UrlGeneratorInterface $router)
    {
        $this->panierService = $panierService;
        $this->router = $router;
    }

    // -----------------------produits -------------------------------------
      
    #[Route('/creer-produit', name: 'creer_produit')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
		{
            $entityManager->persist($produit);
            $entityManager->flush();
			
			//message flash
            $this->addFlash
            ('success','Le produit a été bien enregistré avec succès.' );

            return $this->redirectToRoute('creer_produit'); 
        }

        $listeProduits = $entityManager->getRepository(Produit::class)->findAll();

        return $this->render('creation_produit.html.twig', 
		[
            'form' => $form->createView(),
			'listeProduits' => $listeProduits,
        ]);
    }

    #[Route('/liste-produits', name: 'liste_produits')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function liste(
        ProduitRepository $produitRepository,
        EntityManagerInterface $entityManager): Response
    {
        $produits = $produitRepository->findAll();

        $restaurant = $entityManager->getRepository(Restaurant::class)->find(1);

        return $this->render('home/liste_produits.html.twig', 
        [
            'produits' => $produits,
            'restaurant' => $restaurant,
        ]);
    }

    #[Route('/modifier-produit/{id}', name: 'modifier_produit')]
    public function modifier(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        // Créer un formulaire en utilisant un nouveau ProduitType mais avec l'objet $produit existant
        $form = $this->createForm(ProduitType::class, $produit, 
        [
            'method' => 'POST', // Définir la méthode à POST pour que le formulaire utilise la bonne action dans l'attribut action HTML
            'action' => $this->generateUrl('modifier_produit', ['id' => $produit->getId()]), // Définir l'action du formulaire pour qu'elle pointe vers la route de modification du produit actuel
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            // Pas besoin de persister le produit, car il est déjà géré par Doctrine
            $entityManager->flush();

            $this->addFlash('success', 'La modification du produit a été enregistrée avec succès.');

            return $this->redirectToRoute('creer_produit');
        }

        return $this->render('modifier_produit.html.twig', 
        [
            'form' => $form->createView(),
            'produit' => $produit,
        ]);
    }
	
	#[Route('/supprimer-produit/{id}', name: 'supprimer_produit')]
    public function supprimer(Produit $produit, EntityManagerInterface $entityManager): Response
    {
        
        // Supprimez l'entité et redirigez vers la liste des fermetures
        $entityManager->remove($produit);
        $entityManager->flush();

        // message
        $this->addFlash('success', 'La suppression du produit été enregistrée avec succès.');

        // Redirection après la suppression
        return $this->redirectToRoute('creer_produit');
        
    }

}