<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Service\PanierService;

use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PanierController extends AbstractController
{
    private $panierService;
    private $router;

    public function __construct(PanierService $panierService, UrlGeneratorInterface $router)
    {
        $this->panierService = $panierService;
        $this->router = $router;
    }

    // ------------------------------------------ RESERVATION ---------------------------------------------

    // --------------------------panier : reservation avec commande ---------------------------------------

    #[Route('/reservation-ajouter-au-panier/{id}', name: 'ajouter_au_panier_reservation')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function ajouterAuPanierReservation($id, ProduitRepository $produitRepository): Response
    {
        // Trouver le produit par son ID
        $produit = $produitRepository->find($id);

        if (!$produit) 
        {
            // Gérer le cas où le produit n'est pas trouvé
            $this->addFlash('danger', 'Produit non trouvé.');
            return $this->redirectToRoute('liste_produits');
        }

        // Appeler ajouterAuPanier sur PanierService directement
        $this->panierService->ajouterAuPanier($id);
        
        // Personnaliser le message flash pour inclure le nom du produit
        $this->addFlash('success', 'Le produit "' . $produit->getNom() . '" a été bien ajouté au panier.');

        // Assurez-vous que votre entité Produit a une relation avec Categorie et un getter pour celle-ci
        $nomCategorie = $produit->getCategorie()->getNom();

        return $this->redirectToRoute('afficher_produits_par_categorie_reservation', ['nomCategorie' => $nomCategorie]);
    }

    // ------------------ panier : bouton moins reservation--------------------------

    #[Route('/reservation-panier-retirer/{id}', name: 'panier_retirer_reservation')]
    public function retirerDuPanierReservation($id, ProduitRepository $produitRepository): Response
    {
        // Trouver le produit par son ID pour obtenir le nom du produit
        $produit = $produitRepository->find($id);

        if (!$produit) 
        {
            // Gérer le cas où le produit n'est pas trouvé
            $this->addFlash('danger', 'Produit non trouvé.');
            return $this->redirectToRoute('panier_afficher_reservation');
        }

        // Retirer le produit du panier
        $this->panierService->retirerDuPanier($id);

        // Personnaliser le message flash pour inclure le nom du produit
        $this->addFlash('success', 'Le produit "' . $produit->getNom() . '" a été retiré du panier avec succès.');

        return $this->redirectToRoute('panier_afficher_reservation');
    }

    // ----------------panier : bouton plus reservation-----------------------

    #[Route('/reservation-panier-plus/{id}', name: 'panier_plus_reservation')]
    public function panierPlusReservation($id): Response
    {
        $this->panierService->boutonPlus($id);
        return $this->redirectToRoute('panier_afficher_reservation');
    }

    #[Route('/reservation-panier-moins/{id}', name: 'panier_moins_reservation')]
    public function panierMoinsReservation($id): Response
    {
        $this->panierService->boutonMoins($id);
        return $this->redirectToRoute('panier_afficher_reservation');
    }


    // -------------------------------------------- COMMANDE --------------------------------------

    // --------------------------panier : reservation avec commande ----------------------------------------

    #[Route('/commande-ajouter-au-panier/{id}', name: 'ajouter_au_panier_commande')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function ajouterAuPanierCommande($id, ProduitRepository $produitRepository): Response
    {
        // Trouver le produit par son ID
        $produit = $produitRepository->find($id);

        if (!$produit) 
        {
            // Gérer le cas où le produit n'est pas trouvé
            $this->addFlash('danger', 'Produit non trouvé.');
            return $this->redirectToRoute('liste_produits');
        }

        // Appeler ajouterAuPanier sur PanierService directement
        $this->panierService->ajouterAuPanier($id);
        
        // Personnaliser le message flash pour inclure le nom du produit
        $this->addFlash('success', 'Le produit "' . $produit->getNom() . '" a été bien ajouté au panier.');

        // Assurez-vous que votre entité Produit a une relation avec Categorie et un getter pour celle-ci
        $nomCategorie = $produit->getCategorie()->getNom();

        return $this->redirectToRoute('afficher_produits_par_categorie_commande', ['nomCategorie' => $nomCategorie]);
    }

    // ------------------ panier : bouton moins commande--------------------------

    #[Route('/commande-panier-retirer/{id}', name: 'panier_retirer_commande')]
    public function retirerDuPanierCommande($id, ProduitRepository $produitRepository): Response
    {
        // Trouver le produit par son ID pour obtenir le nom du produit
        $produit = $produitRepository->find($id);

        if (!$produit) 
        {
            // Gérer le cas où le produit n'est pas trouvé
            $this->addFlash('danger', 'Produit non trouvé.');
            return $this->redirectToRoute('panier_afficher_commande');
        }

        // Retirer le produit du panier
        $this->panierService->retirerDuPanier($id);

        // Personnaliser le message flash pour inclure le nom du produit
        $this->addFlash('success', 'Le produit "' . $produit->getNom() . '" a été retiré du panier avec succès.');

        return $this->redirectToRoute('panier_afficher_commande');
    }

    // ----------------panier : bouton plus commande------------------------

    #[Route('/commande-panier-plus/{id}', name: 'panier_plus_commande')]
    public function panierPlusCommande($id): Response
    {
        $this->panierService->boutonPlus($id);
        return $this->redirectToRoute('panier_afficher_commande');
    }

    #[Route('/commande-panier-moins/{id}', name: 'panier_moins_commande')]
    public function panierMoinsCommande($id): Response
    {
        $this->panierService->boutonMoins($id);
        return $this->redirectToRoute('panier_afficher_commande');
    } 

}