<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 *
 * @method Client|null find($id_client, $lockMode = null, $lockVersion = null)
 * @method Client|null findOneBy(array $criteria, array $orderBy = null)
 * @method Client[]    findAll()
 * @method Client[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function save(Client $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Client $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function update(Client $client): int
    {
        $entityManager = $this->getEntityManager();
    
        $query = $entityManager->createQuery('
            UPDATE App\Entity\Utilisateur u 
            SET u.cin = :cin, u.nom = :nom, u.prenom = :prenom
            WHERE u.id_user IN (
                SELECT u2.id_user 
                FROM App\Entity\Utilisateur u2 
                JOIN u.roles r 
                JOIN r.clients ch
                WHERE ch.id_client = :id_client AND r.id_role = :id_role AND u.id_user = :id_user
            )
            
        ');
        $query->setParameters([
            'cin' => $client->getIdRole()->getIdUser()->getCin(),
            'nom' => $client->getIdRole()->getIdUser()->getNom(),
            'prenom' => $client->getIdRole()->getIdUser()->getPrenom(),
            'id_client' => $client->getIdClient(),
            'id_role' => $client->getIdRole()->getIdRole(),
            'id_user' => $client->getIdRole()->getIdUser()->getIdUser()
        ]);
    
        return $query->execute();
    }

    public function advancedSearch($query, $cin, $nom, $prenom, $email, $etat)
{
    $qb = $this->createQueryBuilder('client')
        ->join('client.id_role', 'r')
        ->join('r.id_user', 'u');

    if (!empty($query)) {
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->like('u.cin', ':query'),
            $qb->expr()->like('u.nom', ':query'),
            $qb->expr()->like('u.prenom', ':query'),
            $qb->expr()->like('client.email', ':query'),
            $qb->expr()->like('client.etat', ':query')
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
        $qb->andWhere('client.email LIKE :email')
            ->setParameter('email', '%' . $email . '%');
    }

    if (!empty($etat)) {
        $qb->andWhere('client.etat LIKE :etat')
            ->setParameter('etat', '%' . $etat . '%');
    }

    return $qb->getQuery()->getResult();
}

public function findAllSorted() : array
{
    $queryBuilder = $this->createQueryBuilder('cl')
    ->leftJoin('cl.id_role', 'r')
    ->leftJoin('r.id_user', 'u')
    ->orderBy('u.nom', 'ASC');

    return $queryBuilder->getQuery()->getResult();
}

public function findAllSorted2() : array
{
    $queryBuilder = $this->createQueryBuilder('cl')
    ->leftJoin('cl.id_role', 'r')
    ->leftJoin('r.id_user', 'u')
    ->orderBy('u.nom', 'DESC');

    return $queryBuilder->getQuery()->getResult();
}

public function findAllSorted3() : array
{
    $queryBuilder = $this->createQueryBuilder('cl')
    ->leftJoin('cl.id_role', 'r')
    ->leftJoin('r.id_user', 'u')
    ->orderBy('u.prenom', 'ASC');

    return $queryBuilder->getQuery()->getResult();
}

public function findAllSorted4() : array
{
    $queryBuilder = $this->createQueryBuilder('cl')
    ->leftJoin('cl.id_role', 'r')
    ->leftJoin('r.id_user', 'u')
    ->orderBy('u.prenom', 'DESC');

    return $queryBuilder->getQuery()->getResult();
}

}