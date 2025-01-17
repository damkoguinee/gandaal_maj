<?php

namespace App\Repository;

use App\Entity\PaiementActiviteScolaire;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<PaiementActiviteScolaire>
 */
class PaiementActiviteScolaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaiementActiviteScolaire::class);
    }

    //    /**
    //     * @return PaiementActiviteScolaire[] Returns an array of PaiementActiviteScolaire objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?PaiementActiviteScolaire
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
        * @return PaiementEleve[] Returns an array of PaiementEleve objects
        */
        public function paiementEleveParReference($reference): array
        {
            return $this->createQueryBuilder('p')
                ->andWhere('p.reference = :reference')
                ->setParameter('reference', $reference)
                ->orderBy('p.id', 'ASC')
                ->getQuery()
                ->getResult()
            ;
        }
 
        /**
         * @return PaiementEleve[] Returns an array of PaiementEleve objects
         */
         public function paiementEleveLies($reference, $id): array
         {
             return $this->createQueryBuilder('p')
                  ->andWhere('p != :id')
                 ->andWhere('p.reference = :reference')
                 ->setParameter('id', $id)
                 ->setParameter('reference', $reference)
                 ->orderBy('p.id', 'ASC')
                 ->getQuery()
                 ->getResult()
             ;
         }


    public function cumulPaiementEleve($inscription, $periode, $promo): ?float
    {
        $result = $this->createQueryBuilder('p')
            ->select('SUM(p.montant) as total')
            ->andWhere('p.inscription = :inscription')
            ->andWhere('p.periode = :periode')
            ->andWhere('p.promo = :promo')
            ->setParameter('inscription', $inscription)
            ->setParameter('periode', $periode)
            ->setParameter('promo', $promo)
            ->getQuery()
            ->getOneOrNullResult();
            
        return $result !== null ? (float) $result['total'] : null;

    }

    /**
     * @return array
     */
    public function findPaiementActiviteByEtablissementPaginated($etablissement, $promo, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('p')
            ->Where('p.etablissement = :etablissement')
            ->andWhere('p.promo = :promo')
            ->andWhere('p.dateOperation BETWEEN :startDate AND :endDate')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('promo', $promo)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('p.dateOperation', 'DESC')
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
    public function findPaiementActiviteByEtablissementBySearchPaginated($etablissement, $eleve, $promo, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('p')
            ->leftJoin('p.inscription', 'i')
            ->Where('p.etablissement = :etablissement')
            ->andWhere('i.eleve = :eleve')
            ->andWhere('p.promo = :promo')
            ->andWhere('p.dateOperation BETWEEN :startDate AND :endDate')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('promo', $promo)
            ->setParameter('eleve', $eleve)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('p.dateOperation', 'DESC')
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
    public function findPaiementActiviteByEtablissementByActivitePaginated($etablissement, $activite, $promo, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('p')
            ->leftJoin('p.inscription', 'i')
            ->leftJoin('i.tarifActivite', 't')
            ->Where('p.etablissement = :etablissement')
            ->andWhere('t.activite = :activite')
            ->andWhere('p.promo = :promo')
            ->andWhere('p.dateOperation BETWEEN :startDate AND :endDate')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('promo', $promo)
            ->setParameter('activite', $activite)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('p.dateOperation', 'DESC')
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

    public function findMaxId($etablissement, $promo): ?int
    {
        $result = $this->createQueryBuilder('p')
            ->select('MAX(p.id)')
            ->andWhere('p.etablissement = :etab')
            ->andWhere('p.promo = :promo')
            ->setParameter('etab', $etablissement)
            ->setParameter('promo', $promo)
            ->getQuery()
            ->getSingleScalarResult();
        return $result;
    }

    public function findCountId($etablissement, $promo): ?int
    {
        $result = $this->createQueryBuilder('p')
            ->select('Count(p.id)')
            ->andWhere('p.etablissement = :etab')
            ->andWhere('p.promo = :promo')
            ->setParameter('etab', $etablissement)
            ->setParameter('promo', $promo)
            ->getQuery()
            ->getSingleScalarResult();
        return $result;
    }

    
}
