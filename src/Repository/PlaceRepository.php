<?php

namespace App\Repository;

use App\Entity\Place;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Place>
 *
 * @method Place|null find($id, $lockMode = null, $lockVersion = null)
 * @method Place|null findOneBy(array $criteria, array $orderBy = null)
 * @method Place[]    findAll()
 * @method Place[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Place::class);
    }

    public function findPlacesOrderedByDate()
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where('p.date >= :today') // Assurez-vous d'inclure les places d'aujourd'hui
        ->setParameter('today', new \DateTime()) // Convertit aujourd'hui en objet DateTime sans spécifier explicitement le type
        ->orderBy('p.date', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findByDateBeforeToday()
    {
        return $this->createQueryBuilder('p')
            ->where('p.date < :today')
            ->setParameter('today', new \DateTime())
            ->getQuery()
            ->getResult();
    }
}
