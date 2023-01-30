<?php

namespace App\Service;

use App\Repository\SortieRepository;

class SortieManager
{
    public function __construct(private SortieRepository $repository)
    {
    }

    public function findSortiesAArchiver(): array
    {

        $oneMonth = new \DateInterval('P1M');
        $dateArchivage = new \DateTime('now');
        $dateArchivage->sub($oneMonth);

        return $this->repository->createQueryBuilder('s')
            ->join('Etat', 'e')
            ->where('s.etat_id = e.id')
            ->andWhere('e.libelle = \'Passée\' OR e.libelle = \'Annulée\'')
            ->andWhere('s.date_heure_debut > :dateArchivage')
            ->setParameter(':dateArchivage', $dateArchivage)
            ->getQuery()
            ->getResult();
    }
}
