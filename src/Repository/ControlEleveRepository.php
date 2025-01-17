<?php

namespace App\Repository;

use App\Entity\ControlEleve;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ControlEleve>
 */
class ControlEleveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ControlEleve::class);
    }

    //    /**
    //     * @return ControlEleve[] Returns an array of ControlEleve objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ControlEleve
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     *  @return ControlEleve[] Returns an array of ControlEleve objects
     */
    public function listeDesControlesParPromoParEtablissement($promo, $etablissement, $search, $periode, $type)
    {
        $query = $this->createQueryBuilder('c')
            ->leftJoin('c.inscription', 'i')
            ->leftJoin('i.eleve', 'e')
            ->leftJoin('i.classe', 'cl')
            ->andWhere('c.dateControl = :periode')
            ->andWhere('c.etablissement = :etablissement')
            ->andWhere('c.promo = :promo')
            ->setParameter('periode', $periode)
            ->setParameter('promo', $promo)
            ->setParameter('etablissement', $etablissement);

        if ($search) {
            $query->andWhere('e.matricule LIKE :search OR e.prenom LIKE :search OR e.telephone LIKE :search OR cl.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($type) {
            if ($type == 'absence') {
                $query->andWhere('c.type = :type or c.type = :type2')
                ->setParameter('type', $type)
                ->setParameter('type2', "absence global");
            }else{

                $query->andWhere('c.type = :type')
                ->setParameter('type', $type);
            }
        }

        return $query
            ->orderBy('e.prenom', 'ASC')
            ->addOrderBy('e.nom', 'ASC')
            ->addOrderBy('e.matricule', 'ASC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }

    /**
     *  @return ControlEleve[] Returns an array of ControlEleve objects
     */
    public function listeDesControlesParPromoParClasse($promo, $classes, $search, $periode, $type)
    {
        // Transformer les objets ClasseRepartition en IDs si nécessaire
        $classIds = [];
        foreach ($classes as $classe) {
            if ($classe instanceof \App\Entity\ClasseRepartition) {
                $classIds[] = $classe->getId(); // Extraire l'ID de chaque classe
            } else {
                $classIds[] = $classe; // Si c'est déjà un ID
            }
        }

        $query = $this->createQueryBuilder('c')
            ->leftJoin('c.inscription', 'i')
            ->leftJoin('i.eleve', 'e')
            ->leftJoin('i.classe', 'cl')
            ->andWhere('c.dateControl = :periode')
            ->andWhere('cl.id IN (:classes)') // Utilisation des IDs de classes
            ->andWhere('c.promo = :promo')
            ->setParameter('periode', $periode)
            ->setParameter('promo', $promo)
            ->setParameter('classes', $classIds); // Passer les IDs en paramètre

        // Filtrage par recherche si fourni
        if ($search) {
            $query->andWhere('e.matricule LIKE :search OR e.prenom LIKE :search OR e.telephone LIKE :search OR cl.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Filtrage par type si fourni
        if ($type) {
            if ($type == 'absence') {
                $query->andWhere('c.type = :type OR c.type = :type2')
                    ->setParameter('type', $type)
                    ->setParameter('type2', 'absence global');
            } else {
                $query->andWhere('c.type = :type')
                    ->setParameter('type', $type);
            }
        }

        return $query
            ->orderBy('e.prenom', 'ASC')
            ->addOrderBy('e.nom', 'ASC')
            ->addOrderBy('e.matricule', 'ASC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }

    
    /**
     * @return array Returns an array with the count of controls grouped by type
     */
    public function listeDesControlesParEleveGroupe($inscription, $etat, $periode = null, $trimestre = null): array
    {
        $query = $this->createQueryBuilder('c')
            ->select('c.type, COUNT(c.id) as nbControles')
            ->andWhere('c.inscription = :inscription')
            ->andWhere('c.etat = :etat')
            ->setParameter('inscription', $inscription)
            ->setParameter('etat', $etat);

        // Filtre par période (mois)
        if ($periode) {
            $query->andWhere('SUBSTRING(c.dateControl, 6, 2) = :month')
            ->setParameter('month', str_pad($periode, 2, '0', STR_PAD_LEFT));
        }

        // Filtre par trimestre
        if ($trimestre && $trimestre != 'annuel') {
            $query->andWhere('c.trimestre = :trimestre')
                ->setParameter('trimestre', $trimestre);
        }

        // Regroupement par type
        $query->groupBy('c.type');

        // Exécution de la requête et retour des résultats
        return $query->getQuery()->getResult();
    }

    /**
     * @return array Returns an array with the count of controls grouped by type
     */
    public function listeDesControlesParClasseGroupe($classe, $periode = null, $trimestre = null): array
    {
        $query = $this->createQueryBuilder('c')
            ->select('c.type, COUNT(c.id) as nbControles')
            ->leftJoin('c.inscription', 'i')
            ->andWhere('i.classe = :classe')
            ->andWhere('i.etatScol != :etat')
            ->setParameter('etat', 'inactif')
            ->setParameter('classe', $classe);

        // Filtre par période (mois)
        if ($periode) {
            $query->andWhere('SUBSTRING(c.dateControl, 6, 2) = :month')
            ->setParameter('month', str_pad($periode, 2, '0', STR_PAD_LEFT));
        }

        // Filtre par trimestre
        if ($trimestre && $trimestre != 'annuel') {
            $query->andWhere('c.trimestre = :trimestre')
                ->setParameter('trimestre', $trimestre);
        }

        // Regroupement par type
        $query->groupBy('c.type');

        // Exécution de la requête et retour des résultats
        return $query->getQuery()->getResult();
    }




}
