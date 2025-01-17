<?php

namespace App\Repository;

use App\Entity\FonctionnementScolaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FonctionnementScolaire>
 */
class FonctionnementScolaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FonctionnementScolaire::class);
    }

    //    /**
    //     * @return FonctionnementScolaire[] Returns an array of FonctionnementScolaire objects
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

    //    public function findOneBySomeField($value): ?FonctionnementScolaire
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * @return FonctionnementScolaire[] Returns an array of FonctionnementScolaire objects
     */
    public function recuperationTrimestre($periode, $etablissement, $promo): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.etablissement = :etablissement')
            ->andWhere('f.promo = :promo')
            ->andWhere(':periode BETWEEN f.dateDebut AND f.dateFin')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('promo', $promo)
            ->setParameter('periode', $periode) // Ajouter le paramètre pour $periode
            ->getQuery()
            ->getResult();
    }

}
