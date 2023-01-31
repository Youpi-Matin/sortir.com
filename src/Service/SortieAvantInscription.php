<?php

namespace App\Service;

use App\Entity\Sortie;
use DateTime;

class SortieAvantInscription
{
    public static function dansLesTemps(Sortie $sortie)
    {
        $now = (new DateTime())->getTimestamp();
        return $now < $sortie->getDateLimiteInscription()->modify('+1 Day')->getTimestamp();
    }

    public static function placesDisponibles(Sortie $sortie)
    {
        $dispo = $sortie->getNbInscriptionsMax() - count($sortie->getParticipants());

        return $dispo > 0;
    }
}
