<?php

namespace App\Repository;

use App\Entity\AvanceSalairePersonnel;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<AvanceSalairePersonnel>
 */
class AvanceSalairePersonnelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AvanceSalairePersonnel::class);
    }

    //    /**
    //     * @return AvanceSalairePersonnel[] Returns an array of AvanceSalairePersonnel objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?AvanceSalairePersonnel
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }


     /**
        * @return AvanceSalairePersonnel[] Returns an array of AvanceSalairePersonnel objects
        */
        public function avancePersonnelGroupeParPeriode($personnel, $etablissement, $promo): array
        {
            return $this->createQueryBuilder('a')
                ->andWhere('a.personnelActif = :personnel')
                ->andWhere('a.etablissement = :etablissement')
                ->andWhere('a.promo = :promo')
                ->setParameter('personnel', $personnel)
                ->setParameter('etablissement', $etablissement)
                ->setParameter('promo', $promo)
                ->addGroupBy('a.periode')
                ->orderBy('a.id', 'ASC')
                ->getQuery()
                ->getResult()
            ;
        }

        /**
        * @return AvanceSalairePersonnel[] Returns an array of AvanceSalairePersonnel objects
        */
        public function avanceParReference($reference): array
        {
            return $this->createQueryBuilder('a')
                ->andWhere('a.reference = :reference')
                ->setParameter('reference', $reference)
                ->orderBy('a.id', 'ASC')
                ->getQuery()
                ->getResult()
            ;
        }
 
        /**
         * @return AvanceSalairePersonnel[] Returns an array of AvanceSalairePersonnel objects
         */
         public function avanceLies($reference, $id): array
         {
             return $this->createQueryBuilder('a')
                  ->andWhere('a != :id')
                 ->andWhere('a.reference = :reference')
                 ->setParameter('id', $id)
                 ->setParameter('reference', $reference)
                 ->orderBy('a.id', 'ASC')
                 ->getQuery()
                 ->getResult()
             ;
         }

         /**
     * @return array
     */
    public function listeDesAvancesParEtablissementPaginated($etablissement, $promo, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('a')
            ->Where('a.etablissement = :etablissement')
            ->andWhere('a.promo = :promo')
            ->andWhere('a.dateOperation BETWEEN :startDate AND :endDate')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('promo', $promo)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('a.dateOperation', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult(($pageEnCours * $limit) - $limit);
        $paginator = new Paginator($query);
        $data = $paginator->getQuery()->getResult();

        $nbrePages = ceil($paginator->count() / $limit);
        
         $result['data'] = $data;
         $result['nbrePages'] = $nbrePages;
         $result['pageEncours'] = $pageEnCours;
         $result['limit'] = $limit;
         
        return $result;
    }

    /**
     * @return int|null Returns the sum of hours for the specified personnel
     */
    public function sommeAvancePersonnel($personnelId, $date): ?int
    {
        // Extraire le mois et l'année de la date fournie
        $year = substr($date, 0, 4);
        $month = substr($date, 5, 2);

        // Créer la date de début et de fin pour le mois donné
        $startDate = new \DateTime("{$year}-{$month}-01");
        $endDate = clone $startDate;
        $endDate->modify('last day of this month');

        return $this->createQueryBuilder('a')
            ->select('SUM(a.montant) as totalMontant')
            ->andWhere('a.personnelActif = :personnelId')
            ->andWhere('a.periode BETWEEN :startDate AND :endDate')
            ->setParameter('personnelId', $personnelId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findMaxId($etablissement, $promo): ?int
    {
        $result = $this->createQueryBuilder('a')
            ->select('MAX(a.id)')
            ->andWhere('a.etablissement = :etab')
            ->andWhere('a.promo = :promo')
            ->setParameter('etab', $etablissement)
            ->setParameter('promo', $promo)
            ->getQuery()
            ->getSingleScalarResult();
        return $result;
    }

    public function findCountId($etablissement, $promo): ?int
    {
        $result = $this->createQueryBuilder('a')
            ->select('Count(a.id)')
            ->andWhere('a.etablissement = :etab')
            ->andWhere('a.promo = :promo')
            ->setParameter('etab', $etablissement)
            ->setParameter('promo', $promo)
            ->getQuery()
            ->getSingleScalarResult();
        return $result;
    }
}
