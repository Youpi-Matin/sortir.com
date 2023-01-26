<?php

namespace App\Repository;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Model\SortieFiltre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    private EtatRepository $etatRepository;

    public function __construct(
        ManagerRegistry $registry,
        EtatRepository $etatRepository
    ) {
        parent::__construct($registry, Sortie::class);
        $this->etatRepository = $etatRepository;
    }

    public function save(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Sortie[] Returns an array of Sortie objects
     */
    public function findByFiltre(
        SortieFiltre $filtre,
        Participant $participant
    ): array {

        $etatPassee = $this->etatRepository->findOneBy(['libelle' => 'Passée']);
        $etatArchivee = $this->etatRepository->findOneBy(['libelle' => 'Archivée']);

        $qb = $this->createQueryBuilder('s')
                    ->join('s.participants', 'p')
                    ->addSelect('p')
                    ->join('s.organisateur', 'o')
                    ->addSelect('o')
                    ->join('s.etat', 'e')
                    ->addSelect('e')
                    ->andWhere('s.campus = :campus')
                    ->setParameter('campus', $filtre->getCampus())
                    ->andWhere("s.etat != {$etatArchivee->getId()}")
        ;

        if ($filtre->getSearch() !== '') {
            $qb->andWhere('s.nom LIKE :search')
                ->setParameter('search', "%{$filtre->getSearch()}%")
            ;
        }

        if ($filtre->getDateMin()) {
            $qb->andWhere('s.dateHeureDebut >= :dateMin')
                ->setParameter('dateMin', $filtre->getDateMin())
            ;
        }

        if ($filtre->getDateMax()) {
            $qb->andWhere('s.dateHeureDebut <= :dateMax')
                ->setParameter('dateMax', $filtre->getDateMax())
            ;
        }

        if ($filtre->isOrganisateurice()) {
            $qb->andWhere('s.organisateur = :organisateur')
                ->setParameter('organisateur', $participant)
            ;
        }

        if ($filtre->isInscrite()) {
            $qb->andWhere(':inscrite MEMBER OF s.participants')
                ->setParameter('inscrite', $participant)
            ;
        }

        if ($filtre->isNoninscrite()) {
            $qb->andWhere(':inscrite NOT MEMBER OF s.participants')
                ->setParameter('inscrite', $participant)
            ;
        }

        if ($filtre->isPassee()) {
            $qb->andWhere("s.etat = {$etatPassee->getId()}");
        }

        return $qb->getQuery()->getResult();
    }

//    /**
//     * @return Sortie[] Returns an array of Sortie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
