<?php

namespace App\Repository;

use App\Entity\HistoriqueSuppression;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<HistoriqueSuppression>
 */
class HistoriqueSuppressionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoriqueSuppression::class);
    }

    //    /**
    //     * @return HistoriqueSuppression[] Returns an array of HistoriqueSuppression objects
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

    //    public function findOneBySomeField($value): ?HistoriqueSuppression
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }


    /**
     * @return array
     */
    public function historiqueParEtablissementParPeriodePaginated($search, $etablissement, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('h')
            ->leftJoin('h.user', 'u')
            ->Where('u.etablissement = :etablissement')
            ->andWhere('h.dateOperation BETWEEN :startDate AND :endDate');
            if ($search) {
                $query->andWhere('u.matricule LIKE :search OR u.prenom LIKE :search OR u.telephone LIKE :search OR h.motif LIKE :search OR h.information LIKE :search OR h.type LIKE :search ')
                ->setParameter('search', '%' . $search . '%');
            }
            $query->setParameter('etablissement', $etablissement)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('h.dateOperation', 'DESC')
            ->addOrderBy('u.prenom')
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
     * @return array
     */
    public function historiqueParEtablissementParOrigineParPeriodePaginated($search, $origine, $etablissement, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('h')
            ->leftJoin('h.user', 'u')
            ->Where('u.etablissement = :etablissement')
            ->andWhere('h.origine = :origine')
            ->andWhere('h.dateOperation BETWEEN :startDate AND :endDate');
            if ($search) {
                $query->andWhere('u.matricule LIKE :search OR u.prenom LIKE :search OR u.telephone LIKE :search OR h.motif LIKE :search OR h.information LIKE :search OR h.type LIKE :search ')
                ->setParameter('search', '%' . $search . '%');
            }
            $query->setParameter('etablissement', $etablissement)
            ->setParameter('origine', $origine)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('h.dateOperation', 'DESC')
            ->addOrderBy('u.prenom')
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


    public function findCountSup($type, $etablissement, $promo): ?int
    {
        $result = $this->createQueryBuilder('h')
            ->select('Count(h.id)')
            ->andWhere('h.type = :type')
            // ->andWhere('h.etablissement = :etab')
            ->andWhere('h.promo = :promo')
            // ->setParameter('etab', $etablissement)
            ->setParameter('promo', $promo)
            ->setParameter('type', $type)
            ->getQuery()
            ->getSingleScalarResult();
        return $result;
    }
}
