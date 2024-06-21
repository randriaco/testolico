<?php

namespace App\Repository;

use App\Entity\DiningTable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DiningTable>
 *
 * @method DiningTable|null find($id, $lockMode = null, $lockVersion = null)
 * @method DiningTable|null findOneBy(array $criteria, array $orderBy = null)
 * @method DiningTable[]    findAll()
 * @method DiningTable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DiningTableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiningTable::class);
    }

//    /**
//     * @return DiningTable[] Returns an array of DiningTable objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DiningTable
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
