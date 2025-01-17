<?php

namespace App\Repository;

use App\Entity\HeureTravaille;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HeureTravaille>
 */
class HeureTravailleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HeureTravaille::class);
    }

    //    /**
    //     * @return HeureTravaille[] Returns an array of HeureTravaille objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('h.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?HeureTravaille
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * @return int|null Returns the sum of hours for the specified personnel
     */
    public function sommeHeureTravaillePersonnel($personnelId, $date): ?int
    {
        // Extraire le mois et l'année de la date fournie
        $year = substr($date, 0, 4);
        $month = substr($date, 5, 2);

        // Créer la date de début et de fin pour le mois donné
        $startDate = new \DateTime("{$year}-{$month}-01");
        $endDate = clone $startDate;
        $endDate->modify('last day of this month');

        return $this->createQueryBuilder('h')
            ->leftJoin('h.event', 'e')
            ->select('SUM(h.heureReel) as totalHours')
            ->andWhere('e.enseignant = :personnelId')
            ->andWhere('h.periode BETWEEN :startDate AND :endDate')
            ->setParameter('personnelId', $personnelId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
    }


    public function listeEvenementTransmiseParPeriodeParParPromoEtablissement($periode, $promo, $etablissement, $search = null): array
    {
        // Extraire l'année et le mois de la période fournie
        $year = substr($periode, 0, 4);
        $month = substr($periode, 5, 2);

        // Créer les objets DateTime pour le début et la fin du mois
        $startDate = new \DateTime("{$year}-{$month}-01");
        $endDate = (clone $startDate)->modify('last day of this month');
        // dd($startDate, $endDate);

        // Créer la requête pour récupérer tous les événements
        $queryBuilder = $this->createQueryBuilder('h')
            ->leftJoin('h.event', 'e')
            ->leftJoin('e.enseignant', 'pa')
            ->leftJoin('pa.personnel', 'p')
            ->andWhere('h.etablissement = :etablissement')
            ->andWhere('h.promo = :promo')
            ->andWhere('h.periode BETWEEN :date1 AND :date2');

            if ($search) {
                $queryBuilder->andWhere('p.matricule LIKE :search OR p.prenom LIKE :search OR p.telephone LIKE :search ')
                ->setParameter('search', '%' . $search . '%');
            }
            $queryBuilder->setParameter('etablissement', $etablissement)
            ->setParameter('promo', $promo)
            ->setParameter('date1', $startDate)
            ->setParameter('date2', $endDate)
            ->addOrderBy('p.prenom', 'ASC')
            ->addOrderBy('p.nom', 'ASC')
            ->addOrderBy('p.matricule', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }
}
