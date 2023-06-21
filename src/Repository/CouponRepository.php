<?php

namespace App\Repository;

use App\Entity\Coupon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Coupon>
 *
 * @method Coupon|null find($id, $lockMode = null, $lockVersion = null)
 * @method Coupon|null findOneBy(array $criteria, array $orderBy = null)
 * @method Coupon[]    findAll()
 * @method Coupon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CouponRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Coupon::class);
    }

    public function save(Coupon $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Coupon $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
        public function findTaux()
{
    $query = $this->createQueryBuilder('c')
        ->select('c.taux')
        ->getQuery();

    return $query->getScalarResult();
}
public function getTaux(): array
{
    $taux = [];
    $coupons = $this->findAll();
    foreach ($coupons as $coupon) {
        $taux[] = $coupon->getTaux();
    }
    shuffle($taux); // shuffle the array to randomize the order
    return $taux;
}
public function getTauxWithType(): array
{
    $tauxWithType = [];
    $coupons = $this->findAll();
    foreach ($coupons as $coupon) {
        $tauxWithType[] = [
            'taux' => $coupon->getTaux(),
            'type' => $coupon->getType(),
        ];
    }
    shuffle($tauxWithType); // shuffle the array to randomize the order
    return $tauxWithType;
}
public function findByCodeCouponOrType(string $query): array
{
    return $this->createQueryBuilder('c')
        ->andWhere('c.code_coupon LIKE :query OR c.type LIKE :query')
        ->setParameter('query', '%'.$query.'%')
        ->orderBy('c.id', 'ASC')
        ->getQuery()
        ->getResult();
}
public function findAllSortedByTauxReduction($order = 'ASC')
{
    $queryBuilder = $this->createQueryBuilder('c')
        ->orderBy('c.taux', $order);

    return $queryBuilder->getQuery()->getResult();
}
public function findOneByCodeCoupon($codeCoupon): ?Coupon
{
    return $this->createQueryBuilder('c')
        ->andWhere('c.code_coupon = :codeCoupon')
        ->setParameter('codeCoupon', $codeCoupon)
        ->getQuery()
        ->getOneOrNullResult();
}
public function getCodeCouponByTaux($taux)
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->select('c.code_coupon')
            ->andWhere('c.taux = :taux')
            ->setParameter('taux', $taux)
            ->setMaxResults(1);

        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result ? $result['code_coupon'] : null;
    }


    
    


//    /**
//     * @return Coupon[] Returns an array of Coupon objects
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

//    public function findOneBySomeField($value): ?Coupon
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}