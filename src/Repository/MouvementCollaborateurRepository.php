<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\MouvementCollaborateur;
use App\Entity\Personnel;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<MouvementCollaborateur>
 *
 * @method MouvementCollaborateur|null find($id, $lockMode = null, $lockVersion = null)
 * @method MouvementCollaborateur|null findOneBy(array $criteria, array $orderBy = null)
 * @method MouvementCollaborateur[]    findAll()
 * @method MouvementCollaborateur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MouvementCollaborateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MouvementCollaborateur::class);
    }

//    /**
//     * @return MouvementCollaborateur[] Returns an array of MouvementCollaborateur objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MouvementCollaborateur
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function verifMouvement($collaborateur): array
    {
        return $this->createQueryBuilder('m')
            ->select('m.id')
            ->andWhere('m.collaborateur = :colab')
            // ->andWhere('m.montant != :montant')
            ->setParameter('colab', $collaborateur)
            // ->setParameter('montant', 0)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        ;
    }

    public function verifMouvementPersonnel($collaborateur): array
    {
        return $this->createQueryBuilder('m')
            ->select('m.id')
            ->andWhere('m.traitePar = :colab')
            // ->andWhere('m.montant != :montant')
            ->setParameter('colab', $collaborateur)
            // ->setParameter('montant', 0)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        ;
    }

    public function findSoldeCollaborateur($collaborateur): array
    {
        return $this->createQueryBuilder('m')
            ->select('sum(m.montant) as montant' , 'c.nom as devise')
            ->leftJoin('m.devise', 'c')
            ->andWhere('m.collaborateur = :colab')
            ->setParameter('colab', $collaborateur)
            ->addGroupBy('m.devise')
            ->getQuery()
            ->getResult()

        ;
    }

    /**
     * @return array
     */
    public function SoldeDetailByCollaborateurByDeviseByDate($collaborateur, $devise, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('m')
            ->andWhere('m.collaborateur = :collab')
            ->andWhere('m.devise = :devise')
            ->andWhere('m.dateSaisie BETWEEN :startDate AND :endDate')
            ->setParameter('collab', $collaborateur)
            ->setParameter('devise', $devise)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('m.dateOperation', 'ASC')
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
        ;
    }

    /**
     * @return int|null
     */
    public function sumMontantBeforeStartDate($collaborateur, $devise, $startDate): ?int
    {
        $query = $this->createQueryBuilder('m')
            ->select('sum(m.montant) as totalMontant')
            ->andWhere('m.collaborateur = :collab')
            ->andWhere('m.devise = :devise')
            ->andWhere('m.dateSaisie < :startDate')
            ->setParameter('collab', $collaborateur)
            ->setParameter('devise', $devise)
            ->setParameter('startDate', $startDate)
            ->getQuery()
            ->getSingleScalarResult();
        return $query;
    }

    public function findSoldeCompteCollaborateur($collaborateur, $devises): array
    {
        $query = $this->createQueryBuilder('m');        
        $results = $query
            ->select('sum(m.montant) as montant', 'd.nom as devise', 'd.id as id_devise')
            ->leftJoin('m.devise', 'd')
            ->andWhere('m.collaborateur = :colab')
            ->setParameter('colab', $collaborateur)
            ->addGroupBy('d.nom')
            ->getQuery()
            ->getResult();

        // Créer un tableau pour stocker les résultats finaux
        $finalResults = [];
        foreach ($devises as $devise) {
            $trouve = false;
            foreach ($results as $resultat) {
                if ($resultat['devise'] === $devise->getNom()) {
                    $finalResults[] = $resultat;
                    $trouve = true;
                    break;
                }
            }
            if (!$trouve) {
                // Si la devise n'est pas trouvée dans les résultats, ajouter une entrée avec un montant de zéro
                $finalResults[] = ['montant' => '0.00', 'devise' => $devise->getNom(), 'id_devise' => $devise->getId()];
            }
        }

        return $finalResults;
    }

    public function findSoldeGeneralByType($type1, $type2, $etablissement, $devises = null): array
    {
        $query = $this->createQueryBuilder('m');
        $results = $query
            ->select('sum(m.montant) as montant' , 'd.nom as devise')
            ->leftJoin('m.devise', 'd')
            ->leftJoin('m.collaborateur', 'u')
            // ->leftJoin(Personnel::class, 'p', 'WITH', 'p.user = u.id ')
            ->andWhere('u.statut = :statut')
            ->andWhere('u.typeUser = :type1 ')
            ->andWhere('m.etablissement= :etablissement')
            ->setParameter('statut', 'actif')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('type1', 'personnel')
            ->addGroupBy('m.devise')
            ->getQuery()
            ->getResult()

        ;
  

        // Créer un tableau pour stocker les résultats finaux
        $finalResults = [];
        foreach ($devises as $devise) {
            $trouve = false;
            foreach ($results as $resultat) {
                if ($resultat['devise'] === $devise->getNom()) {
                    $finalResults[] = $resultat;
                    $trouve = true;
                    break;
                }
            }
            if (!$trouve) {
                // Si la devise et la caisse ne sont pas trouvées dans les résultats, ajouter une entrée avec un solde de zéro
                $finalResults[] = [
                    'montant' => '0.00', 
                    'devise' => $devise->getNom()
                ];
            }
        }
        return $finalResults;
    }

    public function findAncienSoldeCollaborateur($collaborateur, $dateOp): array
    {
        return $this->createQueryBuilder('m')
            ->select('sum(m.montant) as montant' , 'c.nom as devise')
            ->leftJoin('m.devise', 'c')
            ->andWhere('m.collaborateur = :colab')
            ->andWhere('m.dateOperation < :dateOp')
            ->setParameter('colab', $collaborateur)
            ->setParameter('dateOp', $dateOp)
            ->addGroupBy('m.devise')
            ->orderBy('m.devise')
            ->getQuery()
            ->getResult()

        ;
    }

    public function listeSoldeGeneralGroupeParDeviseParCollaborateurParAnnee($anneeOp): array
    {
        $dateDebutAnnee = new \DateTime($anneeOp . '-01-01');
        $dateFinAnnee = new \DateTime($anneeOp . '-12-31');
    
        return $this->createQueryBuilder('m')
            ->select('sum(m.montant) as montant', 'c.nom as devise', 'c.id as id_devise')
            ->leftJoin('m.devise', 'c')
            ->andWhere('m.dateOperation BETWEEN :dateDebut AND :dateFin')
            ->setParameter('dateDebut', $dateDebutAnnee)
            ->setParameter('dateFin', $dateFinAnnee)
            ->addGroupBy('m.devise, m.collaborateur')
            ->orderBy('m.devise')
            ->getQuery()
            ->getResult();
    }

    public function listeSoldeGeneralGroupeParDeviseParCollaborateurParAnneeParEtablissement($anneeOp, $lieu): array
    {
        $dateDebutAnnee = new \DateTime($anneeOp . '-01-01');
        $dateFinAnnee = new \DateTime($anneeOp . '-12-31');
    
        return $this->createQueryBuilder('m')
            ->select('sum(m.montant) as montant', 'c.nom as devise', 'c.id as id_devise')
            ->leftJoin('m.devise', 'c')
            ->andWhere('m.etablissement = :etablissement')
            ->andWhere('m.dateOperation BETWEEN :dateDebut AND :dateFin')
            ->setParameter('etablissement', $lieu)
            ->setParameter('dateDebut', $dateDebutAnnee)
            ->setParameter('dateFin', $dateFinAnnee)
            ->addGroupBy('m.devise, m.collaborateur')
            ->orderBy('m.devise')
            ->getQuery()
            ->getResult();
    }


    public function findSoldeCompteCollaborateurInactif($collaborateur, $limit)
    {
        $dateLimite = new \DateTime();
        $dateLimite->modify(-$limit.' days');
        $query = $this->createQueryBuilder('m');
        $results = $query
            ->select('sum(m.montant) as montant', 'd.nom as devise', 'max(m.dateOperation) as derniereOperation')
            ->leftJoin('m.devise', 'd')
            ->andWhere('m.collaborateur = :colab')
            ->andWhere('m.devise = 1')
            ->setParameter('colab', $collaborateur)
            ->groupBy('d.nom')
            ->having('sum(m.montant) < 0')
            ->andHaving('sum(m.montant) != 0')
            ->getQuery()
            ->getResult();

        $finalResults = [];

        foreach ($results as $result) {
            $derniereOperation = new \DateTime($result['derniereOperation']);
            $difference = $derniereOperation->diff($dateLimite);
            if ($difference->days >= $limit) {
                $finalResults[] = $result;
            }

        }
    
        return $finalResults;
    }


    public function soldeCollaborateurParEtablissementGroupeParDevise($etablissement): array
    {
        return $this->createQueryBuilder('m')
            ->select('sum(m.montant) as totalMontant', 'm as mouvement')
            ->andWhere('m.etablissement = :etablissement')
            ->setParameter('etablissement', $etablissement)
            ->groupBy('m.collaborateur')
            ->addGroupBy('m.devise')
            ->getQuery()
            ->getResult();
    }

    public function soldeGeneralParEtablissementGroupeParDevise($etablissement): array
    {
        return $this->createQueryBuilder('m')
            ->select('sum(m.montant) as totalMontant', 'm as mouvement')
            ->andWhere('m.etablissement = :etablissement')
            ->setParameter('etablissement', $etablissement)
            ->GroupBy('m.devise')
            ->getQuery()
            ->getResult();
    }

    public function soldeDettesParEtablissementGroupeParDevise($etablissement): array
    {
        return $this->createQueryBuilder('m')
            ->select('sum(m.montant) as totalMontant', 'm as mouvement')
            ->andWhere('m.etablissement = :etablissement')
            ->andWhere('m.montant > :montant')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('montant', 0)
            ->GroupBy('m.devise')
            ->getQuery()
            ->getResult();
    }

    public function soldeCreancesParEtablissementGroupeParDevise($etablissement): array
    {
        return $this->createQueryBuilder('m')
            ->select('sum(m.montant) as totalMontant', 'm as mouvement')
            ->andWhere('m.etablissement = :etablissement')
            ->andWhere('m.montant < :montant')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('montant', 0)
            ->GroupBy('m.devise')
            ->getQuery()
            ->getResult();
    }
}
