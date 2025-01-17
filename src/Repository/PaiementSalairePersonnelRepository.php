<?php

namespace App\Repository;

use App\Entity\PaiementSalairePersonnel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaiementSalairePersonnel>
 */
class PaiementSalairePersonnelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaiementSalairePersonnel::class);
    }

    //    /**
    //     * @return PaiementSalairePersonnel[] Returns an array of PaiementSalairePersonnel objects
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

    //    public function findOneBySomeField($value): ?PaiementSalairePersonnel
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

           /**
        * @return PaiementSalairePersonnel[] Returns an array of PaiementSalairePersonnel objects
        */
       public function listePaiementParEtablissementParPromo($etablissement, $promo): array
       {
           return $this->createQueryBuilder('p')
                ->andWhere('p.promo = :promo')
                ->andWhere('p.etablissement = :etablissement')
                ->setParameter('etablissement', $etablissement)
                ->setParameter('promo', $promo)
                ->addOrderBy('p.periode', 'ASC')
               ->getQuery()
               ->getResult()
           ;
       }

           /**
        * @return PaiementSalairePersonnel[] Returns an array of PaiementSalairePersonnel objects
        */
        public function listePaiementParPeriodeParEtablissementParPromo($periode, $etablissement, $promo): array
        {
            // Extraire l'année et le mois de la période fournie
            $year = substr($periode, 0, 4);
            $month = substr($periode, 5, 2);

            // Créer les objets DateTime pour le début et la fin du mois
            $startDate = new \DateTime("{$year}-{$month}-01");
            $endDate = (clone $startDate)->modify('last day of this month');
            return $this->createQueryBuilder('p')
                ->leftJoin('p.personnelActif', 'pa')
                ->leftJoin('pa.personnel', 'u')
                 ->andWhere('p.promo = :promo')
                 ->andWhere('p.etablissement = :etablissement')
                 ->andWhere('p.periode BETWEEN :date1 AND :date2')
                    ->setParameter('date1', $startDate)
                    ->setParameter('date2', $endDate)
                 ->setParameter('etablissement', $etablissement)
                 ->setParameter('promo', $promo)
                 ->addOrderBy('u.prenom', 'ASC')
                 ->addOrderBy('u.nom', 'ASC')
                ->getQuery()
                ->getResult()
            ;
        }

       
        public function cumulPaiementParTypeParPeriodeParEtablissementParPromo($types, $periode, $etablissement, $promo): ?float
        {
            // Extraire l'année et le mois de la période fournie
            $year = substr($periode, 0, 4);
            $month = substr($periode, 5, 2);

            // Créer les objets DateTime pour le début et la fin du mois
            $startDate = new \DateTime("{$year}-{$month}-01");
            $endDate = (clone $startDate)->modify('last day of this month');
            $result = $this->createQueryBuilder('p')
                ->addSelect('p.montant')
                ->leftJoin('p.personnelActif', 'pa')
                ->leftJoin('pa.personnel', 'u')
                 ->andWhere('pa.type IN (:types)')
                 ->andWhere('pa.promo = :promo')
                 ->andWhere('p.promo = :promo')
                 ->andWhere('p.etablissement = :etablissement')
                 ->andWhere('p.periode BETWEEN :date1 AND :date2')
                ->setParameter('date1', $startDate)
                ->setParameter('date2', $endDate)
                 ->setParameter('types', $types)
                 ->setParameter('etablissement', $etablissement)
                 ->setParameter('promo', $promo)
                 ->getQuery()
                 ->getOneOrNullResult();
                     
             return $result !== null ? (float) $result['montant'] : 0;
            ;
        }

        public function rectificatifCumulPaiementParTypeParPeriodeParEtablissementParPromo($types, $periode, $etablissement, $promo): ?float
        {
            // Extraire l'année et le mois de la période fournie
            $year = substr($periode, 0, 4);
            $month = substr($periode, 5, 2);

            // Créer les objets DateTime pour le début et la fin du mois
            $startDate = new \DateTime("{$year}-{$month}-01");
            $endDate = (clone $startDate)->modify('last day of this month');
            $result = $this->createQueryBuilder('p')
                ->addSelect('SUM(p.montant) as montant')
                ->leftJoin('p.personnelActif', 'pa')
                ->leftJoin('pa.personnel', 'u')
                 ->andWhere('pa.type IN (:types)')
                 ->andWhere('pa.promo = :promo')
                 ->andWhere('p.promo = :promo')
                 ->andWhere('p.etablissement = :etablissement')
                 ->andWhere('p.periode BETWEEN :date1 AND :date2')
                ->setParameter('date1', $startDate)
                ->setParameter('date2', $endDate)
                 ->setParameter('types', $types)
                 ->setParameter('etablissement', $etablissement)
                 ->setParameter('promo', $promo)
                 ->getQuery()
                 ->getOneOrNullResult();
                     
             return $result !== null ? (float) $result['montant'] : 0;
            ;
        }


    /**
     * Récupère la liste du personnel non payé par type, cursus, période et établissement.
     *
     * @param string $type
     * @param string $cursus
     * @param string $periode
     * @param string $etablissement
     * @return array
     */
    public function listePaiementParTypeParCursusParPeriodeParEtablissementParPromo($type, $cursus, $periode, $etablissement, $promo, $mode = null): array
    {
        // Extraire l'année et le mois de la période fournie
        $year = substr($periode, 0, 4);
        $month = substr($periode, 5, 2);

        // Créer les objets DateTime pour le début et la fin du mois
        $startDate = new \DateTime("{$year}-{$month}-01");
        $endDate = (clone $startDate)->modify('last day of this month');        

        // Créer la requête
        $query = $this->createQueryBuilder('s')
            ->leftJoin('s.personnelActif', 'p')
            ->leftJoin('p.personnel', 'u')
            ->andWhere('s.promo = :promo')
            ->andWhere('s.etablissement = :etablissement')
            ->andWhere('s.periode BETWEEN :date1 AND :date2')
            ->setParameter('date1', $startDate)
            ->setParameter('date2', $endDate)
            ->setParameter('etablissement', $etablissement)
            ->setParameter('promo', $promo);


        // Filtrer par type, si nécessaire
        if ($type !== 'général') {
            if ($type == 'enseignant') {
                $query->andWhere('p.type IN (:types)')
                    ->setParameter('types', ['enseignant', 'personnel-enseignant']);
            } else {
                $query->andWhere('p.type = :type')
                    ->setParameter('type', $type);
            }
        }

        // Filtrer par cursus, si nécessaire
        if ($cursus) {
            if ($cursus !== 'général') {
                $query->andWhere('p.rattachement = :rattachement')
                    ->setParameter('rattachement', $cursus);
            }
        }



        if ($mode) {
            $query->andWhere('s.modePaie = :mode')
                ->setParameter('mode', $mode);
        }
        // Ajouter les critères de tri
        $query->addOrderBy('u.prenom', 'ASC')
            ->addOrderBy('u.nom', 'ASC');

        // Exécuter la requête et retourner les résultats
        return $query->getQuery()->getResult();
    }


    public function paiementPersonnelParPeriodeParParPromoEtablissement($personnel, $periode, $promo, $etablissement): array
    {
        // Extraire l'année et le mois de la période fournie
        // dd($periode);
        $year = substr($periode, 0, 4);
        $month = substr($periode, 5, 2);

        // Créer les objets DateTime pour le début et la fin du mois
        $startDate = new \DateTime("{$year}-{$month}-01");
        $endDate = (clone $startDate)->modify('last day of this month');
        // dd($startDate, $endDate);

        // Créer la requête pour récupérer tous les événements
        $queryBuilder = $this->createQueryBuilder('p')
            ->andWhere('p.personnelActif = :personnel')
            ->andWhere('p.etablissement = :etablissement')
            ->andWhere('p.promo = :promo')
            ->andWhere('p.periode BETWEEN :date1 AND :date2')
            ->setParameter('personnel', $personnel)
            ->setParameter('etablissement', $etablissement)
            ->setParameter('promo', $promo)
            ->setParameter('date1', $startDate)
            ->setParameter('date2', $endDate);

        return $queryBuilder->getQuery()->getResult();
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
