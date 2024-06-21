<?php

namespace App\Controller;

use App\Entity\Place;
use App\Form\PlaceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PlaceController extends AbstractController
{
    #[Route('/creer-place', name: 'creer_place')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Étape de nettoyage : Supprimer les places passées
        $placesPassees = $entityManager->getRepository(Place::class)->findByDateBeforeToday();
        foreach ($placesPassees as $placePassee) 
        {
            $entityManager->remove($placePassee);
        }
        $entityManager->flush();
        
        // afficher la liste des places
        $place = new Place();
        $form = $this->createForm(PlaceType::class, $place);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) 
        {
            // Calculez le nouveau nombre de places disponibles
            $placesDispo = $place->getPlacesTotal() - $place->getPlacesReservees() + $place->getPlacesLiberees();

            // Mettez à jour le nombre de places réservées en soustrayant les places libérées
            $placesReserveesUpdated = $place->getPlacesReservees() - $place->getPlacesLiberees();
            // $place->setPlacesReservees(max(0, $placesReserveesUpdated)); // Assurez-vous que cela ne devient pas négatif
            $place->setPlacesReservees($placesReserveesUpdated);

            // Réinitialisez le nombre de places libérées à zéro
            $place->setPlacesLiberees(0);

            // Mettez à jour le nombre de places disponibles
            $place->setPlacesDispo($placesDispo);

            $entityManager->persist($place);
            $entityManager->flush();
            
            //message flash
            $this->addFlash
            (
                'success',
                'Les places ont été bien enregistrées avec succès.'
            );

            return $this->redirectToRoute('creer_place');
        }

        // Récupérer la liste des fermetures existantes..
        $listePlaces = $entityManager->getRepository(Place::class)->findPlacesOrderedByDate();
        // dd( $listePlaces);

        return $this->render('creation_place.html.twig', 
        [
            'form' => $form->createView(),
            'listePlaces' => $listePlaces 
        ]);
    }
    

    #[Route('/modifier-places/{id}', name: 'modifier_places')]
    public function modifier(Request $request, Place $place, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PlaceType::class, $place);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            // Calculez le nouveau nombre de places disponibles
            $placesDispo = $place->getPlacesTotal() - $place->getPlacesReservees() + $place->getPlacesLiberees();

            // Mettez à jour le nombre de places réservées en soustrayant les places libérées
            $placesReserveesUpdated = $place->getPlacesReservees() - $place->getPlacesLiberees();
            // $place->setPlacesReservees(max(0, $placesReserveesUpdated)); // Assurez-vous que cela ne devient pas négatif
            $place->setPlacesReservees($placesReserveesUpdated);

            // Réinitialisez le nombre de places libérées à zéro
            $place->setPlacesLiberees(0);

            // Mettez à jour le nombre de places disponibles
            $place->setPlacesDispo($placesDispo);

            $entityManager->persist($place);
            $entityManager->flush();

            $this->addFlash('success', 'Les modifications ont été enregistrées avec succès.');
            
            // Redirection vers la liste des places
            return $this->redirectToRoute('creer_place'); 
        }

        return $this->render('modifier_places.html.twig', 
        [
            'form' => $form->createView(),
            'place' => $place, // Assurez-vous que c'est 'place' et non 'places' pour correspondre à l'entité unique
        ]);
    }

    #[Route('/supprimer-places/{id}', name: 'supprimer_places')]
    public function supprimer(Place $places, EntityManagerInterface $entityManager): Response
    {       
        // Supprimez l'entité et redirigez vers la liste des fermetures
        $entityManager->remove($places);
        $entityManager->flush();

        // message
        $this->addFlash('success', 'La suppression de la place été enregistrées avec succès.');

        // Redirection après la suppression
        return $this->redirectToRoute('creer_place');
        
    }
}