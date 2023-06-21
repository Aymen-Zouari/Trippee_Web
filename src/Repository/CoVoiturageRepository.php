<?php

namespace App\Repository;

use App\Entity\CoVoiturage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use Symfony\Component\Mailer\Mailer;
use Twilio\Rest\Client;
use Symfony\Component\Mime\Email;

/**
 * @extends ServiceEntityRepository<CoVoiturage>
 *
 * @method CoVoiturage|null find($id, $lockMode = null, $lockVersion = null)
 * @method CoVoiturage|null findOneBy(array $criteria, array $orderBy = null)
 * @method CoVoiturage[]    findAll()
 * @method CoVoiturage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoVoiturageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoVoiturage::class);
    }

    public function save(CoVoiturage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CoVoiturage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function countByLibelle($destination)
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.destination)')
            ->where('r.destination = :destination')
            ->setParameter('destination', $destination)
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function countByLibelle2($depart)
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.depart)')
            ->where('r.depart = :depart')
            ->setParameter('depart', $depart)
            ->getQuery()
            ->getSingleScalarResult();
    }



    public function advancedSearch($query, $id, $depart, $destination, $nmbr_place)
    {
        $qb = $this->createQueryBuilder('c');

        if ($query) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('c.id', ':query'),
                $qb->expr()->like('c.depart', ':query'),
                $qb->expr()->like('c.destination', ':query'),
                $qb->expr()->like('c.nmbr_place', ':query'),

            ))
                ->setParameter('query', '%' . $query . '%');
        }

        if ($id) {
            $qb->andWhere('c.id = :id')
                ->setParameter('id', $id);
        }

        if ($depart) {
            $qb->andWhere('c.depart = :depart')
                ->setParameter('depart', $depart);
        }

        if ($destination) {
            $qb->andWhere('c.destination = :destination')
                ->setParameter('destination', $destination);
        }

        if ($nmbr_place) {
            $qb->andWhere('c.nmbr_place = :nmbr_place')
                ->setParameter('nmbr_place', $nmbr_place);
        }

        return $qb->getQuery()->getResult();
    }

    public function findByCoVoiturage(string $query): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.depart LIKE :query ')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findById_client(string $id_client): array
{
    return $this->createQueryBuilder('c')
        ->andWhere('c.id_client = :id_client')
        ->setParameter('id_client', $id_client)
        ->orderBy('c.id', 'ASC')
        ->getQuery()
        ->getResult();
}

    public function findAllSorted(): array
    {
        $queryBuilder = $this->createQueryBuilder('cl')
            ->orderBy('cl.depart', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }

    public function findAvailable(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.nmbr_place > 0')
            ->getQuery()
            ->getResult();
    }




    //    /**
    //     * @return CoVoiturage[] Returns an array of CoVoiturage objects
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

    //    public function findOneBySomeField($value): ?CoVoiturage
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function email()
    {

        try {
            $email = (new Email())
                ->from('symfonycopte822@gmail.com')
                ->to('aymen.rahali@esprit.tn')
                ->subject('Car Pool Participation Confirmation')
                ->text('test');

            $transport = new GmailSmtpTransport('symfonycopte822@gmail.com', 'cdwgdrevbdoupxhn');
            $mailer = new Mailer($transport);
            $mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            // Handle any errors that occur during email sending
            $this->addFlash('error', 'An error occurred while sending the email');
        }
    }

    public function email_rec()
    {

        try {
            $email = (new Email())
                ->from('symfonycopte822@gmail.com')
                ->to('mohamedtaher.guerfala@esprit.tn')
                ->subject('Reclamation')
                ->text('Votre réclamation est reçu avec succés');

            $transport = new GmailSmtpTransport('symfonycopte822@gmail.com', 'cdwgdrevbdoupxhn');
            $mailer = new Mailer($transport);
            $mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            // Handle any errors that occur during email sending
            $this->addFlash('error', 'An error occurred while sending the email');
        }
    }
    public function send_msg(String $num): void
    {

        $accountSid = 'ACb8ac250d94d237ea91634b8def26f57d';
        $authToken = 'e0cbfc8640120b578d622e411f0f7821';
        $client = new Client($accountSid, $authToken);
        $message = $client->messages->create(
            $num, // recipient's phone number
            array(
                'from' => '+15673132411', // your Twilio phone number
                'body' => 'Participation added !'
            )
        );
    }

    public function sms(String $num): void
    {

        $accountSid = 'ACb8ac250d94d237ea91634b8def26f57d';
        $authToken = 'e0cbfc8640120b578d622e411f0f7821';
        $client = new Client($accountSid, $authToken);
        $message = $client->messages->create(
            $num, // recipient's phone number
            array(
                'from' => '+15673132411', // your Twilio phone number
                'body' => 'Reclamation reçu !'
            )
        );
    }
}
