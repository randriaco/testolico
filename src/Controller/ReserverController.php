<?php

namespace App\Controller;

use DateTime;
use App\Entity\Place;
use App\Entity\Reservation;
use App\Service\TraductionJourMois;
use App\Repository\HoraireRepository;
use App\Service\ConfigurationService;
use App\Repository\FrequenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\JoursMultiplesRepository;
use App\Repository\JourSpecifiqueRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use App\Entity\Restaurant;

class ReserverController extends AbstractController
{

    #[Route('/reserver/{date?}', name: 'reserver')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function index( 
        EntityManagerInterface $entityManager,
        Request $request,
        FrequenceRepository $frequenceRepo,
        HoraireRepository $horaireRepo, 
        JourSpecifiqueRepository $jourSpecifiqueRepo, 
        JoursMultiplesRepository $jourMultiplesRepo,
        TraductionJourMois $traducteur, $date = null): Response
    {
        $aujourdhui = $date ? new DateTime($date) : new DateTime();
        $aujourdhui->setTime(0, 0, 0); // Réinitialiser l'heure à minuit

        $debutSemaine = (clone $aujourdhui)->modify('Monday this week');
        $finSemaine = (clone $debutSemaine)->modify('+6 days');

        $horairesSemaine = $horaireRepo->trouverHorairesPourLaSemaine();

        $creneaux = $this->calculerCreneaux($horairesSemaine);

        $dates = [];
        for ($i = 0; $i < 7; $i++) 
        {
            $dateDuJour = (clone $debutSemaine)->modify("+$i days");
            $jourEnFrancais = $traducteur->traduire($dateDuJour->format('l'));
            $dates[$jourEnFrancais] = $dateDuJour;
        }

        $statutsJours = [];
        foreach ($horairesSemaine as $horaire) 
        {
            $jour = $horaire->getJour();
            $statutsJours[$jour] = $horaire->getStatus();
        }
		
		//----------------------------debut : Jours specifiques - fermeture------------------------------------------

        // Récupération des jours specifiques - fermeture
		$joursSpecifiques = $jourSpecifiqueRepo->trouverParPeriode($debutSemaine, $finSemaine);
        $joursFermeture = $jourMultiplesRepo->findAll(); 

        // Traitement des jours spécifiques - fermeture
        $creneaux = $this->traiterJoursSpecifiques($creneaux, $joursSpecifiques, $traducteur);
        $creneaux = $this->exclureJoursFermeture($creneaux, $joursFermeture, $dates, $traducteur);

		// Convertir les jours de fermeture en un format utilisable
		$periodesFermeture = [];
		foreach ($joursFermeture as $fermeture) 
		{
			$debut = $fermeture->getDebutFermeture();
			$fin = $fermeture->getFinFermeture();
			// Vous pouvez ajouter d'autres détails si nécessaire
			$periodesFermeture[] = ['debut' => $debut, 'fin' => $fin];
		}

        // ----------------------debut : date butoir  -----------------------
        
        // Récupérer l'instance de Frequence (assurez-vous d'avoir une méthode pour obtenir la bonne instance)
        $frequence = $frequenceRepo->findLatestFrequence();

        $joursButoir = $frequence->getDateButoir() ?? 30; // Valeur par défaut si non défini
        $dateButoir = (new DateTime('now'))->modify("+$joursButoir days");

        // Filtrage des jours spécifiques et des créneaux
        $joursSpecifiquesFiltres = array_filter($joursSpecifiques, function($jour) use ($dateButoir) 
        {
            return $jour->getDate() <= $dateButoir;
        });

        $creneauxFiltres = [];
        foreach ($creneaux as $nomJour => $creneauxJour) 
        {
            // Assurez-vous que $dates[$nomJour] est déjà un objet DateTime
            $dateDuJour = $dates[$nomJour];

            if ($dateDuJour <= $dateButoir) 
            {
                $creneauxFiltres[$nomJour] = $creneauxJour;
            }
        }

        //Gérer l'affichage du bouton semaine suivante        
        $afficherBoutonSemaineSuivante = $finSemaine <= $dateButoir;

        // -------------------------debut : gestion semaine vide----------------------------

        $semaineVide = true;
        foreach ($creneaux as $creneauxJour) 
        {
            if (!empty($creneauxJour)) 
            {
                $semaineVide = false;
                break;
            }
        }

        $nombreCouverts = $request->request->get('nombreCouverts');

        $restaurant = $entityManager->getRepository(Restaurant::class)->find(1);

        return $this->render('home/reserver.html.twig', 
		[
            'debutSemaine' => $debutSemaine,
            'finSemaine' => $finSemaine,
            'dates' => $dates,
            'aujourdhui' => $aujourdhui,
            'statutsJours' => $statutsJours,
            'joursSpecifiques' => $joursSpecifiques,
            'semaineVide' => $semaineVide,
			'joursFermeture' => $periodesFermeture, 

            'joursSpecifiquesFiltres' => $joursSpecifiquesFiltres,
            'creneaux' => $creneauxFiltres,
            'afficherBoutonSemaineSuivante' => $afficherBoutonSemaineSuivante,

            'restaurant' => $restaurant,
        ]);
    }

    #[Route('/confirmation_reservation', name: 'confirmation_reservation', methods:['post'])]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function confirmerReservation(
        Request $request, 
        EntityManagerInterface $entityManager,
        ConfigurationService $configurationService): Response
    {       
        //---------------------------- nombre de couverts -----------------------------------
		$nombreCouverts = $request->request->get('nombreCouverts'); // dd($nombreCouverts);
		
		// --------bouton radio activé : determiner dateReservation et horaireReservation------
		$creneauSelectionne = $request->request->get('creneau');

        // Si aucun créneau n'est sélectionné, affichez un message d'erreur et redirigez
        if (empty($creneauSelectionne)) 
        {
            $this->addFlash(
                'danger',
                'Veuillez choisir une date et un horaire pour pouvoir réserver.'
            );

            return $this->redirectToRoute('reserver');
        }

        // Trouver la position du dernier tiret qui sépare la date de l'horaire
        $lastDashPos = strrpos($creneauSelectionne, '-');
        
        // Extraire la date et l'horaire en utilisant la position du dernier tiret
        $dateReservation = substr($creneauSelectionne, 0, $lastDashPos);
        $horaireReservation = substr($creneauSelectionne, $lastDashPos + 1);
		
		//--------------------------- la date de reservation à utiliser -----------------------------
		$dateReservation = new \DateTime($dateReservation);
		
		// le nombre total de places reservables determiné par le gérant
		$placesTotal = $configurationService->getPlacesTotal();

        // Chercher une entité Place pour la date choisie
		$place = $entityManager->getRepository(Place::class)->findOneBy(['date' => $dateReservation]);
		
		// Si aucune entité Place n'existe pour cette date, créez-en une nouvelle
        if (!$place) 
		{            
			$place = new Place();
			$place->setDate($dateReservation);
			$place->setPlacesTotal($placesTotal); // Exemple: définir le nombre total de places
			$place->setPlacesReservees($nombreCouverts);
			$place->setPlacesLiberees(0);
			$place->setPlacesDispo($place->getPlacesTotal() - $place->getPlacesReservees());
			
			$entityManager->persist($place);
			$entityManager->flush(); // Sauvegarder la nouvelle entité Place
			
			// Récupérer le nombre de places disponibles pour aujourd'hui
			$placesDispo = $place->getPlacesDispo();
			
        }
		else
		{
			
			// Chercher une entité Place pour la date choisie
			$place = $entityManager->getRepository(Place::class)->findOneBy(['date' => $dateReservation]);
		
			// determiner la place dispo
			$placesDispo = $place->getPlacesDispo();

			if ($placesDispo < $nombreCouverts) 
			{
				$this->addFlash
				(
					'danger',
					'il ne faut pas depasser le nombre de places disponibles.'
				);

				return $this->redirectToRoute('reserver');
			}
			else
			{			
				// Mise à jour des places réservées
				$placesReservees = $place->getPlacesReservees();
				$place->setPlacesReservees($placesReservees + $nombreCouverts);

				$placesReserveesNew =  $place->getPlacesReservees();

				// Mise à jour des places disponibles
				$placesTotal = $place->getPlacesTotal();
				$placesLiberees = $place->getPlacesLiberees();
				
				$place->setPlacesDispo($placesTotal - $placesReserveesNew + $placesLiberees);

				// mettre à zero la place liberée
				$place->setPlacesLiberees(0);

				// Sauvegardez les modifications dans la base de données
				$entityManager->flush();

			}		
		}
        
        // Création et sauvegarde de l'entité Reservation
		$reservation = new Reservation();
		
		$reservation->setUser($this->getUser());
		$reservation->setDateReservation(new \DateTime($dateReservation->format('Y-m-d')));
		$reservation->setHoraireReservation($horaireReservation);
		$reservation->setNombreCouverts($nombreCouverts);

		$entityManager->persist($reservation);
		$entityManager->flush();

        // Après avoir flushé la réservation, stockez l'ID dans la session
        $request->getSession()->set('reservationId', $reservation->getId());

        $restaurant = $entityManager->getRepository(Restaurant::class)->find(1);

        return $this->render('home/confirmation_reservation.html.twig', 
        [
            'nombreCouverts' => $nombreCouverts,
            'dateReservation' => $dateReservation->format('Y-m-d'),
            'horaireReservation' => $horaireReservation,
            
            'restaurant' => $restaurant,
        ]);
    }
    
    #[Route('/fetch-places-dispo', name: 'fetch_places_dispo')]
    public function fetchPlacesDispo(Request $request, EntityManagerInterface $entityManager, ConfigurationService $configurationService): JsonResponse
    {
        $date = new \DateTime($request->query->get('date'));
        $place = $entityManager->getRepository(Place::class)->findOneBy(['date' => $date]);
        
        // si l'entite place n'existe pas, donc on la crée
        if (!$place) 
        {
            $totalPlaces = $configurationService->getPlacesTotal();

            $place = new Place();
            $place->setDate($date);
            $place->setPlacesTotal($totalPlaces); // Utilisez le nombre total de places du ConfigurationService
            $place->setPlacesReservees(0);
            $place->setPlacesLiberees(0);
            $place->setPlacesDispo($totalPlaces); // Initialement, toutes les places sont disponibles

            $entityManager->persist($place);
            $entityManager->flush();
        }

        $placesDispo = $place->getPlacesDispo();
        
        return new JsonResponse(['placesDispo' => $placesDispo]);
    }

    private function exclureJoursFermeture($creneaux, $joursFermeture, $dates, $traducteur)
	{
		foreach ($joursFermeture as $fermeture) 
		{
			foreach ($creneaux as $jour => $creneauxJour) 
			{
				// Traduisez le jour en français avant la comparaison
				$jourTraduit = $traducteur->traduire($jour);
				
				// Utilisez $jourTraduit pour accéder à la date correspondante dans $dates
				if (isset($dates[$jourTraduit])) 
                {
                    $dateDuJour = $dates[$jourTraduit]; // Utilisez directement l'objet DateTime existant

                    if ($dateDuJour >= $fermeture->getDebutFermeture() && $dateDuJour <= $fermeture->getFinFermeture()) 
                    {
                        $creneaux[$jour] = []; // Marquez comme fermé
                    }
                }    
			}
		}

		return $creneaux;
	}

    //--------------------------------jours specifiques---------------------------------------
	private function traiterJoursSpecifiques($creneaux, $joursSpecifiques, $traducteur)
    {
       
        foreach ($joursSpecifiques as $jourSpecifique) 
        {
            $dateSpecifique = $traducteur->traduire($jourSpecifique->getDate()->format('l'));
			$statusSpecifique = $jourSpecifique->getStatus(); 
			$intervalleSpecifique = $jourSpecifique->getFrequence()->getIntervalle();

            if (isset($creneaux[$dateSpecifique])) 
            {
                if ($statusSpecifique === 'Fermé') 
                {
                    $creneaux[$dateSpecifique] = 
                    [
                        'matin' => $this->genererCreneauxPourPeriode($jourSpecifique->getOuvertureMatin(), $jourSpecifique->getFermetureMatin(), $intervalleSpecifique),
                        'soir' => $this->genererCreneauxPourPeriode($jourSpecifique->getOuvertureSoir(), $jourSpecifique->getFermetureSoir(), $intervalleSpecifique),
                    ];
                } 
                else if ($statusSpecifique === 'Fermé Matin') 
                {
                    $creneaux[$dateSpecifique] = [
                        'soir' => $this->genererCreneauxPourPeriode($jourSpecifique->getOuvertureSoir(), $jourSpecifique->getFermetureSoir(), $intervalleSpecifique),
                    ];
                } 
                else if ($statusSpecifique === 'Fermé Soir') 
                {
                    $creneaux[$dateSpecifique] = [
                        'matin' => $this->genererCreneauxPourPeriode($jourSpecifique->getOuvertureMatin(), $jourSpecifique->getFermetureMatin(), $intervalleSpecifique),
                    ];
                } 
                else if ($statusSpecifique === 'Continu') 
                {
                    $creneaux[$dateSpecifique] = [
                        'continu' => $this->genererCreneauxPourPeriode($jourSpecifique->getOuvertureMatin(), $jourSpecifique->getFermetureSoir(), $intervalleSpecifique),
                    ];
                }
                else
                {
                    
                    $creneaux[$dateSpecifique] = 
                    [
                        'matin' => $this->genererCreneauxPourPeriode($jourSpecifique->getOuvertureMatin(), $jourSpecifique->getFermetureMatin(), $intervalleSpecifique),
                        'soir' => $this->genererCreneauxPourPeriode($jourSpecifique->getOuvertureSoir(), $jourSpecifique->getFermetureSoir(), $intervalleSpecifique),
                    ];
                }
            }
        }

        return $creneaux;
    }
	
    //--------------------------------horaires reguliers----------------------------------------
	private function calculerCreneaux($horaires)
    {
        $creneaux = [];
        foreach ($horaires as $horaire) 
        {
            $jour = $horaire->getJour();
            $status = $horaire->getStatus(); // Supposons que vous avez un getter pour 'status'
            $intervalle = $horaire->getFrequence()->getIntervalle();

            if ($status === 'Fermé')
            {
                // Créer des créneaux normalement pour le matin et le soir
                $creneaux[$jour]['matin'] = $this->genererCreneauxPourPeriode($horaire->getOuvertureMatin(), $horaire->getFermetureMatin(), $intervalle);
                $creneaux[$jour]['soir'] = $this->genererCreneauxPourPeriode($horaire->getOuvertureSoir(), $horaire->getFermetureSoir(), $intervalle);
            
            } 
            elseif ($status === 'Continu') 
            {
                // Créer un créneau continu de l'ouverture à la fermeture
                $creneaux[$jour]['continu'] = $this->genererCreneauxPourPeriode($horaire->getOuvertureMatin(), $horaire->getFermetureSoir(), $intervalle);
            }
            elseif ($status === 'Fermé Matin') 
            {
                // Créer uniquement des créneaux pour le soir
                $creneaux[$jour]['soir'] = $this->genererCreneauxPourPeriode($horaire->getOuvertureSoir(), $horaire->getFermetureSoir(), $intervalle);
            }
            elseif ($status === 'Fermé Soir') 
            {
                // Créer uniquement des créneaux pour le soir
                $creneaux[$jour]['matin'] = $this->genererCreneauxPourPeriode($horaire->getOuvertureMatin(), $horaire->getFermetureMatin(), $intervalle);
            }
            else
            {
                // Créer des créneaux normalement pour le matin et le soir
                $creneaux[$jour]['matin'] = $this->genererCreneauxPourPeriode($horaire->getOuvertureMatin(), $horaire->getFermetureMatin(), $intervalle);
                $creneaux[$jour]['soir'] = $this->genererCreneauxPourPeriode($horaire->getOuvertureSoir(), $horaire->getFermetureSoir(), $intervalle);
            }
            
        }

        return $creneaux;
    }

    private function genererCreneauxPourPeriode($debut, $fin, $intervalle)
    {
        $creneaux = [];
        $heureActuelle = clone $debut;

        while ($heureActuelle < $fin) 
        {
            $creneaux[] = $heureActuelle->format('H:i');
            $heureActuelle->modify("+{$intervalle} minutes");
        }

        return $creneaux;
    }
}