<?php

namespace App\Repository;

use App\Entity\InscriptionActivite;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<InscriptionActivite>
 */
class InscriptionActiviteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InscriptionActivite::class);
    }

    //    /**
    //     * @return InscriptionActivite[] Returns an array of InscriptionActivite objects
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

    //    public function findOneBySomeField($value): ?InscriptionActivite
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
    public function listeDesElevesExterneInscritParPromoParEtablissement($promo, $etablissement, $search, $search_activite, int $pageEnCours, int $limit): array
    {
    
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('i')
            ->leftJoin('i.eleve', 'e')
            ->leftJoin('i.tarifActivite', 't')
            ->leftJoin('t.activite', 'a')
            ->andWhere('i.promo = :promo')
            ->andWhere('i.etablissement = :etablissement');
            if ($search) {
                $query->andWhere('e.matricule LIKE :search OR e.prenom LIKE :search OR e.telephone LIKE :search ')
                ->setParameter('search', '%' . $search . '%');
            }

            if ($search_activite) {
                $query->andWhere('a.id = :activite ')
                ->setParameter('activite', $search_activite);
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
