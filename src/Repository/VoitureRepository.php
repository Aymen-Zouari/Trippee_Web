<?php

namespace App\Repository;
use App\Entity\Voiture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<Voiture>
 *
 * @method Voiture|null find($id, $lockMode = null, $lockVersion = null)
 * @method Voiture|null findOneBy(array $criteria, array $orderBy = null)
 * @method Voiture[]    findAll()
 * @method Voiture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VoitureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Voiture::class);
    }

    public function save(Voiture $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Voiture $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Voiture[] Returns an array of Voiture objects
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

//    public function findOneBySomeField($value): ?Voiture
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function countByLibelle($marque)
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.marque)')
            ->where('r.marque = :marque')
            ->setParameter('marque', $marque)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function advancedSearch($query, $id, $marque, $matricule)
    {
        $qb = $this->createQueryBuilder('c');

        if ($query) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('c.id', ':query'),
                $qb->expr()->like('c.marque', ':query'),
                $qb->expr()->like('c.matricule', ':query'),

            ))
                ->setParameter('query', '%' . $query . '%');
        }

        if ($id) {
            $qb->andWhere('c.id = :id')
                ->setParameter('id', $id);
        }

        if ($marque) {
            $qb->andWhere('c.marque = :marque')
                ->setParameter('marque', $marque);
        }

        if ($matricule) {
            $qb->andWhere('c.matricule = :matricule')
                ->setParameter('matricule', $matricule);
        }



        return $qb->getQuery()->getResult();
    }

    public function findAllSorted(): array
    {
        $queryBuilder = $this->createQueryBuilder('cl')
            ->orderBy('cl.marque', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }

}
