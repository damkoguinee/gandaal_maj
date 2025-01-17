<?php

namespace App\Repository;

use App\Entity\ConfigurationLogiciel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConfigurationLogiciel>
 *
 * @method ConfigurationLogiciel|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConfigurationLogiciel|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConfigurationLogiciel[]    findAll()
 * @method ConfigurationLogiciel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfigurationLogicielRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConfigurationLogiciel::class);
    }

//    /**
//     * @return ConfigurationLogiciel[] Returns an array of ConfigurationLogiciel objects
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

//    public function findOneBySomeField($value): ?ConfigurationLogiciel
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
