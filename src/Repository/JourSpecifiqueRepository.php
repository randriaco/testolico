<?php

namespace App\Repository;

use App\Entity\JourSpecifique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JourSpecifique>
 *
 * @method JourSpecifique|null find($id, $lockMode = null, $lockVersion = null)
 * @method JourSpecifique|null findOneBy(array $criteria, array $orderBy = null)
 * @method JourSpecifique[]    findAll()
 * @method JourSpecifique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JourSpecifiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JourSpecifique::class);
    }

    // Ajoutez votre méthode personnalisée ici
    public function trouverParPeriode(\DateTimeInterface $debut, \DateTimeInterface $fin)
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.date >= :debut')
            ->andWhere('j.date <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->getQuery()
            ->getResult();
    }

}
