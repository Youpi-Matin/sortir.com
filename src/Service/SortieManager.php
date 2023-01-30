<?php

namespace App\Service;

use App\Entity\Sortie;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\Exception\ORMException;

class SortieManager
{
    public function __construct(private SortieRepository $sortieRepository, private EtatRepository $etatRepository)
    {
    }


    /**Retourne les sorties passées ou annulées de plus d'un mois
     * @return array Les sorties expirées.
     */
    public function findSortiesAArchiver(): array
    {

        $oneMonth = new \DateInterval('P1M');
        $dateArchivage = new \DateTime('now');
        $dateArchivage->sub($oneMonth);

        $qb = $this->sortieRepository->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->addSelect('e')
            ->where('e.libelle = \'Passée\'')
            ->orWhere('e.libelle = \'Annulée\'')
            ->andWhere('s.dateHeureDebut < :dateArchivage')
            ->setParameter('dateArchivage', $dateArchivage);

        return $qb->getQuery()->getResult();
    }

    /** Modifie l'état de la sortie à 'Archivée'
     * @throws ORMException
     */
    public function archiveSortie(Sortie $sortie)
    {
        $sortie->setEtat($this->etatRepository->findOneBy(['libelle' => 'Archivée']));
        try {
            $this->sortieRepository->save($sortie, true);
        } catch (ORMException $e) {
            throw new ORMException("Une erreur est survenue dans l'archivage de la sortie" . $sortie->getId());
        }
    }
}
