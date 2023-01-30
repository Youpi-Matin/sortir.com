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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
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

        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.participants', 'p')
            ->addSelect('p')
            ->join('s.organisateur', 'o')
            ->addSelect('o')
            ->join('s.etat', 'e')
            ->addSelect('e')
            ->andWhere('s.campus = :campus')
            ->setParameter('campus', $filtre->getCampus())//->andWhere('e.libelle != \'Archivée\'')
        ;

        if ($filtre->getSearch() !== '') {
            $qb->andWhere('s.nom LIKE :search')
                ->setParameter('search', "%{$filtre->getSearch()}%");
        }

        if ($filtre->getDateMin()) {
            $qb->andWhere('s.dateHeureDebut >= :dateMin')
                ->setParameter('dateMin', $filtre->getDateMin());
        }

        if ($filtre->getDateMax()) {
            $qb->andWhere('s.dateHeureDebut <= :dateMax')
                ->setParameter('dateMax', $filtre->getDateMax());
        }

        if ($filtre->isOrganisateurice()) {
            $qb->andWhere('s.organisateur = :organisateur')
                ->setParameter('organisateur', $participant);
        }

        if ($filtre->isInscrite()) {
            $qb->andWhere(':inscrite MEMBER OF s.participants')
                ->setParameter('inscrite', $participant);
        }

        if ($filtre->isNoninscrite()) {
            $qb->andWhere(':inscrite NOT MEMBER OF s.participants')
                ->setParameter('inscrite', $participant);
        }

        if ($filtre->isPassee()) {
            $qb->andWhere('e.libelle = \'Passée\'');
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
