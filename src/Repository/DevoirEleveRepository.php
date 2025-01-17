<?php

namespace App\Repository;

use App\Entity\DevoirEleve;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DevoirEleve>
 */
class DevoirEleveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DevoirEleve::class);
    }

    //    /**
    //     * @return DevoirEleve[] Returns an array of DevoirEleve objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?DevoirEleve
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

   

    public function listeDevoirsParMois($periode, $classe, $promo, $matiere = NULL, $enseignant = NULL): array
    {
        // Extraire le mois de la période fournie
        $month = (int) $periode;
    
        $query = $this->createQueryBuilder('d')
            ->andWhere('d.classe = :classe')
            ->andWhere('d.promo = :promo')
            ->andWhere('SUBSTRING(d.dateDevoir, 6, 2) = :month')
            ->setParameter('classe', $classe)
            ->setParameter('promo', $promo)
            ->setParameter('month', str_pad($month, 2, '0', STR_PAD_LEFT));  // Format du mois sur 2 chiffres
            if ($matiere) {
                $query->andWhere('d.matiere = :matiere')
                    ->setParameter('matiere', $matiere);
            }

            if ($enseignant) {
                $query->andWhere('d.enseignant = :enseignant')
                    ->setParameter('enseignant', $enseignant);
            }
        return  $query->getQuery()
            ->getResult();
    }

    public function listeDevoirsAnnuel($classe, $promo, $enseignant = NULL): array
    {    
        $query = $this->createQueryBuilder('d')
            ->andWhere('d.classe = :classe')
            ->andWhere('d.promo = :promo')
            ->setParameter('classe', $classe)
            ->setParameter('promo', $promo);

            if ($enseignant) {
                $query->andWhere('d.enseignant = :enseignant')
                    ->setParameter('enseignant', $enseignant);
            }
        return $query->getQuery()
            ->getResult();
    }
    
}
