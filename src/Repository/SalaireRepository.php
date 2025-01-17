<?php

namespace App\Repository;

use App\Entity\Salaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Salaire>
 */
class SalaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Salaire::class);
    }

    //    /**
    //     * @return Salaire[] Returns an array of Salaire objects
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

    //    public function findOneBySomeField($value): ?Salaire
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function salairePersonnelParPromo($personnel, $promo): ?float
    {
        $result = $this->createQueryBuilder('s')
            ->select('s.salaireBrut')
            ->andWhere('s.user = :personnel')
            ->andWhere('s.promo = :promo')
            ->setParameter('personnel', $personnel) 
            ->setParameter('promo', $promo)
            ->getQuery()
            ->getOneOrNullResult();
                
        return $result !== null ? (float) $result['salaireBrut'] : 0;
    }
}