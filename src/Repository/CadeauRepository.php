<?php

namespace App\Repository;

use App\Entity\Cadeau;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cadeau>
 *
 * @method Cadeau|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cadeau|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cadeau[]    findAll()
 * @method Cadeau[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CadeauRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cadeau::class);
    }

    public function save(Cadeau $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Cadeau $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function findAllSortedByvaleur($order = 'ASC')
{
    $queryBuilder = $this->createQueryBuilder('c')
        ->orderBy('c.valeur', $order);

    return $queryBuilder->getQuery()->getResult();
}
public function findBycadeau(string $query): array
{
    return $this->createQueryBuilder('c')
        ->andWhere('c.nom_cadeay LIKE :query ')
        ->setParameter('query', '%'.$query.'%')
        ->orderBy('c.idcadeau', 'ASC')
        ->getQuery()
        ->getResult();
}

//    /**
//     * @return Cadeau[] Returns an array of Cadeau objects
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

//    public function findOneBySomeField($value): ?Cadeau
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
