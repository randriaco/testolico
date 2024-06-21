<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Reservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reservation[]    findAll()
 * @method Reservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    // Ajoutez vos méthodes de requête personnalisées ici

    // Exemple de méthode personnalisée
    public function findReservationsByUser($userId)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('r.dateReservation', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findReservationsWithCommandes(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.hasCommande = :val')
            ->setParameter('val', true)
            ->getQuery()
            ->getResult();
    }

    public function findAllFutureReservations()
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.commande', 'c')
            ->addSelect('c') // Inclut les commandes dans le résultat
            ->where('r.dateReservation >= :yesterday')
            ->setParameter('yesterday', new \DateTime('yesterday'))
            ->orderBy('r.dateReservation', 'ASC')
            ->addOrderBy('r.horaireReservation', 'ASC')
            ->addOrderBy('c.montant', 'DESC') // Tri décroissant par montant de la commande
            ->getQuery()
            ->getResult();
    }

    /**
     * Find reservations by specific date
     *
     * @param \DateTimeInterface $date The date to filter reservations
     * @return Reservation[]
     */
    public function findByDate(\DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.dateReservation = :date')
            ->setParameter('date', $date->format('Y-m-d'))
            ->leftJoin('r.commande', 'c') // Assuming there's a OneToOne relation named 'commande'
            ->addSelect('c') // Fetch associated command as well
            ->orderBy('r.dateReservation', 'ASC')
            ->orderBy('r.horaireReservation', 'ASC') // Assuming you want to order by reservation time as well
            ->addOrderBy('c.montant', 'DESC') // Assuming you want to order by the amount if there's a commande
            ->getQuery()
            ->getResult();
    }
    
}