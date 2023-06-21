<?php

namespace App\Controller;

use App\Entity\Voiture;
use App\Repository\VoitureRepository;
use App\Repository\LocateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\Persistence\ManagerRegistry;
class VoituremobileController extends AbstractController

{
    #[Route('/voituremobile', name: 'app_voituremobile')]
    public function index(): Response
    {
        return $this->render('voituremobile/index.html.twig', [
            'controller_name' => 'VoituremobileController',
        ]);
    }
//affichage

    #[Route("/voituremobile/list", name: "list")]
    public function getVoiture(VoitureRepository $repo, SerializerInterface $serializer)
    {
        $voitures = $repo->findAll();
        $json = $serializer->serialize($voitures, 'json', ['groups' => "voitures"]);
        return new Response($json);
    }

//show
        #[Route("/voituremobile/{id}", name: "voiture")]
        public function VoitureId($id, NormalizerInterface $normalizer, VoitureRepository $repo)
    {
        
        $voiture = $repo->find($id);
        $voitureNormalises = $normalizer->normalize($voiture, 'json', ['groups' => "voitures"]);
        return new Response(json_encode($voitureNormalises));
    }
    //ajouter
    #[Route("addVoitureJSON/new", name: "addVoitureJSON")]
    public function addVoitureJSON(ManagerRegistry $doctrine, Request $req,NormalizerInterface $Normalizer,LocateurRepository $LocateurRepository)
    {
       
      
        $email = (new Email())
            ->from('symfonycopte822@gmail.com')
            ->to('khmiri.iheb@esprit.tn')
            ->subject('Car Rental Reservation Confirmation')
            ->text('Dear user,

Thank you for choosing to rent  from Trippie. We are pleased to confirm your reservation for the 

As a reminder, please bring a valid driver\'s license and a credit card in your name when you come to pick up the car. If you have any additional questions or special requests, please do not hesitate to contact us.

We look forward to serving you and hope you have a safe and enjoyable rental experience with us.

Best regards,
Trippie');

        $transport = new GmailSmtpTransport('symfonycopte822@gmail.com', 'cdwgdrevbdoupxhn');
        $mailer = new Mailer($transport);
        $mailer->send($email);
        $em = $doctrine->getManager();
        $voiture = new Voiture();
        $voiture->setMatricule($req->get('matricule'));
        $voiture->setMarque($req->get('marque'));
        $voiture->setPuissance($req->get('puissance'));
        $voiture->setPrixJours($req->get('prix_jours'));
        $voiture->setPicture($req->get('picture'));
        $voiture->setEnergie($req->get('energie'));
        $voiture->setEtat($req->get('etat'));
       // $voiture->setIdLoc($req->get('id_loc'));
        $locateur = $LocateurRepository->find($req->get('id_loc'));
        $voiture->setIdLoc($locateur);
        $em->persist($voiture);
        $em->flush();

        $jsonContent = $Normalizer->normalize($voiture, 'json', ['groups' => 'voitures']);

        return new Response(json_encode($jsonContent));
    }
    //update
    #[Route("updateVoitureJSON/{id}", name: "updateVoitureJSON")]
    public function updateVoitureJSON(ManagerRegistry $doctrine, Request $req, $id, NormalizerInterface $Normalizer)
    {

        $em = $doctrine->getManager();
        $voiture = $em->getRepository(Voiture::class)->find($id);
        $voiture->setMatricule($req->get('matricule'));
        $voiture->setMarque($req->get('marque'));
        $voiture->setPuissance($req->get('puissance'));
        $voiture->setPrixJours($req->get('prix_jours'));
        $voiture->setPicture($req->get('picture'));
        $voiture->setEnergie($req->get('energie'));
        $voiture->setEtat($req->get('etat'));
        $voiture->setIdLocateur($req->get('id_locateur'));
        $em->flush();

        $jsonContent = $Normalizer->normalize($voiture, 'json', ['groups' => 'voitures']);
        return new Response("voiture updated successfully " . json_encode($jsonContent));
    }
    #[Route("deleteVoitureJSON/{id}", name: "deleteVoitureJSON")]
    public function deleteVoitureJSON(ManagerRegistry $doctrine, Request $req, $id, NormalizerInterface $Normalizer)
    {

        $em = $doctrine->getManager();
        $voiture = $em->getRepository(Voiture::class)->find($id);

        $em->remove($voiture);
        $em->flush();

        $jsonContent = $Normalizer->normalize($voiture, 'json', ['groups' => 'voitures']);
        return new Response("voiture deleted successfully " . json_encode($jsonContent));
    }
}