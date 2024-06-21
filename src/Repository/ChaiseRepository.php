<?php

namespace App\Repository;

use App\Entity\Chaise;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Chaise>
 *
 * @method Chaise|null find($id, $lockMode = null, $lockVersion = null)
 * @method Chaise|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chaise[]    findAll()
 * @method Chaise[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChaiseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chaise::class);
    }

//    /**
//     * @return Chaise[] Returns an array of Chaise objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Chaise
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
