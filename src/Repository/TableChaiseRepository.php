<?php

namespace App\Repository;

use App\Entity\TableChaise;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TableChaise>
 *
 * @method TableChaise|null find($id, $lockMode = null, $lockVersion = null)
 * @method TableChaise|null findOneBy(array $criteria, array $orderBy = null)
 * @method TableChaise[]    findAll()
 * @method TableChaise[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TableChaiseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TableChaise::class);
    }

//    /**
//     * @return TableChaise[] Returns an array of TableChaise objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TableChaise
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
