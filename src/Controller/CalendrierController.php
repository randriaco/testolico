<?php

namespace App\Controller;

use App\Repository\HoraireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalendrierController extends AbstractController
{
    #[Route('/calendrier/{date?}', name: 'calendrier')]
    public function index(HoraireRepository $horaireRepository, $date = null): Response
    {
        $dateAujourdhui = new \DateTime(); // Date et heure actuelles
        $date = $date ? new \DateTime($date) : clone $dateAujourdhui;
        $debutSemaine = (clone $date)->modify('monday this week');
        $finSemaine = (clone $debutSemaine)->modify('+6 days');

        $creneauxParJour = [];
        $datesParJour = [];
        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

        $dateDuJour = clone $date;

        // dd($dateDuJour);

        //------ debut--------
        foreach ($jours as $jour) 
        {
            $horaire = $horaireRepository->trouverParJour($jour);
            $datesParJour[$jour] = clone $dateDuJour;
        
            // Ne calculez les créneaux que si le jour n'est pas passé
            if ($dateDuJour >= $dateAujourdhui) {
                if ($horaire) { 
                    $frequence = $horaire->getFrequence()->getIntervalle();
                    $creneaux = $this->calculerCreneaux($horaire, $frequence, $dateDuJour, $dateAujourdhui);
                    $creneauxParJour[$jour] = $creneaux;
                } else {
                    $creneauxParJour[$jour] = [];
                }
            } else {
                // Pour les jours passés, ne pas calculer ou afficher de créneaux
                $creneauxParJour[$jour] = [];
            }
        
            $dateDuJour->modify('+1 day');
        }
        // -------fin---------

        // foreach ($jours as $jour) 
        // {
        //     $horaire = $horaireRepository->trouverParJour($jour);
        //     $datesParJour[$jour] = clone $dateDuJour;

        //     if ($horaire) { // Vérifiez si l'horaire existe
        //         $frequence = $horaire->getFrequence()->getIntervalle();
        //         $creneaux = $this->calculerCreneaux($horaire, $frequence, $dateDuJour, $dateAujourdhui);
        //         $creneauxParJour[$jour] = $creneaux;
        //     } else {
        //         $creneauxParJour[$jour] = [];
        //     }

        //     $dateDuJour->modify('+1 day');
        // }

        return $this->render('calendrier.html.twig', [
            'debutSemaine' => $debutSemaine,
            'finSemaine' => $finSemaine,
            'creneauxParJour' => $creneauxParJour,
            'datesParJour' => $datesParJour,
            'jours' => $jours,
        ]);
    }

    // private function calculerCreneaux($horaire, $frequence, $dateDuJour, $dateAujourdhui)
    // {
    //     $creneaux = [];
    //     $heureActuelle = new \DateTime();
    //     $formatHeure = 'H:i';

    //     if ($horaire->getOuvertureMatin() && $horaire->getFermetureMatin() && $horaire->getOuvertureSoir() && $horaire->getFermetureSoir()) 
    //     {
    //         $heureDebutMatin = $horaire->getOuvertureMatin()->format($formatHeure);
    //         $heureFinMatin = $horaire->getFermetureMatin()->format($formatHeure);
    //         $heureDebutSoir = $horaire->getOuvertureSoir()->format($formatHeure);
    //         $heureFinSoir = $horaire->getFermetureSoir()->format($formatHeure);

    //         // Pour la date d'aujourd'hui
    //         if ($dateDuJour->format('Y-m-d') == $dateAujourdhui->format('Y-m-d')) {
    //             $this->ajouterCreneauxFuturs($creneaux, $heureDebutMatin, $heureFinMatin, $frequence, $heureActuelle);
    //             $this->ajouterCreneauxFuturs($creneaux, $heureDebutSoir, $heureFinSoir, $frequence, $heureActuelle);
    //         } 
    //         // Pour les jours futurs
    //         elseif ($dateDuJour > $dateAujourdhui) {
    //             $this->ajouterCreneaux($creneaux, $heureDebutMatin, $heureFinMatin, $frequence);
    //             $this->ajouterCreneaux($creneaux, $heureDebutSoir, $heureFinSoir, $frequence);
    //             }
    //             // Les jours passés sont déjà gérés dans la méthode index
    //     }
            
    //     return $creneaux;
    // }

    private function calculerCreneaux($horaire, $frequence, $dateDuJour, $dateAujourdhui)
    {
        $creneaux = [];
        $heureActuelle = new \DateTime();
        $formatHeure = 'H:i';
        
        if ($horaire->getOuvertureMatin() && $horaire->getFermetureMatin() && $horaire->getOuvertureSoir() && $horaire->getFermetureSoir()) 
        {
            $heureDebutMatin = $horaire->getOuvertureMatin()->format($formatHeure);
            $heureFinMatin = $horaire->getFermetureMatin()->format($formatHeure);
            $heureDebutSoir = $horaire->getOuvertureSoir()->format($formatHeure);
            $heureFinSoir = $horaire->getFermetureSoir()->format($formatHeure);

            if ($dateDuJour->format('Y-m-d') == $dateAujourdhui->format('Y-m-d')) 
            {
                // Générez les créneaux pour la date actuelle sans tenir compte de l'heure actuelle
                $this->ajouterCreneaux($creneaux, $heureDebutMatin, $heureFinMatin, $frequence);
                $this->ajouterCreneaux($creneaux, $heureDebutSoir, $heureFinSoir, $frequence);
            } elseif ($dateDuJour > $dateAujourdhui) 
            {
                // Générez les créneaux normalement pour les jours futurs
                $this->ajouterCreneaux($creneaux, $heureDebutMatin, $heureFinMatin, $frequence);
                $this->ajouterCreneaux($creneaux, $heureDebutSoir, $heureFinSoir, $frequence);
            }
            //Les jours passés sont déjà gérés dans la méthode index et n'auront pas de créneaux
        }

        return $creneaux;
    }

    // private function ajouterCreneauxFuturs(&$creneaux, $heureDebut, $heureFin, $frequence, $heureActuelle)
    // {
    //     $heureDebutObjet = \DateTime::createFromFormat('H:i', $heureDebut);
    //     $heureFinObjet = \DateTime::createFromFormat('H:i', $heureFin);
    //     $formatHeure = 'H:i';
        
    //     if ($heureActuelle->format($formatHeure) < $heureFin) 
    //     {
    //         $heure = $heureActuelle->format($formatHeure) > $heureDebut ? $heureActuelle : \DateTime::createFromFormat('H:i', $heureDebut);
    //         while ($heure->format($formatHeure) < $heureFin) 
    //         {
    //             $creneaux[] = $heure->format($formatHeure);
    //             $heure->modify("+{$frequence} minutes");
    //         }
    //     }
    // }

    private function ajouterCreneauxFuturs(&$creneaux, $heureDebut, $heureFin, $frequence, $heureActuelle)
    {
        $heureDebutObjet = \DateTime::createFromFormat('H:i', $heureDebut);
        $heureFinObjet = \DateTime::createFromFormat('H:i', $heureFin);
        $formatHeure = 'H:i';
        
        // Assurez-vous que l'heure actuelle est entre l'heure de début et l'heure de fin
        if ($heureActuelle->format($formatHeure) >= $heureDebut && $heureActuelle->format($formatHeure) < $heureFin) 
        {
            $heure = $heureActuelle->format($formatHeure) > $heureDebutObjet->format($formatHeure) ? $heureActuelle : \DateTime::createFromFormat($formatHeure, $heureDebut);
            while ($heure->format($formatHeure) < $heureFin) 
            {
                $creneaux[] = $heure->format($formatHeure);
                $heure->modify("+{$frequence} minutes");
            }
        }
    }

    private function ajouterCreneaux(&$creneaux, $heureDebut, $heureFin, $frequence)
    {
        $heureDebutObjet = \DateTime::createFromFormat('H:i', $heureDebut);
        $heureFinObjet = \DateTime::createFromFormat('H:i', $heureFin);

        while ($heureDebutObjet <= $heureFinObjet) 
        {
            $creneaux[] = $heureDebutObjet->format('H:i');
            $heureDebutObjet->modify("+{$frequence} minutes");
        }
    }

}
