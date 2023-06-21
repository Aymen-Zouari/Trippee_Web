<?php

namespace App\Repository;

use App\Entity\Chauffeur;
use App\Entity\Utilisateur;
use App\Entity\Role;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Chauffeur>
 *
 * @method Chauffeur|null find($id_ch, $lockMode = null, $lockVersion = null)
 * @method Chauffeur|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chauffeur[]    findAll()
 * @method Chauffeur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChauffeurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chauffeur::class);
    }

    public function save(Chauffeur $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Chauffeur $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

   

    public function update(Chauffeur $chauffeur): int
{
    $entityManager = $this->getEntityManager();

    $query = $entityManager->createQuery('
        UPDATE App\Entity\Utilisateur u 
        SET u.cin = :cin, u.nom = :nom, u.prenom = :prenom
        WHERE u.id_user IN (
            SELECT u2.id_user 
            FROM App\Entity\Utilisateur u2 
            JOIN u.roles r 
            JOIN r.chauffeurs ch
            WHERE ch.id_ch = :id_ch AND r.id_role = :id_role AND u.id_user = :id_user
        )
        
    ');
    $query->setParameters([
        'cin' => $chauffeur->getIdRole()->getIdUser()->getCin(),
        'nom' => $chauffeur->getIdRole()->getIdUser()->getNom(),
        'prenom' => $chauffeur->getIdRole()->getIdUser()->getPrenom(),
        'id_ch' => $chauffeur->getIdCh(),
        'id_role' => $chauffeur->getIdRole()->getIdRole(),
        'id_user' => $chauffeur->getIdRole()->getIdUser()->getIdUser()
    ]);

    return $query->execute();
}


public function advancedSearch($query, $cin, $nom, $prenom, $email, $etat)
{
    $qb = $this->createQueryBuilder('ch')
        ->join('ch.id_role', 'r')
        ->join('r.id_user', 'u');

    if (!empty($query)) {
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->like('u.cin', ':query'),
            $qb->expr()->like('u.nom', ':query'),
            $qb->expr()->like('u.prenom', ':query'),
            $qb->expr()->like('ch.email', ':query'),
            $qb->expr()->like('ch.etat', ':query')
        ))
        ->setParameter('query', '%' . $query . '%');
    }

    if (!empty($cin)) {
        $qb->andWhere('u.cin LIKE :cin')
            ->setParameter('cin', '%' . $cin . '%');
    }

    if (!empty($nom)) {
        $qb->andWhere('u.nom LIKE :nom')
            ->setParameter('nom', '%' . $nom . '%');
    }

    if (!empty($prenom)) {
        $qb->andWhere('u.prenom LIKE :prenom')
            ->setParameter('prenom', '%' . $prenom . '%');
    }

    if (!empty($email)) {
        $qb->andWhere('ch.email LIKE :email')
            ->setParameter('email', '%' . $email . '%');
    }

    if (!empty($etat)) {
        $qb->andWhere('ch.etat LIKE :etat')
            ->setParameter('etat', '%' . $etat . '%');
    }

    return $qb->getQuery()->getResult();
}

    public function findAllSorted() : array
    {
        $queryBuilder = $this->createQueryBuilder('ch')
        ->leftJoin('ch.id_role', 'r')
        ->leftJoin('r.id_user', 'u')
        ->orderBy('u.nom', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }

    public function findAllSorted2() : array
    {
        $queryBuilder = $this->createQueryBuilder('ch')
        ->leftJoin('ch.id_role', 'r')
        ->leftJoin('r.id_user', 'u')
        ->orderBy('u.nom', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

    public function findAllSorted3() : array
    {
        $queryBuilder = $this->createQueryBuilder('ch')
        ->leftJoin('ch.id_role', 'r')
        ->leftJoin('r.id_user', 'u')
        ->orderBy('u.prenom', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }

    public function findAllSorted4() : array
    {
        $queryBuilder = $this->createQueryBuilder('ch')
        ->leftJoin('ch.id_role', 'r')
        ->leftJoin('r.id_user', 'u')
        ->orderBy('u.prenom', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }


}