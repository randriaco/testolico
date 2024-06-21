<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commande>
 *
 * @method Commande|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commande|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commande[]    findAll()
 * @method Commande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    // Exemple de méthode personnalisée
    public function findByUser($user)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.date', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.status = :status')
            ->setParameter('status', $status)
            ->orderBy('c.montant', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find commandes by date and status
     *
     * @param \DateTimeInterface $date The date to filter commandes
     * @param string $status The status to filter commandes
     * @return Commande[]
     */
    public function findByDateAndStatus(\DateTimeInterface $date, string $status): array
    {
        // return $this->createQueryBuilder('c')
        // ->where('c.date = :date AND c.status = :status')
        // ->setParameter('date', $date->format('Y-m-d'))
        // ->setParameter('status', $status)
        // ->orderBy('c.montant', 'DESC')
        // ->getQuery()
        // ->getResult();

        return $this->createQueryBuilder('c')
        ->andWhere('c.date LIKE :date')
        ->andWhere('c.status = :status')
        ->setParameter('date', $date->format('Y-m-d') . '%')  // Ajouter % pour matcher les heures
        ->setParameter('status', $status)
        ->orderBy('c.date', 'DESC')
        ->getQuery()
        ->getResult();
    }

}