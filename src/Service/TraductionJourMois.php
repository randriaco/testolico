<?php

namespace App\Service;

class TraductionJourMois
{
    private $jours = [
        'Monday' => 'Lundi',
        'Tuesday' => 'Mardi',
        'Wednesday' => 'Mercredi',
        'Thursday' => 'Jeudi',
        'Friday' => 'Vendredi',
        'Saturday' => 'Samedi',
        'Sunday' => 'Dimanche',  
    ];

    private $mois = [
        'January' => 'Janvier',
        'February' => 'Fevrier',
        'March' => 'Mars',
        'April' => 'Avril',
        'May' => 'Mai',
        'June' => 'Juin',
        'July' => 'Juillet',
        'August' => 'Aout',
        'September' => 'Septembre',
        'October' => 'Octobre',
        'November' => 'Novembre',
        'December' => 'Decembre',
    ];

    public function traduire($chaine)
    {
        return $this->jours[$chaine] ?? $this->mois[$chaine] ?? $chaine;
    }
}