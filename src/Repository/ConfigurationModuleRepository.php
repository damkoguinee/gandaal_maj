<?php

namespace App\Repository;

use App\Entity\ConfigurationModule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConfigurationModule>
 */
class ConfigurationModuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConfigurationModule::class);
    }

//    /**
//     * @return ConfigurationModule[] Returns an array of ConfigurationModule objects
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

//    public function findOneBySomeField($value): ?ConfigurationModule
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

   /**
     * Retourne un module correspondant au cursus, à la période et à l'établissement donnés.
     *
     * @param mixed $cursus Le cursus recherché
     * @param string $periode La période recherchée
     * @param mixed $etablissement L'établissement concerné
     * @return ConfigurationModule|null Retourne un module ou null s'il n'y a pas de correspondance
     */
    public function moduleParCursusParPeriodeParEtablissement($cursus, $periode, $etablissement): ?ConfigurationModule
    {
        return $this->createQueryBuilder('c')
            ->andWhere(':cursus MEMBER OF c.cursus')
            ->andWhere('c.etablissement = :etablissement')
            ->andWhere('c.periode = :periode')
            ->setParameter('cursus', $cursus)
            ->setParameter('etablissement', $etablissement)
            ->setParameter('periode', $periode)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }


}
