<?php

namespace App\Service;

use App\Entity\Sortie;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use DateTime;
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

    /** Recherche les sorties à cloturer
     * Les sorties ouvertes et dont la date limite est hier
     * @return array
     */
    public function findSortiesACloturer(): array
    {
        $oneDay = new \DateInterval('P1D');
        $yesterday = (new \DateTime('now'))->sub($oneDay);
        $qb = $this->sortieRepository->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->addSelect('e')
            ->where('e.libelle = \'Ouverte\'')
            ->andWhere('s.dateLimiteInscription <= :delay')
            ->setParameter('delay', $yesterday);

        return $qb->getQuery()->getResult();
    }


    /** Recherche les sorties à activer
     * On recherche les sorties cloturées dont la date de début est antérieure à il y a 15 minutes.
     * @return array
     */
    public function findSortiesAActiver(): array
    {
        //
        $delay = new DateTime();
        $fiveteenMinutesAgo = (new DateTime('now'))->getTimestamp() - 900; //15 minutes Ago
        date_timestamp_set($delay, $fiveteenMinutesAgo); // Set delay to 15 minutes ago

        $qb = $this->sortieRepository->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->addSelect('e')
            ->where('e.libelle = \'Clôturée\'')
            ->andWhere('s.dateHeureDebut <= :interval')
            ->setParameter('interval', $delay);

        return $qb->getQuery()->getResult();
    }

    /** Modifie l'état de la sortie à 'Archivée'
     * (Passée || Annulée -> Archivée)
     * @throws ORMException
     */
    public function archiveSortie(Sortie $sortie)
    {
        $sortie->setEtat($this->etatRepository->findOneBy(['libelle' => 'Archivée']));
        try {
            $this->sortieRepository->save($sortie, true);
        } catch (ORMException $e) {
            throw new ORMException(
                "Une erreur est survenue dans l'archivage de la sortie: "
                . $sortie->getId()
                . " Message: "
                . $e->getMessage()
            );
        }
    }

    /**
     * Cloture une sortie (Ouverte -> CLôturée)
     * @param Sortie $sortie
     * @return void
     */
    public function clotureInscription(Sortie $sortie)
    {
        $sortie->setEtat($this->etatRepository->findOneBy(['libelle' => 'Clôturée']));
        try {
            $this->sortieRepository->save($sortie, true);
        } catch (ORMException $e) {
            throw new ORMException(
                "Une erreur est survenue dans la cloture de la sortie: "
                . $sortie->getId()
                . " Message: "
                . $e->getMessage()
            );
        }
    }

    /**
     * Active une sortie (Clôturée -> En cours)
     * @param Sortie $sortie
     * @return void
     */
    public function activeSortie(Sortie $sortie)
    {
        $sortie->setEtat($this->etatRepository->findOneBy(['libelle' => 'Activité en cours']));
        try {
            $this->sortieRepository->save($sortie, true);
        } catch (ORMException $e) {
            throw new ORMException(
                "Une erreur est survenue à l\'activation de la sortie: "
                . $sortie->getId()
                . " Message: "
                . $e->getMessage()
            );
        }
    }
}
