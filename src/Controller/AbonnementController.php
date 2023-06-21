<?php

namespace App\Controller;

use App\Entity\Cartefidelite;
use App\Entity\Highscores;
use App\Entity\Client;


use App\Entity\Abonnement;
use App\Form\AbonnementType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use Symfony\Component\Mailer\MailerInterface;



#[Route('/abonnement')]
class AbonnementController extends AbstractController
{
    #[Route('/', name: 'app_abonnement_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $abonnements = $entityManager
            ->getRepository(Abonnement::class)
            ->findAll();

        return $this->render('abonnement/index.html.twig', [
            'abonnements' => $abonnements,
        ]);
    }
    #[Route('/', name: 'app_abonnement_indexClient', methods: ['GET'])]
    public function indexClient(EntityManagerInterface $entityManager): Response
    {
        $abonnements = $entityManager
            ->getRepository(Abonnement::class)
            ->findAll();

        return $this->render('indexClient.html.twig', [
            'abonnements' => $abonnements,
        ]);
    }

    #[Route('/new', name: 'app_abonnement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        
        $abonnement = new Abonnement();
           // Set the dateAchat and dateExpiration attributes
    $today = new \DateTime('today');
    $dateExpiration = clone $today;
    $dateExpiration->modify('+1 year');

    $abonnement->setDateachat($today);
    $abonnement->setDateexpiration($dateExpiration);

        $form = $this->createForm(AbonnementType::class, $abonnement);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {

            



            $entityManager->persist($abonnement);
            $entityManager->flush();
    
          

            // Create a new Cartefidelite for the new Abonnement
            $cartefidelite = new Cartefidelite();
            $cartefidelite->setAbonnement($abonnement);
    
            // Set the required properties for Cartefidelite object
            $cartefidelite->setPointmerci("0"); // You can set a default value or calculate it based on your business logic
            $cartefidelite->setDateexpiration($abonnement->getDateexpiration());
          
    
            // Persist and flush the Cartefidelite object
            $entityManager->persist($cartefidelite);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_abonnement_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->renderForm('abonnement/new.html.twig', [
            'abonnement' => $abonnement,
            'form' => $form,
        ]);
    }
    #[Route('/newC/{id_client}', name: 'app_abonnement_newC', methods: ['GET', 'POST'])]
public function newC(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer,int $id_client): Response
{ 
    $userRepository = $this->getDoctrine()->getRepository(Client::class);
    $client = $userRepository->find($id_client);
        $abonnement = new Abonnement();
         // Set the dateAchat and dateExpiration attributes
    $today = new \DateTime('today');
    $dateExpiration = clone $today;
    $dateExpiration->modify('+1 year');

    $abonnement->setDateachat($today);
    $abonnement->setDateexpiration($dateExpiration);

    $form = $this->createForm(AbonnementType::class, $abonnement);
    $form->handleRequest($request);
        $form = $this->createForm(AbonnementType::class, $abonnement);
        $form->handleRequest($request);
    
        $highscores = $entityManager
            ->getRepository(Highscores::class)
            ->createQueryBuilder('h')
            ->select('h')
            ->orderBy('h.ids', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    
        $success = false;
        $error = false;
    
        if ($form->isSubmitted() && $form->isValid()) {
            if ($abonnement->getPrix() < $highscores->getScore()) {
                
    
                
    
                // Create a new Cartefidelite for the new Abonnement
                $cartefidelite = new Cartefidelite();
                $cartefidelite->setAbonnement($abonnement);
    
                // Set the required properties for Cartefidelite object
                $cartefidelite->setPointmerci("0");
                $cartefidelite->setDateexpiration($abonnement->getDateexpiration());
    
                // Persist and flush the Cartefidelite object
                $entityManager->persist($cartefidelite);
                $entityManager->flush();
    
                // Subtract the price of the membership from the highest score
                $highscores->setScore($highscores->getScore() - $abonnement->getPrix());
                $entityManager->flush();
    

                 // Create an email message object
                 $email = (new Email())
                 ->from('symfonycopte822@gmail.com')
                 ->to('accessoriesnova9300@gmail.com')
                 ->subject('Your new membership')
                 ->html('
                 <div style="background-color: #f2f2f2; padding: 20px; font-family: Arial, sans-serif; font-size: 14px; line-height: 1.5;">
                     <img src="cid:membership_card" alt="Membership card" style="display: block; margin: 0 auto; max-width: 100%; height: auto;">
                     <h2 style="margin-top: 0;">Thank you for purchasing a new membership!</h2>
                     <p>Your membership details are as follows:</p>
                     <ul style="list-style-type: none; margin: 0; padding: 0;">
                         <li><strong>Type:</strong> ' . $abonnement->getType() . '</li>
                         <li><strong>Price:</strong> ' . $abonnement->getPrix() . '</li>
                         <li><strong>Start date:</strong> ' . $abonnement->getDateachat()->format('Y-m-d') . '</li>
                         <li><strong>Expiration date:</strong> ' . $abonnement->getDateexpiration()->format('Y-m-d') . '</li>
                     </ul>
                 </div>
             ');
             
             if ($abonnement->getType() === 'Gold') {
                 $email->embedFromPath('C:\Users\aymen\Desktop\Integration\public\uploads\Gold.png', 'membership_card');
             } else if ($abonnement->getType() === 'Bronze') {
                 $email->embedFromPath('C:\Users\aymen\Desktop\Integration\public\uploads\Bronze.png', 'membership_card');
             }
             else if ($abonnement->getType() === 'Platinum') {
                 $email->embedFromPath('C:\Users\aymen\Desktop\Integration\public\uploads\Plat.png', 'membership_card');
             }


    
    $transport = new GmailSmtpTransport('symfonycopte822@gmail.com', 'cdwgdrevbdoupxhn');
            $mailer = new Mailer($transport);
    $mailer->send($email);


    $success = true;
   


            } else {
                $error = true;
            }
        }
    
        return $this->renderForm('abonnement/newC.html.twig', [
            'client'=>$client,
            'id_client'=>$id_client,
            'abonnement' => $abonnement,
            'form' => $form,
            'highscores' => $highscores,
            'success' => $success,
            'error' => $error,
        ]);
    }
    

    #[Route('/{ida}', name: 'app_abonnement_show', methods: ['GET'])]
    public function show(Abonnement $abonnement): Response
    {
        return $this->render('abonnement/show.html.twig', [
            'abonnement' => $abonnement,
        ]);
    }

    #[Route('/{ida}/edit', name: 'app_abonnement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Abonnement $abonnement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AbonnementType::class, $abonnement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_abonnement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('abonnement/edit.html.twig', [
            'abonnement' => $abonnement,
            'form' => $form,
        ]);
    }

    #[Route('/{ida}', name: 'app_abonnement_delete', methods: ['POST'])]
public function delete(Request $request, Abonnement $abonnement, EntityManagerInterface $entityManager): Response
{
    if ($this->isCsrfTokenValid('delete'.$abonnement->getIda(), $request->request->get('_token'))) {

        // Find the related Cartefidelite entity
        $carteFidelite = $entityManager->getRepository(Cartefidelite::class)->findOneBy(['abonnement' => $abonnement]);

        // If a Cartefidelite entity is found, remove it
        if ($carteFidelite) {
            $entityManager->remove($carteFidelite);
        }

        // Remove the Abonnement entity
        $entityManager->remove($abonnement);
        $entityManager->flush();
    }

    return $this->redirectToRoute('app_abonnement_index', [], Response::HTTP_SEE_OTHER);
}

}
