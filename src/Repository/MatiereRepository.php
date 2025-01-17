<?php

namespace App\Repository;

use App\Entity\Matiere;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Matiere>
 */
class MatiereRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Matiere::class);
    }

//    /**
//     * @return Matiere[] Returns an array of Matiere objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Matiere
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

   /**
    * @return Matiere[] Returns an array of Matiere objects
    */
   public function listeMatiereParFormation($formation): array
   {
       return $this->createQueryBuilder('m')
           ->andWhere('m.formation = :formation')
           ->setParameter('formation', $formation)
           ->orderBy('m.nom', 'ASC')
           ->getQuery()
           ->getResult()
       ;
   }


   /**
     * @return Matiere[] Returns an array of Matiere objects
     */
    public function listeMatiereParFormations(array $formations): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.formation IN (:formations)')
            ->setParameter('formations', $formations)
            ->orderBy('m.formation', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Matiere[] Returns an array of Matiere objects
     */
    public function listeMatiereParCursus($cursus): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.formation', 'f')
            ->andWhere('f.cursus = :cursus')
            ->setParameter('cursus', $cursus)
            ->orderBy('m.formation', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }


    
}
