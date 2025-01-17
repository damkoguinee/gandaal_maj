<?php

namespace App\Repository;

use App\Entity\ClasseRepartition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClasseRepartition>
 */
class ClasseRepartitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClasseRepartition::class);
    }

    //    /**
    //     * @return ClasseRepartition[] Returns an array of ClasseRepartition objects
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

    //    public function findOneBySomeField($value): ?ClasseRepartition
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

       /**
        * @return ClasseRepartition[] Returns an array of ClasseRepartition objects
        */
       public function listeDesClassesParEtablissementParPromo($etablissement, $promo): array
       {
           return $this->createQueryBuilder('c')
                ->leftJoin('c.formation', 'f')
                ->leftJoin('f.cursus', 'cs')
                ->andWhere('c.promo = :promo')
               ->andWhere('cs.etablissement = :etablissement')
               ->setParameter('etablissement', $etablissement)
               ->setParameter('promo', $promo)
               ->orderBy('cs.id', 'ASC')
               ->addOrderBy('c.id', 'ASC')
               ->getQuery()
               ->getResult()
           ;
       }

       /**
        * @return ClasseRepartition[] Returns an array of ClasseRepartition objects
        */
        public function listeDesClassesParCursusParPromo($cursus, $promo): array
        {
            return $this->createQueryBuilder('c')
                 ->leftJoin('c.formation', 'f')
                 ->andWhere('f.cursus = :cursus')
                 ->andWhere('c.promo = :promo')
                ->setParameter('cursus', $cursus)
                ->setParameter('promo', $promo)
                ->orderBy('c.formation', 'ASC')
                ->addOrderBy('c.nom', 'ASC')
                ->getQuery()
                ->getResult()
            ;
        }
}
