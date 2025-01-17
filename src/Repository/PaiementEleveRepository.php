<?php

namespace App\Repository;

use App\Entity\PaiementEleve;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<PaiementEleve>
 */
class PaiementEleveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaiementEleve::class);
    }

    //    /**
    //     * @return PaiementEleve[] Returns an array of PaiementEleve objects
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

    //    public function findOneBySomeField($value): ?PaiementEleve
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
       public function listeDesPaiementsParCursus($cursus, $promo): array
       {
           return $this->createQueryBuilder('p')
                ->leftJoin('p.inscription', 'i')
                ->leftJoin('i.classe', 'c')
                ->leftJoin('c.formation', 'f')                
               ->andWhere('f.cursus = :cursus')
               ->andWhere('p.promo = :promo')
               ->setParameter('promo', $promo)
               ->setParameter('cursus', $cursus)
               ->getQuery()
               ->getResult()
           ;
       }

       /**
     * @return float|null Returns the total amount or null if no payments found
     */
    public function cumulPaiementsParCursus($cursus, $promo, $origine = null, $typePaie = null, $typeMouvement = null): ?float
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('SUM(p.montant) as total')
            ->leftJoin('p.inscription', 'i')
            ->leftJoin('i.classe', 'c')
            ->leftJoin('c.formation', 'f')
            ->andWhere('f.cursus = :cursus')
            ->andWhere('p.promo = :promo')
            ->setParameter('promo', $promo)
            ->setParameter('cursus', $cursus);
        
        if ($origine) {
            $queryBuilder->andWhere('p.origine = :origine')
                ->setParameter('origine', $origine);
        }
        if ($typePaie) {
            $queryBuilder->andWhere('p.typePaie = :typePaie')
                ->setParameter('typePaie', $typePaie);
        }
        if ($typeMouvement) {
            $queryBuilder->andWhere('p.typeMouvement = :typeMouvement')
                ->setParameter('typeMouvement', $typeMouvement);
        }

        // Exécute la requête et récupère le total
        $result = $queryBuilder->getQuery()->getSingleScalarResult();
        
        // Retourne le total ou null si pas de paiement
        return $result !== null ? (float) $result : null;
    }

    public function paiementEleveParType($inscription, $origine = null, $typePaie = null, $typeMouvement = null): ?float
    {
        $queryBuilder = $this->createQueryBuilder('p')
        ->select('sum(p.montant) as montant')
        ->andWhere('p.inscription = :inscription')
        ->setParameter('inscription', $inscription);
        if ($origine) {
            $queryBuilder->andWhere('p.origine = :origine')
                ->setParameter('origine', $origine);
        }
        if ($typePaie) {
            $queryBuilder->andWhere('p.typePaie = :typePaie')
                ->setParameter('typePaie', $typePaie);
        }
        if ($typeMouvement) {
            $queryBuilder->andWhere('p.typeMouvement = :typeMouvement')
                ->setParameter('typeMouvement', $typeMouvement);
        }
        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        return $result !== null ? (float) $result['montant'] : null;
    }

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

       
        public function cumulPaiementEleve($inscription, $promo): ?float
        {
            $result = $this->createQueryBuilder('p')
                ->select('SUM(p.montant) as total')
                ->andWhere('p.inscription = :inscription')
                ->andWhere('p.promo = :promo')
                ->setParameter('inscription', $inscription)
                ->setParameter('promo', $promo)
                ->getQuery()
                ->getOneOrNullResult();
                
            return $result !== null ? (float) $result['total'] : null;

        }

        public function cumulPaiementEleveParType($inscription, $type, $promo): ?float
        {
            $result = $this->createQueryBuilder('p')
                ->select('SUM(p.montant) as total')
                ->andWhere('p.inscription = :inscription')
                ->andWhere('p.typePaie = :type')
                ->andWhere('p.promo = :promo')
                ->setParameter('inscription', $inscription)
                ->setParameter('type', $type)
                ->setParameter('promo', $promo)
                ->getQuery()
                ->getOneOrNullResult();
                
            return $result !== null ? (float) $result['total'] : null;

        }

        /**
        * @return PaiementEleve[] Returns an array of PaiementEleve objects
        */
        public function cumulPaiementEleveGroupeParType($inscription, $promo): array
        {
            return $this->createQueryBuilder('p')
                ->select('SUM(p.montant) as solde', 'p as paiement',)
                ->andWhere('p.inscription = :inscription')
                ->andWhere('p.promo = :promo')
                ->setParameter('inscription', $inscription)
                ->setParameter('promo', $promo)
                ->addGroupBy('p.typePaie')
                ->getQuery()
                ->getResult()
            ;
        }

    
    public function paiementInscription($inscription, $promo): ?float
    {
        $result = $this->createQueryBuilder('p')
        ->select('sum(p.montant) as montant')
        ->andWhere('p.inscription = :inscription')
        ->andWhere('p.promo = :promo')
        ->andWhere('p.origine = :origine')
        ->setParameter('inscription', $inscription)
        ->setParameter('promo', $promo)
        ->setParameter('origine', 'inscription')
        ->getQuery()
        ->getOneOrNullResult();

        return $result !== null ? (float) $result['montant'] : null;
    }

    


    /**
     * @return array Returns an array with the total paid for each typePaie and the remaining amount to be paid for each tranche.
     */
    public function resteScolariteEleve($inscription, $promo, $fraisScol, $remise): array
    {
        $query = $this->createQueryBuilder('p')
            ->select('p.typePaie', 'SUM(p.montant) as totalPaid')
            ->andWhere('p.inscription = :inscription')
            ->andWhere('p.promo = :promo')
            ->andWhere('p.origine = :origine')
            ->setParameter('inscription', $inscription)
            ->setParameter('promo', $promo)
            ->setParameter('origine', 'scolarite')
            ->groupBy('p.typePaie')
            ->getQuery()
            ->getResult();
        
        $paiements = [];
        foreach ($query as $q) {
            $paiements[$q['typePaie']] = $q['totalPaid'];
        }

        $resteAPayer = [];
        foreach ($fraisScol as $frais) {
            $trancheNom = $frais->getTranche()->getNom();
            $montantFrais = $frais->getMontant()*(1 - $remise);
            $montantPaye = isset($paiements[$trancheNom]) ? $paiements[$trancheNom] : 0;
            $resteAPayer[$trancheNom] = $montantFrais - $montantPaye;
        }

        return $resteAPayer;

    }

    

        /**
     * @return array Returns an array with the total paid for each typePaie and the remaining amount to be paid for each tranche with past due dates.
     */
    public function resteScolariteEleveParDatelimite($inscription, $promo, $fraisScol, $remise): array
    {
        // Get the current date
        $currentDate = new \DateTime();
        // Définir une petite tolérance pour exclure les valeurs proches de zéro
        $tolérance = 1e-1;


        // Fetch the total paid amount for each payment type
        $query = $this->createQueryBuilder('p')
            ->select('p.typePaie', 'SUM(p.montant) as totalPaid')
            ->andWhere('p.inscription = :inscription')
            ->andWhere('p.promo = :promo')
            ->andWhere('p.origine = :origine')
            ->setParameter('inscription', $inscription)
            ->setParameter('promo', $promo)
            ->setParameter('origine', 'scolarite')
            ->groupBy('p.typePaie')
            ->getQuery()
            ->getResult();
        
        $paiements = [];
        foreach ($query as $q) {
            $paiements[$q['typePaie']] = $q['totalPaid'];
        }

        $resteAPayer = [];
        foreach ($fraisScol as $frais) {
            // Check if the date limit has passed
            if ($frais->getDateLimite() < $currentDate) {
                $trancheNom = $frais->getTranche()->getNom();
                $montantFrais = $frais->getMontant() * (1 - $remise);
                $montantPaye = isset($paiements[$trancheNom]) ? $paiements[$trancheNom] : 0;
                $reste = $montantFrais - $montantPaye;
                // Exclure les valeurs proches de zéro
                if (abs($reste) > $tolérance) {
                    $resteAPayer[$trancheNom] = $reste;
                }
            }
        }

        return $resteAPayer;
    }

    /**
     * @return array Returns an array with the total paid for each typePaie and the remaining amount to be paid for each tranche with past due dates.
     */
    public function creances($inscription, $promo, $fraisScol, $remise): array
    {
        // Get the current date
        $currentDate = new \DateTime();

        // Fetch the total paid amount for each payment type
        $query = $this->createQueryBuilder('p')
            ->select('p.typePaie', 'SUM(p.montant) as totalPaid')
            ->andWhere('p.inscription = :inscription')
            ->andWhere('p.promo = :promo')
            ->andWhere('p.origine = :origine')
            ->setParameter('inscription', $inscription)
            ->setParameter('promo', $promo)
            ->setParameter('origine', 'scolarite')
            ->groupBy('p.typePaie')
            ->getQuery()
            ->getResult();
        
        $paiements = [];
        foreach ($query as $q) {
            $paiements[$q['typePaie']] = $q['totalPaid'];
        }

        $resteAPayer = [];
        foreach ($fraisScol as $frais) {
            $trancheNom = $frais->getTranche()->getNom();
            $montantFrais = $frais->getMontant() * (1 - $remise);
            $montantPaye = isset($paiements[$trancheNom]) ? $paiements[$trancheNom] : 0;
            $reste = $montantFrais - $montantPaye;
            if ($reste !=0) {
                $resteAPayer[$trancheNom] = $reste;
            }
            
        }

        return $resteAPayer;
    }

    /**
     * @return array
     */
    public function findPaiementScolariteByEtablissementPaginated($etablissement, $promo, $startDate, $endDate, int $pageEnCours, int $limit): array
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
    public function findPaiementScolariteByEtablissementBySearchPaginated($etablissement, $eleve, $promo, $startDate, $endDate, int $pageEnCours, int $limit): array
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

    public function nombrePaiementScolariteParCursus($cursus, $promo): ?int
    {
        $result = $this->createQueryBuilder('p')
            ->select('Count(p.id)')
            ->leftJoin('p.inscription', 'i')
            ->leftJoin('i.classe', 'c')
            ->leftJoin('c.formation', 'f')                
            ->andWhere('f.cursus = :cursus')
            ->andWhere('p.promo = :promo')
            ->setParameter('promo', $promo)
            ->setParameter('cursus', $cursus)
            ->getQuery()
            ->getSingleScalarResult();
        return $result;
    }

    
}
