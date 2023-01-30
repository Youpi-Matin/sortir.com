<?php

namespace App\Service;

use App\Entity\Sortie;
use DateTime;

class SortieAvantInscription
{
    public static function dansLesTemps(Sortie $sortie)
    {
        $now = new DateTime();

        return $sortie->getDateLimiteInscription() > $now;
    }

    public static function placesDisponibles(Sortie $sortie)
    {
        $dispo = $sortie->getNbInscriptionsMax() - count($sortie->getParticipants());

        return $dispo > 0;
    }
}
