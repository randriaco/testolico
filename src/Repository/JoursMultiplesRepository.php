<?php

namespace App\Repository;

use App\Entity\JoursMultiples;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JoursMultiples>
 *
 * @method JoursMultiples|null find($id, $lockMode = null, $lockVersion = null)
 * @method JoursMultiples|null findOneBy(array $criteria, array $orderBy = null
 * @method JoursMultiples[]    findAll()
 * @method JoursMultiples[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JoursMultiplesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JoursMultiples::class);
    }

    // méthode pour trouver les fermetures dans une période spécifique :
    public function trouverFermeturesParPeriode(\DateTimeInterface $debut, \DateTimeInterface $fin)
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.debutFermeture >= :debut')
            ->andWhere('j.finFermeture <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->getQuery()
            ->getResult();
    }

}