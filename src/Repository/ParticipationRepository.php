<?php

namespace App\Repository;

use App\Entity\Participation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Participation>
 *
 * @method Participation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Participation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Participation[]    findAll()
 * @method Participation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participation::class);
    }

    public function save(Participation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Participation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function advancedSearch($query, $id, $nmbrPlacePart, $id_co, $client)
    {
        $qb = $this->createQueryBuilder('c') ;

        if ($query) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('c.id', ':query'),
                $qb->expr()->like('c.Nmbr_place_part', ':query'),
                $qb->expr()->like('c.id_co', ':query'),
                $qb->expr()->like('c.id_client', ':query'),

            ))
                ->setParameter('query', '%' . $query . '%');
        }

        if ($id) {
            $qb->andWhere('c.id = :id')
                ->setParameter('id', $id);
        }

        if ($nmbrPlacePart) {
            $qb->andWhere('c.nmbrPlacePart = :nmbrPlacePart')
                ->setParameter('nmbrPlacePart', $nmbrPlacePart);
        }

        if ($id_co) {
            $qb->andWhere('c.id_co = :id_co')
                ->setParameter('id_co', $id_co);
        }

        if ($id_client) {
            $qb->andWhere('c.id_client = :id_client')
                ->setParameter('id_client', $id_client);
        }

        return $qb->getQuery()->getResult();
    }





    //    /**
    //     * @return Participation[] Returns an array of Participation objects
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

    //    public function findOneBySomeField($value): ?Participation
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function send_msg(String $num): void
    {

        $accountSid ='AC99faaf6c18b526197934d98bd930d0e1';
        $authToken = '0cda48fe6688e201370ebb88918e42b0';
        $client = new Client($accountSid, $authToken);
        $message = $client->messages->create(
            $num, // recipient's phone number
            array(
                'from' => '+16206282479', // your Twilio phone number
                'body' => 'Participation added !'
            )
        );
    }
}
