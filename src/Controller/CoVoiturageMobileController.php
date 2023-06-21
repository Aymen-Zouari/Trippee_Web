<?php

namespace App\Controller;

use App\Entity\CoVoiturage;
use App\Entity\Participation;
use App\Repository\CoVoiturageRepository;
use App\Repository\ChauffeurRepository;
use App\Repository\ParticipationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Response;


class CoVoiturageMobileController extends AbstractController
{
  



    #[Route("/covmobile/list", name: "listcov")]
    public function getCov(CoVoiturageRepository $repo, SerializerInterface $serializer)
    {
        $covs = $repo->findAll();
        $json = $serializer->serialize($covs, 'json', ['groups' => "covs"]);
        return new Response($json);
    }


    /*
    //show
    #[Route("/co/voiturage/mobile/show/{id}", name: "co_voiturage")]
    public function VoitureId($id, NormalizerInterface $normalizer, CoVoiturageRepository $repo)
    {
        $voiture = $repo->find($id);
        $voitureNormalises = $normalizer->normalize($voiture, 'json', ['groups' => "Co_voiturages"]);
        return new Response(json_encode($voitureNormalises));
    }
*/

    //ajouter
    #[Route('/addCo_voiturageJSON/new', name: 'addCo_voiturageJSON')]
    public function addCoVoiturageJSON(ManagerRegistry $doctrine, Request $req, NormalizerInterface $normalizer, CoVoiturageRepository $repo,ChauffeurRepository $ChauffeurRepository)
    {
        $em = $doctrine->getManager();
        $co_voiturage = new CoVoiturage();
        $co_voiturage->setDepart($req->get('depart'));
        $co_voiturage->setDestination($req->get('destination'));
        $co_voiturage->setNmbrPlace($req->get('nmbr_place'));
        $co_voiturage->setCovImg($req->get('img_cov'));
        $chauffeur = $ChauffeurRepository->find($req->get('id_ch'));
        $co_voiturage->setIdCh($chauffeur);

        $em->persist($co_voiturage);
        $em->flush();
        $repo->email();
        $repo->send_msg('+21658604483');
        $jsonContent = $normalizer->normalize($co_voiturage, 'json', ['groups' => 'co_voiturages']);
        return new Response(json_encode($jsonContent));
    }

    #[Route("/deleteCovJSON/{id}", name: "deleteCovJSON")]
    public function deleteVoitureJSON(ManagerRegistry $doctrine, Request $req, $id, NormalizerInterface $Normalizer)
    {

        $em = $doctrine->getManager();
        $cov = $em->getRepository(CoVoiturage::class)->find($id);
        $em->remove($cov);
        $em->flush();
        $jsonContent = $Normalizer->normalize($cov, 'json', ['groups' => 'covs']);
        return new Response("voiture deleted successfully " . json_encode($jsonContent));
    }

        //update
        #[Route("/updateCovoiturageJSON/{id}", name: "updateCovoiturageJSON")]
        public function updateVoitureJSON(ManagerRegistry $doctrine, Request $req, $id, NormalizerInterface $Normalizer)
        {
    
            $em = $doctrine->getManager();
            $cov = $em->getRepository(CoVoiturage::class)->find($id);
            $cov->setDepart($req->get('depart'));
            $cov->setDestination($req->get('destination'));
            $cov->setNmbrPlace($req->get('nmbr_place'));
            $cov->setCovImg($req->get('img_cov'));
           
            $em->flush();
    
            $jsonContent = $Normalizer->normalize($cov, 'json', ['groups' => 'covs']);
            return new Response("cov updated successfully " . json_encode($jsonContent));
        }
}
