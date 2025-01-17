<?php

namespace App\Repository;

use App\Entity\MouvementCaisse;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<MouvementCaisse>
 */
class MouvementCaisseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MouvementCaisse::class);
    }

    //    /**
    //     * @return MouvementCaisse[] Returns an array of MouvementCaisse objects
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

    //    public function findOneBySomeField($value): ?MouvementCaisse
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }


    /**
     * @return array
     */
    public function soldeCaisseParPeriodeParLieu($startDate, $endDate, $promo, $etablissement, $devises, $caisses): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $query = $this->createQueryBuilder('m');
        $results = $query
            ->select('SUM(m.montant) as solde', 'c.id as id_caisse', 'c.nom as designation', 'd.nom as nomDevise', 'd.id as id_devise')
            ->leftJoin('m.devise', 'd')
            ->leftJoin('m.caisse', 'c')
            ->Where('m.etablissement = :etablissement')
            ->andWhere('m.dateOperation BETWEEN :startDate AND :endDate')
            ->andWhere('m.promo = :promo')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('promo', $promo)
            ->groupBy('m.devise', 'm.caisse')
            ->orderBy('d.id')
            ->getQuery()
            ->getResult();

        // Créer un tableau pour stocker les résultats finaux
        $finalResults = [];
        foreach ($devises as $devise) {
            foreach ($caisses as $caisse) {
                $trouve = false;
                foreach ($results as $resultat) {
                    if ($resultat['id_devise'] === $devise->getId() && $resultat['id_caisse'] === $caisse->getId()) {
                        $finalResults[] = $resultat;
                        $trouve = true;
                        break;
                    }
                }
                if (!$trouve) {
                    // Si la devise et la caisse ne sont pas trouvées dans les résultats, ajouter une entrée avec un solde de zéro
                    $finalResults[] = [
                        'solde' => '0.00', 
                        'id_caisse' => $caisse->getId(),
                        'designation' => $caisse->getNom(),
                        'nomDevise' => $devise->getNom(),
                        'id_devise' => $devise->getId()
                    ];
                }
            }
        }
        return $finalResults;
    }


    /**
     * @return array
     */
    public function soldeCaisseParDeviseParLieu($startDate, $endDate, $promo, $etablissement, $devises): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $query = $this->createQueryBuilder('m');
        $results = $query
            ->select('SUM(m.montant) as solde', 'd.nom as nomDevise', 'd.id as id_devise')
            ->Where('m.etablissement = :etablissement')
            ->andWhere('m.dateOperation BETWEEN :startDate AND :endDate')
            ->andWhere('m.promo = :promo')
            ->leftJoin('m.devise', 'd')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('promo', $promo)
            ->groupBy('m.devise')
            ->orderBy('d.id')
            ->getQuery()
            ->getResult();

        // Créer un tableau pour stocker les résultats finaux
        $finalResults = [];
        foreach ($devises as $devise) {
            $trouve = false;
            foreach ($results as $resultat) {
                if ($resultat['id_devise'] === $devise->getId()) {
                    $finalResults[] = $resultat;
                    $trouve = true;
                    break;
                }
            }
            if (!$trouve) {
                // Si la devise et la caisse ne sont pas trouvées dans les résultats, ajouter une entrée avec un solde de zéro
                $finalResults[] = [
                    'solde' => '0.00', 
                    'nomDevise' => $devise->getNom(),
                    'id_devise' => $devise->getId()
                ];
            }
        }
        return $finalResults;
    }


    /**
     * @return array
     */
    public function soldeCaisseParPeriodeParTypeParLieuParDevise($startDate, $endDate, $promo, $etablissement, $devise): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        return $this->createQueryBuilder('m')
            ->select('SUM(m.montant) as solde, COUNT(m.id) as nbre, m as mouvement')
            ->Where('m.etablissement = :etablissement')
            ->andWhere('m.devise = :devise')
            ->andWhere('m.dateOperation BETWEEN :startDate AND :endDate')
            ->andWhere('m.promo = :promo')
            ->addOrderBy('m.id')
            ->groupBy('m.typeMouvement')
            ->addGroupBy('m.modePaie')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('devise', $devise)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('promo', $promo)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array
     */
    public function soldeCaisseParPeriodeParTypeParLieuParDeviseParCaisse($startDate, $endDate, $promo, $etablissement, $devise, $caisse): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        return $this->createQueryBuilder('m')
            ->select('SUM(m.montant) as solde, COUNT(m.id) as nbre, m as mouvement')
            ->Where('m.etablissement = :etablissement')
            ->andWhere('m.devise = :devise')
            ->andWhere('m.caisse = :caisse')
            ->andWhere('m.dateOperation BETWEEN :startDate AND :endDate')
            ->andWhere('m.promo = :promo')
            ->addOrderBy('m.id')
            ->groupBy('m.typeMouvement')
            ->addGroupBy('m.modePaie')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('devise', $devise)
            ->setParameter('caisse', $caisse)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('promo', $promo)
            ->getQuery()
            ->getResult();
    }

    public function findSoldeCaisse($caisse, $devise): ?float
   {
       return $this->createQueryBuilder('m')
           ->select('sum(m.montant) as montant')
           ->andWhere('m.caisse = :val')
           ->andWhere('m.devise = :devise')
           ->setParameter('val', $caisse)
           ->setParameter('devise', $devise)
           ->getQuery()
           ->getSingleScalarResult()
       ;
   }

   public function findSoldeCaisseByPromo($caisse, $devise, $promo): ?float
   {
       return $this->createQueryBuilder('m')
           ->select('sum(m.montant) as montant')
           ->andWhere('m.caisse = :val')
           ->andWhere('m.devise = :devise')
           ->andWhere('m.promo = :promo')
           ->setParameter('val', $caisse)
           ->setParameter('devise', $devise)
           ->setParameter('promo', $promo)
           ->getQuery()
           ->getSingleScalarResult()
       ;
   }

   public function soldeCaisseParDeviseParPeriode($caisse, $devise, $promo, $startDate, $endDate): ?float
   {
       return $this->createQueryBuilder('m')
           ->select('sum(m.montant) as montant')
           ->andWhere('m.caisse = :val')
           ->andWhere('m.promo = :promo')
           ->andWhere('m.devise = :devise')
           ->andWhere('m.dateOperation BETWEEN :startDate AND :endDate')
           ->setParameter('val', $caisse)
           ->setParameter('promo', $promo)
           ->setParameter('devise', $devise)
           ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
           ->getQuery()
           ->getSingleScalarResult()
       ;
   }


   /**
     * @return array
     */
    public function listeOperationcaisseParEtablissementParCaisseParDeviseParPeriode($etablissement, $caisse, $devise, $promo, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $query = $this->createQueryBuilder('m')
            ->Where('m.etablissement = :etablissement')
            ->andWhere('m.promo = :promo')
            ->andWhere('m.caisse = :caisse')
            ->andWhere('m.devise = :devise')
            ->andWhere('m.dateOperation BETWEEN :startDate AND :endDate')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('promo', $promo)
            ->setParameter('devise', $devise)
            ->setParameter('caisse', $caisse)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('m.dateSaisie', 'DESC')
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
     * @return array Returns an array with the sum of payments grouped by typeMouvement
     */
    public function sommePaiementParType($types, $promo): array
    {
        $result = $this->createQueryBuilder('m')
            ->select('m.typeMouvement, SUM(m.montant) AS sommeMontant, count(m.id) as nbre')
            ->andWhere('m.typeMouvement IN (:types)')
            ->andWhere('m.promo = :promo')
            ->setParameter('types', $types)  // Utilisation de IN pour les types
            ->setParameter('promo', $promo)
            ->groupBy('m.typeMouvement')  // Groupement par type de mouvement
            ->orderBy('sommeMontant', 'DESC')
            ->getQuery()
            ->getResult();

        return $result;
    }

   

}
