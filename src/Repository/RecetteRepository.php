<?php

namespace App\Repository;

use App\Entity\Recette;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Recette>
 *
 * @method Recette|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recette|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recette[]    findAll()
 * @method Recette[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecetteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recette::class);
    }

//    /**
//     * @return Recette[] Returns an array of Recette objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Recette
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * @return array
     */
    public function findRecetteByLieuPaginated($etablissement, $startDate, $endDate, int $pageEnCours, int $limit, $promo = NULL): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('d')
            ->Where('d.etablissement = :etablissement');
            if ($promo) {
                $query->andWhere('d.promo = :promo')
                    ->setParameter('promo' , $promo);
            }
            $query->andWhere('d.dateOperation BETWEEN :startDate AND :endDate')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('d.dateOperation', 'DESC')
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
    public function findRecetteByLieuBySearchPaginated($etablissement, $categorie, $startDate, $endDate, int $pageEnCours, int $limit, $promo = NULL): array
    {
        // $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('d')
            ->Where('d.etablissement = :etablissement');
            if ($promo) {
                $query->andWhere('d.promo = :promo')
                    ->setParameter('promo' , $promo);
            }
            $query->andWhere('d.categorie = :cat')
            ->andWhere('d.dateOperation BETWEEN :startDate AND :endDate')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('cat', $categorie)
            ->orderBy('d.dateOperation', 'DESC')
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
    public function totalRecetteParPeriodeParLieu($etablissement, $startDate, $endDate, $promo = NULL): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $query = $this->createQueryBuilder('d')
            ->select('SUM(d.montant) as montantTotal', 'dev.nom as nomDevise', 'dev.id as id_devise')
            ->leftJoin('d.devise', 'dev')
            ->Where('d.etablissement = :etablissement');
            if ($promo) {
                $query->andWhere('d.promo = :promo')
                    ->setParameter('promo' , $promo);
            }
            $query->andWhere('d.dateOperation BETWEEN :startDate AND :endDate')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->groupBy('d.devise');
        return $query->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return array
     */
    public function totalRecetteParPeriodeParLieuParCategorie($etablissement, $categorie, $startDate, $endDate, $promo = NULL): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $query = $this->createQueryBuilder('d')
            ->select('SUM(d.montant) as montantTotal', 'dev.nom as nomDevise', 'dev.id as id_devise')
            ->leftJoin('d.devise', 'dev')
            ->Where('d.etablissement = :etablissement');
            if ($promo) {
                $query->andWhere('d.promo = :promo')
                    ->setParameter('promo' , $promo);
            }
            $query->andWhere('d.categorie = :cat')
            ->andWhere('d.dateOperation BETWEEN :startDate AND :endDate')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('cat', $categorie)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->groupBy('d.devise');
            return $query->getQuery()
            ->getResult()
            ;
    }

    public function totalRecetteParPeriodeParLieuParDevise($startDate, $endDate, $etablissement, $devise, $promo = NULL): ?float
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $query = $this->createQueryBuilder('d')
            ->select('SUM(d.montant) as montantTotal')
            ->leftJoin('d.devise', 'dev')
            ->Where('d.etablissement = :etablissement');
            if ($promo) {
                $query->andWhere('d.promo = :promo')
                    ->setParameter('promo' , $promo);
            }
            $query->andWhere('d.devise = :devise')
            ->andWhere('d.dateOperation BETWEEN :startDate AND :endDate')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('devise', $devise)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);
            return $query->getQuery()
            ->getSingleScalarResult();
            ;
    }

    public function totalRecetteParPeriode($startDate, $endDate, $promo = NULL): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $query = $this->createQueryBuilder('d')
            ->select('SUM(d.montant) as montantTotal', 'dev.id as id_devise')
            ->leftJoin('d.devise', 'dev');
            if ($promo) {
                $query->andWhere('d.promo = :promo')
                    ->setParameter('promo' , $promo);
            }
            $query->andWhere('d.dateOperation BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->addGroupBy('d.devise');
            return $query->getQuery()
            ->getResult();
            ;
    }

    

    public function findMaxId($etablissement): ?int
    {
        $result = $this->createQueryBuilder('d')
            ->select('MAX(d.id)')
            ->andWhere('d.etablissement = :etab')
            ->setParameter('etab', $etablissement)
            ->getQuery()
            ->getSingleScalarResult();
        return $result;
    }

    public function findCountId($etablissement, $promo): ?int
    {
        $result = $this->createQueryBuilder('r')
            ->select('Count(r.id)')
            ->Where('r.etablissement = :etablissement')
            ->andWhere('r.promo = :promo')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('promo', $promo)
            ->getQuery()
            ->getSingleScalarResult();
        return $result;
    }
}
