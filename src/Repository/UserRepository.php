<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function userByIdPaginated($id): array
    {
        $limit = 1;
        $pageEnCours = 1;
        $result = [];
        $query =  $this->createQueryBuilder('u')
            ->where('u.id = :id ')
            ->setParameter('id', $id)
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
    }

    public function listePersonnelGeneralActifParEtablissement($etablissement, $pageEnCours, $limit, $search = null): array
    {
        $type1 = 'enseignant';
        $type2 = 'personnel';
        $limit = abs($limit);
        $result = [];
        $query =  $this->createQueryBuilder('u')
            ->where('u.typeUser = :type1 OR u.typeUser = :type2')
            ->andWhere('u.statut = :statut')
            ->andWhere('u.etablissement = :etablissement')
            ->setParameter('type1', $type1)
            ->setParameter('type2', $type2)
            ->setParameter('statut', 'actif')
            ->setParameter('etablissement', $etablissement);
        if ($search) {
            $query->andWhere('u.prenom LIKE :val Or u.nom LIKE :val Or u.telephone LIKE :val Or u.matricule LIKE :val')
            ->setParameter('val', '%' . $search . '%');
        }
        $query->orderBy('u.prenom', 'ASC')
            ->addOrderBy('u.nom', 'ASC')
            ->addOrderBy('u.matricule', 'ASC')
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
    }

    public function rechercheUserParTypeParEtablissement($value, $type, $etablissement): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.typeUser = :type')
            ->andWhere('u.statut = :statut')
            ->andWhere('u.etablissement = :etablissement')
            ->andWhere('u.prenom LIKE :val Or u.nom LIKE :val Or u.telephone LIKE :val Or u.matricule LIKE :val')
            ->setParameter('type', $type)
            ->setParameter('statut', 'actif')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('val', '%' . $value . '%')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }

    public function rechercheUserType1Type2ParEtablissement($value, $type1, $type2, $etablissement): array
    {
        // $type1 = 'enseignant';
        // $type2 = 'personnel';
        return $this->createQueryBuilder('u')
            ->where('u.typeUser = :type1 OR u.typeUser = :type2')
            ->andWhere('u.statut = :statut')
            ->andWhere('u.etablissement = :etablissement')
            ->andWhere('u.prenom LIKE :val OR u.nom LIKE :val OR u.telephone LIKE :val OR u.matricule LIKE :val')
            ->setParameter('type1', $type1)
            ->setParameter('type2', $type2)
            ->setParameter('statut', 'actif')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('val', '%' . $value . '%')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }

    public function findMaxId(): ?int
    {
        $result = $this->createQueryBuilder('u')
            ->select('MAX(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
        return $result;
    }


    public function listeDesUtilisateursParTypeEtablissement($etablissement, $type, $pageEnCours, $limit, $search = null): array
    {
        $limit = abs($limit);
        $result = [];
        $query =  $this->createQueryBuilder('u')
            ->where('u.typeUser IN (:type) ')
            ->andWhere('u.etablissement = :etablissement')
            ->setParameter('type', $type)
            ->setParameter('etablissement', $etablissement);
        if ($search) {
            $query->andWhere('u.prenom LIKE :val Or u.nom LIKE :val Or u.telephone LIKE :val Or u.matricule LIKE :val')
            ->setParameter('val', '%' . $search . '%');
        }
        $query->orderBy('u.typeUser', 'ASC')
            ->addOrderBy('u.prenom', 'ASC')
            ->addOrderBy('u.nom', 'ASC')
            ->addOrderBy('u.matricule', 'ASC')
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
    }
}
