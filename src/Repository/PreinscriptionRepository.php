<?php

namespace App\Repository;

use App\Entity\Preinscription;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Preinscription>
 */
class PreinscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Preinscription::class);
    }

    //    /**
    //     * @return Preinscription[] Returns an array of Preinscription objects
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

    //    public function findOneBySomeField($value): ?Preinscription
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }


    /**
    * @return Inscription[] Returns an array of Inscription objects
    */
    public function listeDesElevesPreinscritParPromoParEtablissement($promo, $etablissement, $search, int $pageEnCours, int $limit): array
    {
    
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.promo = :promo')
            ->andWhere('p.etablissement = :etablissement');
            if ($search) {
                $query->andWhere(' p.prenom LIKE :search OR p.telephone LIKE :search ')
                ->setParameter('search', '%' . $search . '%');
            }

            $query->setParameter('promo', $promo)
            ->setParameter('etablissement', $etablissement)
            ->orderBy('p.prenom', 'ASC')
            ->addOrderBy('p.nom', 'ASC')
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
}
