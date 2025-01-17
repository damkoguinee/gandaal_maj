<?php

namespace App\Repository;

use App\Entity\FraisScolarite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FraisScolarite>
 */
class FraisScolariteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FraisScolarite::class);
    }

    //    /**
    //     * @return FraisScolarite[] Returns an array of FraisScolarite objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?FraisScolarite
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

        // /**
        // * @return FraisScolarite[] Returns an array of FraisScolarite objects
        // */
        // public function montantTotalFraisScolariteParFormation($formation, $promo): array
        // {
        //     return $this->createQueryBuilder('f')
        //         ->select('SUM(f.montant) as total', 'f as fraisScol',)
        //         ->andWhere('f.formation = :formation')
        //         ->andWhere('f.promo = :promo')
        //         ->setParameter('formation', $formation)
        //         ->setParameter('promo', $promo)
        //         ->getQuery()
        //         ->getResult()
        //     ;
        // }

        /**
            * @return FraisScolarite[] Returns an array of FraisScolarite objects
            */
        public function fraisScolariteGroupeParFormation($etablissement, $promo): array
        {
            return $this->createQueryBuilder('f')
                ->leftJoin('f.formation','fo')
                ->andWhere('f.promo = :promo')
                ->andWhere('f.etablissement = :etablissement')
                ->addGroupBy('f.formation')
                ->addGroupBy('f.tranche')
                ->setParameter('promo', $promo)
                ->setParameter('etablissement', $etablissement)
                ->orderBy('fo.id', 'ASC')
                ->addOrderBy('f.tranche', 'ASC')
                ->getQuery()
                ->getResult()
            ;
        }

        
        public function montantTotalFraisScolariteParCursus($cursus, $promo): ?float
        {
            return $this->createQueryBuilder('f')
                ->select('SUM(f.montant) as total')
                ->leftJoin('f.formation', 'form')
                ->andWhere('form.cursus = :cursus')
                ->andWhere('f.promo = :promo')
                ->setParameter('cursus', $cursus)
                ->setParameter('promo', $promo)
                ->getQuery()
                ->getSingleScalarResult()
            ;
        }

        public function montantTotalFraisScolariteParFormation($formation, $promo): ?float
        {
            return $this->createQueryBuilder('f')
                ->select('SUM(f.montant) as total')
                ->andWhere('f.formation = :formation')
                ->andWhere('f.promo = :promo')
                ->setParameter('formation', $formation)
                ->setParameter('promo', $promo)
                ->getQuery()
                ->getSingleScalarResult()
            ;
        }

        public function montantTotalFraisScolariteParTrancheParFormation($tranche, $formation, $promo): ?float
        {
            return $this->createQueryBuilder('f')
                ->select('SUM(f.montant) as total')
                ->andWhere('f.formation = :formation')
                ->andWhere('f.tranche = :tranche')
                ->andWhere('f.promo = :promo')
                ->setParameter('formation', $formation)
                ->setParameter('tranche', $tranche)
                ->setParameter('promo', $promo)
                ->getQuery()
                ->getSingleScalarResult()
            ;
        }
}
