<?php

namespace App\Controller;

use App\Entity\Reclamation;
use Doctrine\ORM\EntityRepository;
use App\Repository\ReclamationRepository;
use App\Repository\ClientRepository;
use App\Repository\CoVoiturageRepository;
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
class ReclamationMobileController extends AbstractController

{
    #[Route('/reclamationmobile', name: 'app_reclamationmobile')]
    public function index(): Response
    {
        return $this->render('reclamationmobile/index.html.twig', [
            'controller_name' => 'ReclamationMobileController',
        ]);
    }

    #[Route("addReclamationSON/new", name: "addReclamationjSON")]
    public function addReclamationJSON(ManagerRegistry $doctrine, Request $req,NormalizerInterface $Normalizer,ClientRepository $ClientRepository,CoVoiturageRepository $rec)
    {

        $em = $doctrine->getManager();
        $reclamation = new Reclamation();
        $reclamation->setType($req->get('type'));
        $reclamation->setCommentaire($req->get('commentaire'));
        $reclamation->setEtat($req->get('etat'));
  //      $reclamation->setIdUser($req->get('id_user'));
//        $reclamation->setDateCreation($req->get('date_creation'));
        $reclamation->setImage($req->get('image'));
        $client = $ClientRepository->find($req->get('id_client'));
        $role = $client->getIdRole();
        $reclamation->setIdUser($role->getIdRole());
        $rec->sms("+21658604483");
        $email = (new Email())
        ->from('symfonycopte822@gmail.com')
        ->to('mohamedtaher.guerfala@esprit.tn')
        ->subject('Reclamation Confirmation')
        ->text('        Votre réclamation est recu');

    $transport = new GmailSmtpTransport('symfonycopte822@gmail.com', 'cdwgdrevbdoupxhn');
    $mailer = new Mailer($transport);
    $mailer->send($email);
        $em->persist($reclamation);
        $em->flush();

        $jsonContent = $Normalizer->normalize($reclamation, 'json', ['groups' => 'reclamations']);
        return new Response(json_encode($jsonContent));
    }
    #[Route("updateReclamationJSON/{id}", name: "updateReclamationJSON")]
    public function updateReclamationJSON(ManagerRegistry $doctrine, Request $req, $id, NormalizerInterface $Normalizer)
    {

        $em = $doctrine->getManager();
        $reclamation = $em->getRepository(Reclamation::class)->find($id);
        $reclamation->setType($req->get('type'));
        $reclamation->setCommentaire($req->get('commentaire'));
        $reclamation->setEtat($req->get('etat'));
  //      $reclamation->setIdUser($req->get('id_user'));
//        $reclamation->setDateCreation($req->get('date_creation'));
        $reclamation->setImage($req->get('image'));
        $em->flush();

        $jsonContent = $Normalizer->normalize($reclamation, 'json', ['groups' => 'reclamations']);
        return new Response("reclamation updated successfully " . json_encode($jsonContent));
    }

    #[Route("/AllReclamations", name: "listreclamation")]
    public function getReclamations(ReclamationRepository $repo, SerializerInterface $serializer)
    {
        $reclamations = $repo->findAll();
        $json = $serializer->serialize($reclamations, 'json', ['groups' => "reclamations"]);
        return new Response($json);
    }
    #[Route("deleteReclamationJSON/{id}", name: "deleteReclamationJSON")]
    public function deleteReclamationJSON(ManagerRegistry $doctrine, Request $req, $id, NormalizerInterface $Normalizer)
    {

        $em = $doctrine->getManager();
        $reclamation = $em->getRepository(Reclamation::class)->find($id);
        $em->remove($reclamation);
        $em->flush();
        $jsonContent = $Normalizer->normalize($reclamation, 'json', ['groups' => 'reclamations']);
        return new Response("reclamation deleted successfully " . json_encode($jsonContent));
    }
}