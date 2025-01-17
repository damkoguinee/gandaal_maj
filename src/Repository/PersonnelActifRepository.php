<?php

namespace App\Repository;

use App\Entity\PersonnelActif;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PersonnelActif>
 */
class PersonnelActifRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonnelActif::class);
    }

    //    /**
    //     * @return PersonnelActif[] Returns an array of PersonnelActif objects
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

    //    public function findOneBySomeField($value): ?PersonnelActif
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function listeDesEnseignantsActifParEtablissementParPromo($etablissement, $promo): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.personnel', 'u')
            ->andWhere('u.etablissement = :etablissement')
            ->andWhere('u.statut = :statut')
            ->andWhere('p.promo = :promo')
            ->andWhere('p.type = :typeEnseignant OR p.type = :typePersonnelEnseignant')
            ->setParameter('statut', 'actif')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('promo', $promo)
            ->setParameter('typeEnseignant', 'enseignant')
            ->setParameter('typePersonnelEnseignant', 'personnel-enseignant')
            ->addOrderBy('u.prenom')
            ->getQuery()
            ->getResult();
    }

    public function listeDesEnseignantsActifParCursusParPromo($cursus, $promo): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.personnel', 'u')
            ->andWhere(':rattachementPedagogie MEMBER OF p.rattachementPedagogie')
            ->andWhere('u.statut = :statut')
            ->andWhere('p.promo = :promo')
            ->andWhere('p.type = :typeEnseignant OR p.type = :typePersonnelEnseignant')
            ->setParameter('rattachementPedagogie', $cursus)
            ->setParameter('statut', 'actif')
            ->setParameter('promo', $promo)
            ->setParameter('typeEnseignant', 'enseignant')
            ->setParameter('typePersonnelEnseignant', 'personnel-enseignant')
            ->addOrderBy('u.prenom')
            ->getQuery()
            ->getResult();
    }

    public function rechercheEnseignantActifParEtablissement($value, $etablissement, $promo): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.personnel', 'u')
            ->andWhere('u.etablissement = :etablissement')
            ->andWhere('u.statut = :statut')
            ->andWhere('p.promo = :promo')
            ->andWhere('p.type = :typeEnseignant OR p.type = :typePersonnelEnseignant')
            ->andWhere('u.prenom LIKE :val OR u.nom LIKE :val OR u.telephone LIKE :val OR u.matricule LIKE :val')
            ->setParameter('statut', 'actif')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('promo', $promo)
            ->setParameter('typeEnseignant', 'enseignant')
            ->setParameter('typePersonnelEnseignant', 'personnel-enseignant')
            ->setParameter('val', '%' . $value . '%')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }


    public function listeDesPersonnelsActifParTypeParEtablissementParPromo($type1, $type2, $etablissement, $promo): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.personnel', 'u')
            ->andWhere('u.etablissement = :etablissement')
            ->andWhere('u.statut = :statut')
            ->andWhere('p.promo = :promo')
            ->andWhere('p.type = :typeEnseignant OR p.type = :typePersonnelEnseignant')
            ->setParameter('statut', 'actif')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('promo', $promo)
            ->setParameter('typeEnseignant', $type1)
            ->setParameter('typePersonnelEnseignant', $type2)
            ->addOrderBy('u.prenom')
            ->getQuery()
            ->getResult();
    }

    public function listeDesPersonnelsActifParTypeParCursusParPromo($type1, $type2, $cursus, $promo): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.personnel', 'u')
            ->andWhere(':rattachementPedagogie MEMBER OF p.rattachementPedagogie')
            ->andWhere('u.statut = :statut')
            ->andWhere('p.promo = :promo')
            ->andWhere('p.type = :typeEnseignant OR p.type = :typePersonnelEnseignant')
            ->setParameter('rattachementPedagogie', $cursus)
            ->setParameter('statut', 'actif')
            ->setParameter('promo', $promo)
            ->setParameter('typeEnseignant', $type1)
            ->setParameter('typePersonnelEnseignant', $type2)
            ->addOrderBy('u.prenom')
            ->getQuery()
            ->getResult();
    }

    public function rechercheUserParEtablissement($value, $etablissement, $promo): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.personnel', 'u')
            ->andWhere('u.etablissement = :etablissement')
            ->andWhere('u.statut = :statut')
            ->andWhere('p.promo = :promo')
            ->andWhere('u.prenom LIKE :val OR u.nom LIKE :val OR u.telephone LIKE :val OR u.matricule LIKE :val')
            ->setParameter('statut', 'actif')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('promo', $promo)
            ->setParameter('val', '%' . $value . '%')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }

    public function rechercheUserParCursusParPromo($value, $cursus, $promo): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.personnel', 'u')
            ->andWhere(':rattachementPedagogie MEMBER OF p.rattachementPedagogie')
            ->andWhere('u.statut = :statut')
            ->andWhere('p.promo = :promo')
            ->andWhere('u.prenom LIKE :val OR u.nom LIKE :val OR u.telephone LIKE :val OR u.matricule LIKE :val')
            ->setParameter('statut', 'actif')
            ->setParameter('rattachementPedagogie', $cursus)
            ->setParameter('promo', $promo)
            ->setParameter('val', '%' . $value . '%')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }

    public function rechercheUserType1Type2ParEtablissementParPromo($value, $type1, $type2, $etablissement, $promo): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.personnel', 'u')
            ->where('u.typeUser = :type1 OR u.typeUser = :type2')
            ->andWhere('u.statut = :statut')
            ->andWhere('p.promo = :promo')
            ->andWhere('u.etablissement = :etablissement')
            ->andWhere('u.prenom LIKE :val OR u.nom LIKE :val OR u.telephone LIKE :val OR u.matricule LIKE :val')
            ->setParameter('type1', $type1)
            ->setParameter('type2', $type2)
            ->setParameter('statut', 'actif')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('promo', $promo)
            ->setParameter('val', '%' . $value . '%')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }


    /**
     
     * @return array
     */
    public function listePersonnelNonPaiyeParPeriode($date = null): array
    {
        // Extraire le mois et l'année de la date fournie
        $year = substr($date, 0, 4);
        $month = substr($date, 5, 2);

        // Créer la date de début et de fin pour le mois donné
        $startDate = new \DateTime("{$year}-{$month}-01");
        $endDate = clone $startDate;
        $endDate->modify('last day of this month');
        return $this->createQueryBuilder('p')
            ->leftJoin('p.personnelActif', 'u')
            ->leftJoin('u.paiementSalairePersonnels', 's', 'WITH', 's.periode BETWEEN :date1 AND :date2')
            ->andWhere('s.id IS NULL')
            ->setParameter('date1', $startDate)
            ->setParameter('date2', $endDate)
            ->addOrderBy('u.prenom', 'ASC') // Tri par prénom
            ->addOrderBy('u.nom', 'ASC')    // Ensuite, tri par nom
            ->getQuery()
            ->getResult();
    }

    /**
     
     * @return array
     */
    public function listePersonnelNonPaiyeParPeriodeParEtablissement($date, $etablissement): array
    {
        // Extraire le mois et l'année de la date fournie
        $year = substr($date, 0, 4);
        $month = substr($date, 5, 2);

        // Créer la date de début et de fin pour le mois donné
        $startDate = new \DateTime("{$year}-{$month}-01");
        $endDate = clone $startDate;
        $endDate->modify('last day of this month');
        return $this->createQueryBuilder('p')
            ->leftJoin('p.personnel', 'u')
            ->leftJoin('p.paiementSalairePersonnels', 's', 'WITH', 's.periode BETWEEN :date1 AND :date2')
            ->andWhere('s.id IS NULL')
            ->andWhere('u.etablissement = :etablissement')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('date1', $startDate)
            ->setParameter('date2', $endDate)
            ->addOrderBy('u.prenom', 'ASC') // Tri par prénom
            ->addOrderBy('u.nom', 'ASC')    // Ensuite, tri par nom
            ->getQuery()
            ->getResult();
    }

    /**
     
     * @return array
     */
    public function listePersonnelNonPaiyeParTypeParCursusParPeriodeParEtablissementt($type, $cursus, $periode, $etablissement, $promo): array
    {
        // Extraire le mois et l'année de la date fournie
        $year = substr($periode, 0, 4);
        $month = substr($periode, 5, 2);

        // Créer la date de début et de fin pour le mois donné
        $startDate = new \DateTime("{$year}-{$month}-01");
        $endDate = clone $startDate;
        $endDate->modify('last day of this month');
        $query = $this->createQueryBuilder('p')
            ->leftJoin('p.personnel', 'u')
            ->leftJoin('p.paiementSalairePersonnels', 's', 'WITH', 's.periode BETWEEN :date1 AND :date2')
            ->andWhere('s.id IS NULL')
            ->andWhere('u.etablissement = :etablissement')
            ->andWhere('p.promo = :promo')
            ;
        if ($type != 'général') {
            if ($type == 'enseignant') {
                $query->andWhere('p.type = :type1 or p.type = :type2')
                    ->setParameter('type1', 'enseignant')
                    ->setParameter('type2', 'personnel-enseignant');
            }else{
                $query->andWhere('p.type = :type')
                    ->setParameter('type', $type);
            }
        }

        if ($cursus != 'général') {
            $query->andWhere('p.rattachement = :rattachement')
                ->setParameter('rattachement', $cursus);
        }
        $query->setParameter('etablissement', $etablissement)
            ->setParameter('promo', $promo)
            ->setParameter('date1', $startDate)
            ->setParameter('date2', $endDate)
            ->addOrderBy('u.prenom', 'ASC') // Tri par prénom
            ->addOrderBy('u.nom', 'ASC')    // Ensuite, tri par nom
            ->getQuery()
            ->getResult();
        return $query;
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
    public function listePersonnelNonPaiyeParTypeParCursusParPeriodeParEtablissement($type, $cursus, $periode, $etablissement, $promo): array
    {
        // Extraire l'année et le mois de la période fournie
        $year = substr($periode, 0, 4);
        $month = substr($periode, 5, 2);

        // Créer les objets DateTime pour le début et la fin du mois
        $startDate = new \DateTime("{$year}-{$month}-01");
        $endDate = (clone $startDate)->modify('last day of this month');

        // Créer la requête
        $query = $this->createQueryBuilder('p')
            ->leftJoin('p.personnel', 'u')
            ->leftJoin('p.paiementSalairePersonnels', 's', 'WITH', 's.periode BETWEEN :date1 AND :date2')
            ->andWhere('s.id IS NULL')
            ->andWhere('p.promo = :promo')
            ->andWhere('u.etablissement = :etablissement')
            ->setParameter('date1', $startDate)
            ->setParameter('date2', $endDate)
            ->setParameter('promo', $promo)
            ->setParameter('etablissement', $etablissement);

        // Filtrer par type, si nécessaire
        if ($type !== 'général') {
            if ($type === 'enseignant') {
                $query->andWhere('p.type IN (:types)')
                    ->setParameter('types', ['enseignant', 'personnel-enseignant']);
            } else {
                $query->andWhere('p.type = :type')
                    ->setParameter('type', $type);
            }
        }

        // Filtrer par cursus, si nécessaire
        if ($cursus !== 'général') {
            $query->andWhere('p.rattachement = :rattachement')
                ->setParameter('rattachement', $cursus);
        }

        // Ajouter les critères de tri
        $query->addOrderBy('u.prenom', 'ASC')
            ->addOrderBy('u.nom', 'ASC');

        // Exécuter la requête et retourner les résultats
        return $query->getQuery()->getResult();
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
