<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use App\Repository\ProduitRepository;

class PanierService 
{
    private $requestStack;
    private $produitRepository;

    public function __construct(RequestStack $requestStack, ProduitRepository $produitRepository) 
	{
        $this->requestStack = $requestStack;
        $this->produitRepository = $produitRepository;
    }

    public function ajouterAuPanier($idProduit) 
    {
        // Obtenir la session à partir du RequestStack
        $session = $this->requestStack->getSession();
        
        // Utiliser la session obtenue pour accéder et modifier le panier
        $panier = $session->get('panier', []);

        if (!empty($panier[$idProduit])) {
            $panier[$idProduit]++;
        } else {
            $panier[$idProduit] = 1;
        }

        $session->set('panier', $panier);
    }


    public function retirerDuPanier($idProduit) 
    {
        $session = $this->requestStack->getSession();
        $panier = $session->get('panier', []);

        if (!empty($panier[$idProduit])) {
            unset($panier[$idProduit]);
        }

        $session->set('panier', $panier);
    }


    public function changerQuantite($idProduit, $quantite) 
    {
        $session = $this->requestStack->getSession();
        $panier = $session->get('panier', []);

        if (!empty($panier[$idProduit])) {
            $panier[$idProduit] = $quantite;
        }

        $session->set('panier', $panier);
    }


    public function getContenuPanier() 
	{
        $session = $this->requestStack->getSession();
        $panier = $session->get('panier', []);

        $contenuPanier = [];
        foreach ($panier as $idProduit => $quantite) {
            $produit = $this->produitRepository->find($idProduit);
            if ($produit) {
                $contenuPanier[] = [
                    'produit' => $produit,
                    'quantite' => $quantite
                ];
            }
        }

        return $contenuPanier;
    }

    public function getTotalPanier() 
    {
        $total = 0;

        foreach ($this->getContenuPanier() as $item) 
        {
            $total += $item['produit']->getPrix() * $item['quantite'];
        }

        return $total;
    }

    public function viderPanier() 
    {
        $session = $this->requestStack->getSession();
        $session->set('panier', []);
    }

    public function boutonPlus($idProduit)
    {
        $session = $this->requestStack->getSession();
        $panier = $session->get('panier', []);

        if (!empty($panier[$idProduit])) 
        {
            $panier[$idProduit]++;
        } 
        else 
        {
            $panier[$idProduit] = 1; // Si le produit n'est pas dans le panier, l'ajouter avec une quantité de 1
        }

        $session->set('panier', $panier);
    }

    public function boutonMoins($idProduit)
    {
        $session = $this->requestStack->getSession();
        $panier = $session->get('panier', []);

        if (!empty($panier[$idProduit]) && $panier[$idProduit] > 1) 
        {
            $panier[$idProduit]--;
        } 
        else 
        {
            unset($panier[$idProduit]); // Si la quantité devient 0, retirer le produit du panier
        }

        $session->set('panier', $panier);
    }

}