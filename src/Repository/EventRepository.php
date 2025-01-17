<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\HeureTravaille;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    //    /**
    //     * @return Event[] Returns an array of Event objects
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

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }


    public function evenementSimulaire($personnel, $classe, $matiere, $promo, $currentDayOfWeek, $currentStartHour): array
    {
        $events = $this->createQueryBuilder('e')
            ->where('e.enseignant = :personnel')
            ->andWhere('e.classe = :classe')
            ->andWhere('e.matiere = :matiere')
            ->andWhere('e.promo = :promo')
            ->setParameter('personnel', $personnel)
            ->setParameter('classe', $classe)
            ->setParameter('matiere', $matiere)
            ->setParameter('promo', $promo)
            ->addOrderBy('e.start', 'ASC')
            ->getQuery()
            ->getResult();
    
        // Filtrer les résultats en PHP
        return array_filter($events, function($event) use ($currentDayOfWeek, $currentStartHour) {
            return $event->getStart()->format('N') == $currentDayOfWeek &&
                   $event->getStart()->format('H:i:s') == $currentStartHour;
        });
    }


    // public function listeEvenementNonTransmiseParPeriodeParParPromoEtablissement($periode, $promo, $etablissement): array
    // {
    //     // Créer les bornes de la période (début et fin de journée)
    //     $dateDebut = new \DateTime($periode);
    //     $dateFin = new \DateTime($periode);
    //     $dateFin->setTime(23, 59, 59);

    //     // Créer la requête pour récupérer tous les événements
    //     $queryBuilder = $this->createQueryBuilder('e')
    //     ->leftJoin('e.heureTravaille', 'ht', 'WITH', 'ht.periode BETWEEN :dateDebut AND :dateFin')

    //         ->andWhere('e.etablissement = :etablissement')
    //         ->andWhere('e.promo = :promo')
    //         ->andWhere('e.start >= :dateDebut')
    //         ->andWhere('e.start <= :dateFin')
    //         ->setParameter('etablissement', $etablissement)
    //         ->setParameter('promo', $promo)
    //         ->setParameter('dateDebut', $dateDebut)
    //         ->setParameter('dateFin', $dateFin)
    //         ->addOrderBy('e.start', 'ASC');

    //     // Subquery pour obtenir les IDs des événements déjà transmis dans HeureTravaille
    //     $subQueryBuilder = $this->getEntityManager()->createQueryBuilder()
    //         ->select('IDENTITY(ht.event)')
    //         ->from(HeureTravaille::class, 'ht')
    //         ->where('ht.promo = :promo')
    //         ->andWhere('ht.periode = :periode');

    //     // Exclure les événements qui ont été transmis
    //     $queryBuilder->andWhere($queryBuilder->expr()->notIn('e.id', $subQueryBuilder->getDQL()))
    //         ->setParameter('periode', $periode);

    //     return $queryBuilder->getQuery()->getResult();
    // }


    public function listeEvenementNonTransmiseParPeriodeParParPromoEtablissement($periode, $promo, $etablissement, $search = null): array
    {
        // Créer les bornes de la période (début et fin de journée)
        $dateDebut = new \DateTime($periode);
        $dateFin = new \DateTime($periode);
        $dateFin->setTime(23, 59, 59);

        // Créer la requête pour récupérer tous les événements
        $queryBuilder = $this->createQueryBuilder('e')
            ->leftJoin(HeureTravaille::class, 'ht', 'WITH', 'ht.event = e.id AND ht.periode BETWEEN :dateDebut AND :dateFin AND ht.promo = :promo AND ht.etablissement = :etablissement')
            ->leftJoin('e.enseignant', 'pa')
            ->leftJoin('pa.personnel', 'p')
            ->andWhere('ht.id IS NULL') // Exclure les événements qui ont des entrées correspondantes dans HeureTravaille
            ->andWhere('e.etablissement = :etablissement')
            ->andWhere('e.promo = :promo')
            ->andWhere('e.start >= :dateDebut')
            ->andWhere('e.start <= :dateFin');
            if ($search) {
                $queryBuilder->andWhere('p.matricule LIKE :search OR p.prenom LIKE :search OR p.telephone LIKE :search ')
                ->setParameter('search', '%' . $search . '%');
            }
            $queryBuilder->setParameter('etablissement', $etablissement)
            ->setParameter('promo', $promo)
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->addOrderBy('e.start', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }

    public function listeEvenementParClasseParTypeParPromoParEtablissementCompriseEntre($classe, $type, $date1, $date2, $promo, $etablissement): array
    {
        // Créer les bornes de la période (début et fin de journée)
        $dateDebut = new \DateTime($date1);
        $dateFin = new \DateTime($date2);

        // Définir les bornes horaires pour chaque type (matinée, soirée, journée)
        if ($type === 'matinée') {
            // Matinée : 07h00 à 13h00
            $dateDebut->setTime(7, 0, 0);
            $dateFin->setTime(13, 0, 0);
        } elseif ($type === 'soirée') {
            // Soirée : 13h00 à minuit (23h59:59)
            $dateDebut->setTime(13, 0, 0);
            $dateFin->setTime(23, 59, 59);
        } elseif ($type === 'journée') {
            // Journée : 00h00 à 23h59:59
            $dateDebut->setTime(0, 0, 0);
            $dateFin->setTime(23, 59, 59);
        }

        // Créer la requête pour récupérer les événements en fonction du type
        $queryBuilder = $this->createQueryBuilder('e')
            ->andWhere('e.classe = :classe')
            ->andWhere('e.etablissement = :etablissement')
            ->andWhere('e.promo = :promo')
            ->andWhere('e.start >= :dateDebut')
            ->andWhere('e.start <= :dateFin')
            ->setParameter('classe', $classe)
            ->setParameter('etablissement', $etablissement)
            ->setParameter('promo', $promo)
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->addOrderBy('e.start', 'ASC');

        // Exécuter la requête et retourner les résultats
        return $queryBuilder->getQuery()->getResult();
    }


       /**
         * @return ClasseRepartition[] Returns an array of distinct ClasseRepartition objects
         */
        public function listeDesClassesParEnseignant($enseignant): array
        {
            return $this->createQueryBuilder('e')
                ->leftJoin('e.classe', 'c')  // Jointure avec l'entité ClasseRepartition
                ->andWhere('e.enseignant = :enseignant')
                ->setParameter('enseignant', $enseignant)
                ->select('DISTINCT c.id as id')  // Sélectionne l'id de la classe et la classe elle-même
                ->getQuery()
                ->getResult();
        }
}
