<?php

namespace App\Repository;

use App\Entity\Horaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use Psr\Log\LoggerInterface;

/**
 * @extends ServiceEntityRepository<Horaire>
 *
 * @method Horaire|null find($id, $lockMode = null, $lockVersion = null)
 * @method Horaire|null findOneBy(array $criteria, array $orderBy = null)
 * @method Horaire[]    findAll()
 * @method Horaire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HoraireRepository extends ServiceEntityRepository
{
    private $logger;
    
    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, Horaire::class);
        $this->logger = $logger;
    }

    public function trouverHorairesPourLaSemaine(): array
    {
        return $this->findAll();
    }

    // public function trouverHorairesPourLaSemaine(\DateTimeInterface $debutSemaine, \DateTimeInterface $finSemaine): array
    // {
        
        
    //     $horaires = $this->createQueryBuilder('h')
    //         ->where('h.jour >= :debutSemaine')
    //         ->andWhere('h.jour <= :finSemaine')
    //         ->setParameter('debutSemaine', $debutSemaine->format('Y-m-d'))
    //         ->setParameter('finSemaine', $finSemaine->format('Y-m-d'))
    //         ->getQuery()
    //         ->getResult();
    //     return $horaires;

    // }



    // public function trouverParJour($jour): ?Horaire
    // {
    //     $horaire = $this->createQueryBuilder('h')
    //     ->andWhere('h.jour = :jour')
    //     ->setParameter('jour', $jour)
    //     ->getQuery()
    //     ->getOneOrNullResult();

    //     return $horaire;
    // }

    //    /**
    //     * @return Horaire[] Returns an array of Horaire objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('h.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Horaire
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
