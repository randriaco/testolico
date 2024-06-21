<?php

namespace App\Controller;

use App\Repository\HoraireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookingController extends AbstractController
{
    #[Route('/booking/{date?}', name: 'booking')]
    public function index(HoraireRepository $horaireRepository, $date = null): Response
    {
        $dateAujourdhui = new \DateTime();
        $date = $date ? new \DateTime($date) : clone $dateAujourdhui;

		// $date->setTime(0, 0, 0); // Réinitialise l'heure à minuit

        $debutSemaine = (clone $date)->modify('monday this week');
        $finSemaine = (clone $debutSemaine)->modify('+6 days');

		dump($dateAujourdhui);

        $creneauxParJour = [];
        $datesParJour = [];
        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
       
		// $dateDuJour = clone $debutSemaine;
		$dateDuJour = clone $date; // Utilise la date fournie ou la date actuelle
		$dateDuJour->setTime(0, 0, 0); // Assurez-vous que $dateDuJour commence à minuituJour

		dump($dateDuJour);
		
        foreach ($jours as $jour) 
		{
            $datesParJour[$jour] = clone $dateDuJour;
            $horaire = $horaireRepository->trouverParJour($jour);

            if ($dateDuJour < $dateAujourdhui) 
			{
                // Pas de créneaux pour les jours passés
                $creneauxParJour[$jour] = [];
            } 
			else 
			{
                // Calculer les créneaux pour aujourd'hui et les jours futurs
                $creneaux = $this->genererCreneauxPourJour($horaire, $dateDuJour, $dateAujourdhui);
                $creneauxParJour[$jour] = $creneaux;
            }

            $dateDuJour->modify('+1 day');
        }

        return $this->render('booking.html.twig', 
		[
            'debutSemaine' => $debutSemaine,
            'finSemaine' => $finSemaine,
            'creneauxParJour' => $creneauxParJour,
            'datesParJour' => $datesParJour,
            'jours' => $jours,
        ]);
    }

	//------debut-------

	private function genererCreneauxPourJour($horaire, $dateDuJour, $dateAujourdhui)
	{
		$creneaux = [];
		
		if ($horaire) 
		{
			$frequence = $horaire->getFrequence()->getIntervalle();
			$creneauxMatin = $this->creerCreneaux($horaire->getOuvertureMatin(), $horaire->getFermetureMatin(), $frequence);
			$creneauxSoir = $this->creerCreneaux($horaire->getOuvertureSoir(), $horaire->getFermetureSoir(), $frequence);
			
			// dump($creneauxMatin);
			// dump($creneauxSoir);
			
			// Filtrer les créneaux pour la date d'aujourd'hui
			if ($dateDuJour->format('Y-m-d') == $dateAujourdhui->format('Y-m-d')) 
			{
				$heureActuelle = new \DateTime();
				dump($heureActuelle);

				$heureActuelleFormattee = $heureActuelle->format('H:i');

				// dump($heureActuelle);

				$creneauxMatin = array_filter($creneauxMatin, function ($creneau) use ($heureActuelleFormattee) 
				{
					return $creneau >= $heureActuelleFormattee;
				});

				$creneauxSoir = array_filter($creneauxSoir, function ($creneau) use ($heureActuelleFormattee) 
				{
					return $creneau >= $heureActuelleFormattee;
				});

				// $creneauxMatin = array_filter($creneauxMatin, function ($creneau) use ($heureActuelle) 
				// {
				// 	$creneauDateTime = \DateTime::createFromFormat('H:i', $creneau);
				// 	$creneauDateTime->setDate($heureActuelle->format('Y'), $heureActuelle->format('m'), $heureActuelle->format('d'));
				// 	return $creneauDateTime >= $heureActuelle;
				// });

				// $creneauxSoir = array_filter($creneauxSoir, function ($creneau) use ($heureActuelle) 
				// {
				// 	$creneauDateTime = \DateTime::createFromFormat('H:i', $creneau);
				// 	$creneauDateTime->setDate($heureActuelle->format('Y'), $heureActuelle->format('m'), $heureActuelle->format('d'));
				// 	return $creneauDateTime >= $heureActuelle;
				// });

				// dump($heureActuelle);
				// foreach ($creneauxMatin as $creneau) 
				// {
				// 	$creneauDateTime = \DateTime::createFromFormat('H:i', $creneau);
				// 	$creneauDateTime->setDate($heureActuelle->format('Y'), $heureActuelle->format('m'), $heureActuelle->format('d'));
				// 	dump($creneauDateTime);
				// }

				// dump($creneauxMatin);
				// dump($creneauxSoir);

				// Fusionner les créneaux du matin et du soir après le filtrage
				$creneaux = array_merge($creneauxMatin, $creneauxSoir);
				
			} 
			else 
			{
				// Pour les autres jours (pas aujourd'hui), conserver tous les créneaux
				$creneaux = array_merge($creneauxMatin, $creneauxSoir);
			}

			
		}
		
		return $creneaux;
	}

	//------fin-------
    // private function genererCreneauxPourJour($horaire, $dateDuJour, $dateAujourdhui)
	// {
	// 	$creneaux = [];
	// 	if ($horaire) 
	// 	{
	// 		$frequence = $horaire->getFrequence()->getIntervalle();
	// 		$creneauxMatin = $this->creerCreneaux($horaire->getOuvertureMatin(), $horaire->getFermetureMatin(), $frequence);
	// 		$creneauxSoir = $this->creerCreneaux($horaire->getOuvertureSoir(), $horaire->getFermetureSoir(), $frequence);
			
	// 		// Filtrer les créneaux pour la date d'aujourd'hui
	// 		if ($dateDuJour->format('Y-m-d') == $dateAujourdhui->format('Y-m-d')) 
	// 		{
	// 			$heureActuelle = new \DateTime();
	// 			$heureActuelleFormattee = $heureActuelle->format('H:i');

	// 			$creneauxMatin = array_filter($creneauxMatin, function ($creneau) use ($heureActuelleFormattee) 
	// 			{
	// 				return $creneau >= $heureActuelleFormattee;
	// 			});

	// 			$creneauxSoir = array_filter($creneauxSoir, function ($creneau) use ($heureActuelleFormattee) 
	// 			{
	// 				return $creneau >= $heureActuelleFormattee;
	// 			});

	// 			// Fusionner les créneaux du matin et du soir après le filtrage
	// 			$creneaux = array_merge($creneauxMatin, $creneauxSoir);
	// 		}
	// 		else 
	// 		{
	// 			// Pour les autres jours (pas aujourd'hui), conserver tous les créneaux
	// 			$creneaux = array_merge($creneauxMatin, $creneauxSoir);
	// 		}

	// 		// Fusionner les créneaux du matin et du soir
	// 		// $creneaux = array_merge($creneauxMatin, $creneauxSoir);

	// 		// $creneauxParJour[$jour] = $creneaux;
	// 	}

	// 	return $creneaux;
	// }


	private function creerCreneaux($heureDebut, $heureFin, $frequence)
	{
		$creneaux = [];
		$heureDebutObjet = \DateTime::createFromFormat('H:i', $heureDebut->format('H:i'));
		$heureFinObjet = \DateTime::createFromFormat('H:i', $heureFin->format('H:i'));

		while ($heureDebutObjet <= $heureFinObjet) 
		{
			$creneaux[] = $heureDebutObjet->format('H:i');
			$heureDebutObjet->modify("+{$frequence} minutes");
		}

		return $creneaux;
	}
}