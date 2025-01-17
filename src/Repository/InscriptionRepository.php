<?php

namespace App\Repository;

use App\Entity\Inscription;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Inscription>
 */
class InscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inscription::class);
    }

    //    /**
    //     * @return Inscription[] Returns an array of Inscription objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Inscription
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }


    /**
     * @return Inscription[] Returns an array of Inscription objects
     */
    public function listeDesElevesInscritsParCursus($cursus, $promo, $statut = NULL, $etatPedagogie = NULL): array
    {
        $query = $this->createQueryBuilder('i')
            ->leftJoin('i.eleve', 'e')
            ->leftJoin('i.classe', 'c')
            ->leftJoin('c.formation', 'f')
            ->andWhere('f.cursus = :cursus')
            ->andWhere('c.promo = :promo')
            ->andWhere('i.promo = :promo')
            ->setParameter('promo', $promo)
            ->setParameter('cursus', $cursus);
        if ($statut) {
            $query->andWhere('i.statut != :statut or i.statut is null')
            ->setParameter('statut', $statut);

        }
        if ($etatPedagogie) {
            $query->andWhere('i.etatPedagogie != :etatPedagogie or i.etatPedagogie is null')
            ->setParameter('etatPedagogie', $etatPedagogie);

        }
        $query->orderBy('e.prenom', 'ASC')
            ->addOrderBy('e.nom', 'ASC')
            ->addOrderBy('e.matricule', 'ASC');
        return $query->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Inscription[] Returns an array of Inscription objects
     */
    public function listeDesElevesInscritsParCursusParType($type, $cursus, $promo, $statut = NULL, $etatPedagogie = NULL): array
    {
        $query = $this->createQueryBuilder('i')
            ->leftJoin('i.eleve', 'e')
            ->leftJoin('i.classe', 'c')
            ->leftJoin('c.formation', 'f')
            ->andWhere('f.cursus = :cursus')
            ->andWhere('i.type = :type')
            ->andWhere('i.promo = :promo')
            ->setParameter('promo', $promo)
            ->setParameter('type', $type)
            ->setParameter('cursus', $cursus);
        if ($statut) {
            $query->andWhere('i.statut != :statut or i.statut is null')
            ->setParameter('statut', $statut);

        }
        if ($etatPedagogie) {
            $query->andWhere('i.etatPedagogie != :etatPedagogie or i.etatPedagogie is null')
            ->setParameter('etatPedagogie', $etatPedagogie);

        }
        $query->orderBy('e.prenom', 'ASC')
            ->addOrderBy('e.nom', 'ASC')
            ->addOrderBy('e.matricule', 'ASC');
        return $query->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Inscription[] Returns an array of Inscription objects
     */
    public function listeGeneraleDesElevesInscritsParClasse($classe, $etatPedagogie = NULL): array
    {
            $query = $this->createQueryBuilder('i')
                ->leftJoin('i.eleve', 'e')
            ->andWhere('i.classe = :classe')
            ->setParameter('classe', $classe);
            if ($etatPedagogie) {
                $query->andWhere('i.etatPedagogie != :etatPedagogie or i.etatPedagogie is null')
                ->setParameter('etatPedagogie', $etatPedagogie);

            }
            $query->orderBy('e.prenom', 'ASC')
            ->addOrderBy('e.nom', 'ASC')
            ->addOrderBy('e.matricule', 'ASC');
            return $query->getQuery()
            ->getResult()
        ;
    }


    /**
     * @return Inscription[] Returns an array of Inscription objects
     */
    public function listeDesElevesInscritsParClasse($classe, $etatPedagogie = NULL): array
    {
            $query = $this->createQueryBuilder('i')
                ->leftJoin('i.eleve', 'e')
            ->andWhere('i.classe = :classe')
            ->andWhere('i.statut != :statut')
            ->setParameter('classe', $classe)
            ->setParameter('statut', 'inactif');
            if ($etatPedagogie) {
                $query->andWhere('i.etatPedagogie != :etatPedagogie or i.etatPedagogie is null')
                ->setParameter('etatPedagogie', $etatPedagogie);

            }
            $query->orderBy('e.prenom', 'ASC')
            ->addOrderBy('e.nom', 'ASC')
            ->addOrderBy('e.matricule', 'ASC');
            return $query->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Inscription[] Returns an array of Inscription objects
     */
    public function listeDesElevesInscritsParNiveau($niveau, $promo, $etablissement, $etatPedagogie = NULL): array
    {
            $query = $this->createQueryBuilder('i')
            ->leftJoin('i.eleve', 'e')
            ->leftJoin('i.classe', 'c')
            ->leftJoin('c.formation', 'f')
            ->andWhere('i.promo = :promo')
            ->andWhere('i.etablissement = :etablissement')
            ->andWhere('f.classe = :niveau')
            ->andWhere('i.statut != :statut')
            ->setParameter('promo', $promo)
            ->setParameter('etablissement', $etablissement)
            ->setParameter('niveau', $niveau)
            ->setParameter('statut', 'inactif');
            if ($etatPedagogie) {
                $query->andWhere('i.etatPedagogie != :etatPedagogie or i.etatPedagogie is null')
                ->setParameter('etatPedagogie', $etatPedagogie);

            }
            $query->orderBy('e.prenom', 'ASC')
            ->addOrderBy('e.nom', 'ASC')
            ->addOrderBy('e.matricule', 'ASC');
            return $query->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Inscription[] Returns an array of Inscription objects
     */
    public function listeGeneraleDesElevesInscritsParNiveau($niveau, $promo, $etablissement, $etatPedagogie = NULL): array
    {
            $query = $this->createQueryBuilder('i')
            ->leftJoin('i.eleve', 'e')
            ->leftJoin('i.classe', 'c')
            ->leftJoin('c.formation', 'f')
            ->andWhere('i.promo = :promo')
            ->andWhere('i.etablissement = :etablissement')
            ->andWhere('f.classe = :niveau')
            ->setParameter('promo', $promo)
            ->setParameter('etablissement', $etablissement)
            ->setParameter('niveau', $niveau);
            if ($etatPedagogie) {
                $query->andWhere('i.etatPedagogie != :etatPedagogie or i.etatPedagogie is null')
                ->setParameter('etatPedagogie', $etatPedagogie);

            }
            $query->orderBy('e.prenom', 'ASC')
            ->addOrderBy('e.nom', 'ASC')
            ->addOrderBy('e.matricule', 'ASC');
            return $query->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Inscription[] Returns an array of Inscription objects
     */
    public function listeDesElevesInscritsParFormation($formation, $promo, $etablissement, $etatPedagogie = NULL): array
    {
            $query = $this->createQueryBuilder('i')
            ->leftJoin('i.eleve', 'e')
            ->leftJoin('i.classe', 'c')
            ->andWhere('i.promo = :promo')
            ->andWhere('i.etablissement = :etablissement')
            ->andWhere('c.formation = :formation')
            ->andWhere('i.statut != :statut')
            ->setParameter('promo', $promo)
            ->setParameter('etablissement', $etablissement)
            ->setParameter('formation', $formation)
            ->setParameter('statut', 'inactif');
            if ($etatPedagogie) {
                $query->andWhere('i.etatPedagogie != :etatPedagogie or i.etatPedagogie is null')
                ->setParameter('etatPedagogie', $etatPedagogie);

            }
            $query->orderBy('e.prenom', 'ASC')
            ->addOrderBy('e.nom', 'ASC')
            ->addOrderBy('e.matricule', 'ASC');
            return $query->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Inscription[] Returns an array of Inscription objects
     */
    public function listeGeneraleDesElevesInscritsParFormation($formation, $promo, $etablissement, $etatPedagogie = NULL): array
    {
            $query = $this->createQueryBuilder('i')
            ->leftJoin('i.eleve', 'e')
            ->leftJoin('i.classe', 'c')
            ->andWhere('i.promo = :promo')
            ->andWhere('i.etablissement = :etablissement')
            ->andWhere('c.formation = :formation')
            ->setParameter('promo', $promo)
            ->setParameter('etablissement', $etablissement)
            ->setParameter('formation', $formation);
            if ($etatPedagogie) {
                $query->andWhere('i.etatPedagogie != :etatPedagogie or i.etatPedagogie is null')
                ->setParameter('etatPedagogie', $etatPedagogie);

            }
            $query->orderBy('e.prenom', 'ASC')
            ->addOrderBy('e.nom', 'ASC')
            ->addOrderBy('e.matricule', 'ASC');
            return $query->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Inscription[] Returns an array of Inscription objects
     */
    public function listeDesRemises($promo, $etablissement, $remiseInscription = null, $remiseScolarite = NULL, $classes = null): array
    {
        $query = $this->createQueryBuilder('i')
            ->leftJoin('i.eleve', 'e')
            ->andWhere('i.promo = :promo')
            ->andWhere('i.etablissement = :etablissement')
            ->setParameter('promo', $promo)
            ->setParameter('etablissement', $etablissement);
        if ($classes) {
            $query->andWhere('i.classe IN (:classes)')
                    ->setParameter('classes', $classes);
        }

        if ($remiseInscription) {
            $query->andWhere('i.remiseInscription > :remiseInscription ')
            ->setParameter('remiseInscription', 0);
        }
        if ($remiseScolarite) {
            $query->andWhere('i.remiseScolarite > :remiseScolarite ')
            ->setParameter('remiseScolarite', 0);
        }
        $query->orderBy('e.prenom', 'ASC')
            ->addOrderBy('e.nom', 'ASC')
            ->addOrderBy('e.matricule', 'ASC');
        return $query->getQuery()
            ->getResult()
        ;
    }

    public function findMaxId($etablissement): ?int
    {
        $result = $this->createQueryBuilder('i')
            ->select('MAX(i.id)')
            ->andWhere('i.etablissement = :etab')
            ->setParameter('etab', $etablissement)
            ->getQuery()
            ->getSingleScalarResult();
        return $result;
    }

    public function rechercheAncienEleveParEtablissementParPromo($value, $promo, $etablissement): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.eleve', 'e')
            ->andWhere('i.etablissement = :etablissement')
            ->andWhere('i.promo != :promo')
            ->andWhere('e.prenom LIKE :val Or e.nom LIKE :val Or e.telephone LIKE :val Or e.matricule LIKE :val')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('promo', $promo)
            ->setParameter('val', '%' . $value . '%')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }

    public function rechercheInscriptionAncienEleveParEtablissementParPromo($value, $promo, $etablissement): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.eleve', 'e')
            ->andWhere('i.etablissement = :etablissement')
            ->andWhere('i.promo != :promo')
            ->andWhere('e.prenom LIKE :val Or e.nom LIKE :val Or e.telephone LIKE :val Or e.matricule LIKE :val')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('promo', $promo)
            ->setParameter('val', '%' . $value . '%')
            ->setMaxResults(20)
            ->addOrderBy('i.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function derniereInscriptionEleveParEtablissementParPromo($eleve, $promo, $etablissement): ?array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.eleve', 'e')
            ->andWhere('i.etablissement = :etablissement')
            ->andWhere('i.eleve = :eleve')
            ->andWhere('i.promo != :promo')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('promo', $promo)
            ->setParameter('eleve',  $eleve)
            ->setMaxResults(20)
            ->addOrderBy('i.promo', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function rechercheEleveParEtablissementParPromo($value, $promo, $etablissement): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.eleve', 'e')
            ->andWhere('i.etablissement = :etablissement')
            ->andWhere('i.promo = :promo')
            ->andWhere('e.prenom LIKE :val Or e.nom LIKE :val Or e.telephone LIKE :val Or e.matricule LIKE :val')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('promo', $promo)
            ->setParameter('val', '%' . $value . '%')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }

    /**
    * @return Inscription[] Returns an array of Inscription objects
    */
    public function listeDesElevesInscritParPromoParEtablissement($promo, $etablissement, $search , int $pageEnCours, int $limit): array
    {    
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('i')
            ->leftJoin('i.eleve', 'e')
            ->leftJoin('i.classe', 'c')
            ->andWhere('i.promo = :promo')
            ->andWhere('i.etablissement = :etablissement');
            if ($search) {
                $query->andWhere('e.matricule LIKE :search OR e.prenom LIKE :search OR e.nom LIKE :search OR e.telephone LIKE :search OR c.nom LIKE :search ')
                ->setParameter('search', '%' . $search . '%');
            }

            $query->setParameter('promo', $promo)
            ->setParameter('etablissement', $etablissement)
            ->orderBy('e.prenom', 'ASC')
            ->addOrderBy('e.nom', 'ASC')
            ->addOrderBy('e.matricule', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult(($pageEnCours * $limit) - $limit);
        $paginator = new Paginator($query);
        $data = $paginator->getQuery()->getResult();
        // on calcule le nombre des pages

        $nbrePages = ceil($paginator->count() / $limit);
        // on remplit le tableau
        
        $result['data'] = $data;
        $result['nbrePages'] = $nbrePages;
        $result['pageEncours'] = $pageEnCours;
        $result['limit'] = $limit;         
        return $result;
        
    }

    /**
    * @return Inscription[] Returns an array of Inscription objects
    */
    public function listeDesElevesInscritActifParPromoParEtablissement($promo, $etablissement, $search , int $pageEnCours, int $limit): array
    {    
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('i')
            ->leftJoin('i.eleve', 'e')
            ->leftJoin('i.classe', 'c')
            ->andWhere('i.promo = :promo')
            ->andWhere('i.statut != :statut')
            ->andWhere('i.etablissement = :etablissement');
            if ($search) {
                $query->andWhere('e.matricule LIKE :search OR e.prenom LIKE :search OR e.nom LIKE :search OR e.telephone LIKE :search OR c.nom LIKE :search ')
                ->setParameter('search', '%' . $search . '%');
            }

            $query->setParameter('promo', $promo)
            ->setParameter('etablissement', $etablissement)
            ->setParameter('statut', 'inactif')
            ->orderBy('e.prenom', 'ASC')
            ->addOrderBy('e.nom', 'ASC')
            ->addOrderBy('e.matricule', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult(($pageEnCours * $limit) - $limit);
        $paginator = new Paginator($query);
        $data = $paginator->getQuery()->getResult();
        // on calcule le nombre des pages

        $nbrePages = ceil($paginator->count() / $limit);
        // on remplit le tableau
        
        $result['data'] = $data;
        $result['nbrePages'] = $nbrePages;
        $result['pageEncours'] = $pageEnCours;
        $result['limit'] = $limit;         
        return $result;
        
    }

     /**
    * @return Inscription[] Returns an array of Inscription objects
    */
    public function listeDesElevesInscritParCursusParPromoParEtablissement($cursus, $promo, $etablissement, $search , int $pageEnCours, int $limit): array
    {    
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('i')
            ->leftJoin('i.eleve', 'e')
            ->leftJoin('i.classe', 'c')
            ->leftJoin('c.formation', 'f')
            ->andWhere('f.cursus = :cursus')
            ->andWhere('i.promo = :promo')
            ->andWhere('i.etablissement = :etablissement');
            if ($search) {
                $query->andWhere('e.matricule LIKE :search OR e.prenom LIKE :search OR e.nom LIKE :search OR e.telephone LIKE :search OR c.nom LIKE :search ')
                ->setParameter('search', '%' . $search . '%');
            }

            $query->setParameter('cursus', $cursus)
                ->setParameter('promo', $promo)
            ->setParameter('etablissement', $etablissement)
            ->orderBy('e.prenom', 'ASC')
            ->addOrderBy('e.nom', 'ASC')
            ->addOrderBy('e.matricule', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult(($pageEnCours * $limit) - $limit);
        $paginator = new Paginator($query);
        $data = $paginator->getQuery()->getResult();
        // on calcule le nombre des pages

        $nbrePages = ceil($paginator->count() / $limit);
        // on remplit le tableau
        
        $result['data'] = $data;
        $result['nbrePages'] = $nbrePages;
        $result['pageEncours'] = $pageEnCours;
        $result['limit'] = $limit;         
        return $result;
        
    }

    /**
    * @return Inscription[] Returns an array of Inscription objects
    */
    public function listeDesElevesInscritParPromoParEtablissementParClassePaginated($promo, $etablissement, $classe, $search, int $pageEnCours, int $limit): array
    {
    
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('i')
            ->leftJoin('i.eleve', 'e')
            ->andWhere('i.promo = :promo')
            ->andWhere('i.classe = :classe')
            ->andWhere('i.etablissement = :etablissement');
            if ($search) {
                $query->andWhere('e.matricule LIKE :search OR e.prenom LIKE :search OR e.nom LIKE :search OR e.telephone LIKE :search ')
                ->setParameter('search', '%' . $search . '%');
            }

        $query->setParameter('promo', $promo)
            ->setParameter('etablissement', $etablissement)
            ->setParameter('classe', $classe)
            ->orderBy('e.prenom', 'ASC')
            ->addOrderBy('e.nom', 'ASC')
            ->addOrderBy('e.matricule', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult(($pageEnCours * $limit) - $limit);
        $paginator = new Paginator($query);
        $data = $paginator->getQuery()->getResult();
        // on calcule le nombre des pages

        $nbrePages = ceil($paginator->count() / $limit);
        // on remplit le tableau
        
        $result['data'] = $data;
        $result['nbrePages'] = $nbrePages;
        $result['pageEncours'] = $pageEnCours;
        $result['limit'] = $limit;         
        return $result;
        
    }

    /**
    * @return Inscription[] Returns an array of Inscription objects
    */
    public function listeDesElevesInscritActifParPromoParEtablissementParClassePaginated($promo, $etablissement, $classe, $search, int $pageEnCours, int $limit): array
    {
    
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('i')
            ->leftJoin('i.eleve', 'e')
            ->andWhere('i.promo = :promo')
            ->andWhere('i.classe = :classe')
            ->andWhere('i.statut != :statut')
            ->andWhere('i.etablissement = :etablissement');
            if ($search) {
                $query->andWhere('e.matricule LIKE :search OR e.prenom LIKE :search OR e.nom LIKE :search OR e.telephone LIKE :search ')
                ->setParameter('search', '%' . $search . '%');
            }

        $query->setParameter('promo', $promo)
            ->setParameter('etablissement', $etablissement)
            ->setParameter('classe', $classe)
            ->setParameter('statut', 'inactif')
            ->orderBy('e.prenom', 'ASC')
            ->addOrderBy('e.nom', 'ASC')
            ->addOrderBy('e.matricule', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult(($pageEnCours * $limit) - $limit);
        $paginator = new Paginator($query);
        $data = $paginator->getQuery()->getResult();
        // on calcule le nombre des pages

        $nbrePages = ceil($paginator->count() / $limit);
        // on remplit le tableau
        
        $result['data'] = $data;
        $result['nbrePages'] = $nbrePages;
        $result['pageEncours'] = $pageEnCours;
        $result['limit'] = $limit;         
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

    public function findCountInscriptionId($type, $etablissement, $promo): ?int
    {
        $result = $this->createQueryBuilder('p')
            ->select('Count(p.id)')
            ->andWhere('p.type = :type')
            ->andWhere('p.etablissement = :etab')
            ->andWhere('p.promo = :promo')
            ->setParameter('etab', $etablissement)
            ->setParameter('promo', $promo)
            ->setParameter('type', $type)
            ->getQuery()
            ->getSingleScalarResult();
        return $result;
    }

    public function nombreInscriptionParTypeParCursus($type, $cursus, $promo): ?int
    {
        $result = $this->createQueryBuilder('i')
            ->select('Count(i.id)')
            ->leftJoin('i.classe', 'c')
            ->leftJoin('c.formation', 'f')   
            ->andWhere('i.promo = :promo')
            ->andWhere('f.cursus = :cursus')
            ->andWhere('i.type = :type')
            ->setParameter('promo', $promo)
            ->setParameter('type', $type)
            ->setParameter('cursus', $cursus)
            ->getQuery()
            ->getSingleScalarResult();
        return $result;
    }

    
}
