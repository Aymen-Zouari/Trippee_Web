<?php

namespace App\Controller;

use App\Entity\Abonnement;
use App\Repository\abonnementRepository;



use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use Symfony\Component\Mailer\MailerInterface;

class AbonnementMobileController extends AbstractController

{
   /* #[Route('/Abonnementmobile', name: 'app_abonnementmobile')]
    public function index(): Response
    {
        return $this->render('abonnementmobile/index.html.twig', [
            'controller_name' => 'AbonnementmobileController',
        ]);
    }*/
//affichage

    #[Route("/abonnementmobile/list", name: "listnour")]
    public function getAbonnement(abonnementRepository $repo, SerializerInterface $serializer)
    {
        $abonnements = $repo->findAll();
        $json = $serializer->serialize($abonnements, 'json', ['groups' => "abonnements"]);
        return new Response($json);
    }









    //ajouter
    #[Route("addAbonnementJSON/new", name: "addAbonnementJSON")]
    public function addAbonnementJSON(ManagerRegistry $doctrine, Request $req,NormalizerInterface $Normalizer)
    {

        $em = $doctrine->getManager();
        $abonnement = new Abonnement();
        $abonnement->setType($req->get('type'));
        $abonnement->setPrix($req->get('prix'));
        
        
        $em->persist($abonnement);
        $em->flush();
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
            </ul>
        </div>
    ');
    
   /* if ($abonnement->getType() === 'Gold') {
        $email->embedFromPath('C:\xampp\htdocs\membership\resources\Gold.png', 'membership_card');
    } else if ($abonnement->getType() === 'Bronze') {
        $email->embedFromPath('C:\xampp\htdocs\membership\resources\Bronze.png', 'membership_card');
    }
    else if ($abonnement->getType() === 'Platinum') {
        $email->embedFromPath('C:\xampp\htdocs\membership\resources\Plat.png', 'membership_card');
    }*/



$transport = new GmailSmtpTransport('symfonycopte822@gmail.com', 'cdwgdrevbdoupxhn');
   $mailer = new Mailer($transport);
$mailer->send($email);

        $jsonContent = $Normalizer->normalize($abonnement, 'json', ['groups' => 'abonnements']);
        return new Response(json_encode($jsonContent));
        
    }
    //update
    #[Route("updateAbonnementJSON/{idA}", name: "updateAbonnementJSON")]
    public function updateAbonnementJSON(ManagerRegistry $doctrine, Request $req, $idA, NormalizerInterface $Normalizer)
    {

        $em = $doctrine->getManager();
        $abonnement = $em->getRepository(Abonnement::class)->find($idA);
        $abonnement->setType($req->get('type'));
        $abonnement->setPrix($req->get('prix'));
        $em->flush();

        $jsonContent = $Normalizer->normalize($abonnement, 'json', ['groups' => 'abonnements']);
        return new Response("membership updated successfully " . json_encode($jsonContent));
    }
    #[Route("deleteAbonnementJSON/{idA}", name: "deleteAbonnementJSON")]
    public function deleteAbonnementJSON(ManagerRegistry $doctrine, Request $req, $idA, NormalizerInterface $Normalizer)
    {

        $em = $doctrine->getManager();
        $abonnement = $em->getRepository(Abonnement::class)->find($idA);
       
        $em->remove($abonnement);
        $em->flush();
        $jsonContent = $Normalizer->normalize($abonnement, 'json', ['groups' => 'abonnements']);
        return new Response("membership deleted successfully " . json_encode($jsonContent));
    }
}