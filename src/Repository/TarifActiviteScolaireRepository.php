<?php

namespace App\Repository;

use App\Entity\TarifActiviteScolaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TarifActiviteScolaire>
 */
class TarifActiviteScolaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TarifActiviteScolaire::class);
    }

    //    /**
    //     * @return TarifActiviteScolaire[] Returns an array of TarifActiviteScolaire objects
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

    //    public function findOneBySomeField($value): ?TarifActiviteScolaire
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
        * @return TarifActiviteScolaire[] Returns an array of TarifActiviteScolaire objects
     */

       public function tarifActiviteParEtablissementParPromo($etablissement, $promo): array
       {
           return $this->createQueryBuilder('t')
                ->leftJoin('t.activite', 'a')
               ->andWhere('t.promo = :promo')
               ->andWhere('a.etablissement = :etablissement')
               ->setParameter('promo', $promo)
               ->setParameter('etablissement', $etablissement)
               ->getQuery()
               ->getResult()
           ;
       }
}
