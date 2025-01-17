<?php

namespace App\Repository;

use App\Entity\Eleve;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Eleve>
 */
class EleveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Eleve::class);
    }

    //    /**
    //     * @return Eleve[] Returns an array of Eleve objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Eleve
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
    * @return Eleve[] Returns an array of Eleve objects
    */
    public function listeDesElevesParEtablissement($etablissement): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.etablissement = :etab')
            ->setParameter('etab', $etablissement)
            ->orderBy('e.prenom', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
    * @return Eleve[] Returns an array of Eleve objects
    */
    public function listeDesElevesParEtablissementParCategorie($etablissement, $categorie): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.etablissement = :etab')
            ->andWhere('e.categorie = :categorie')
            ->setParameter('etab', $etablissement)
            ->setParameter('categorie', $categorie)
            ->orderBy('e.prenom', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function rechercheEleveParEtablissementParCategorie($search, $etablissement, $categorie, int $pageEnCours, int $limit): array
    {
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('e')
            ->andWhere('e.etablissement = :etablissement')
            ->andWhere('e.categorie = :categorie')
            ;
            if ($search) {
                $query->andWhere('e.matricule LIKE :search OR e.prenom LIKE :search OR e.nom LIKE :search OR e.telephone LIKE :search ')
                ->setParameter('search', '%' . $search . '%');
            }
            $query->setParameter('etablissement', $etablissement)
            ->setParameter('categorie', $categorie)
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

    public function rechercheEleveParEtablissement($value, $etablissement): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.etablissement = :etablissement')
            ->andWhere('e.prenom LIKE :val Or e.nom LIKE :val Or e.telephone LIKE :val Or e.matricule LIKE :val')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('val', '%' . $value . '%')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }

    public function findMaxId($etablissement): ?int
    {
        $result = $this->createQueryBuilder('e')
            ->select('MAX(e.id)')
            ->andWhere('e.etablissement = :etab')
            ->setParameter('etab', $etablissement)
            ->getQuery()
            ->getSingleScalarResult();
        return $result;
    }

    public function findCountIdExterne($etablissement): ?int
    {
        $result = $this->createQueryBuilder('e')
            ->select('Count(e.id)')
            ->andWhere('e.etablissement = :etab')
            ->andWhere('e.categorie = :categorie')
            ->setParameter('etab', $etablissement)
            ->setParameter('categorie', 'externe')
            ->getQuery()
            ->getSingleScalarResult();
        return $result;
    }

    
}
