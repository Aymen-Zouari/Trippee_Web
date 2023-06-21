<?php

namespace App\Controller;
use App\Entity\Coupon;

use App\Repository\CouponRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Doctrine\ORM\EntityManagerInterface;

class CouponMobileController extends AbstractController{

  
    /*
    #[Route("/Allcoupons", name: "list")]
    //* Dans cette fonction, nous utilisons les services NormlizeInterface et StudentRepository, 
    //* avec la méthode d'injection de dépendances.
    public function getStudents(couponRepository $repo, NormalizerInterface $normalizer )
    {
        $coupon = $repo->findAll();
        //* Nous utilisons la fonction normalize qui transforme le tableau d'objets 
        //* students en  tableau associatif simple.
         $couponNormalises = $normalizer->normalize($coupon, 'json', ['groups' => "coupon"]);

        // //* Nous utilisons la fonction json_encode pour transformer un tableau associatif en format JSON
         $json = json_encode($couponNormalises);

       // $json = $serializer->serialize($coupon, 'json', ['groups' => "coupon"]);

        //* Nous renvoyons une réponse Http qui prend en paramètre un tableau en format JSON
        return new Response($json);
    }*/

    #[Route("/Allcoupons", name: "listCoup")]

    public function getStudents(couponRepository $repo, NormalizerInterface $normalizer )
    {
        $coupon = $repo->findAll();
       
$responseArray = array();
        foreach ($coupon as $user) {
            $responseArray[] = array(
                'id' => $user->getId(),
                'date_debut' => $user->getDateDebut()->format('Y-m-d'),
                'date_experatio' => $user->getDateExperatio()->format('Y-m-d'),
                'taux' => $user->getTaux(),
                'code_coupon' => $user->getCodeCoupon(),
                'nbr_utilisation' => $user->getNbrUtilisation(),
                'type' => $user->getType(),
            );
        }


         $couponNormalises = $normalizer->normalize($responseArray, 'json', ['groups' => "coupon"]);

       
         $json = json_encode($couponNormalises);


        return new Response($json);
    }
    #[Route("/coupon1/{id}", name: "coupon")]
    public function couponId($id, NormalizerInterface $normalizer, couponRepository $repo)
    {
        $coupon = $repo->find($id);
        $couponNormalises = $normalizer->normalize($coupon, 'json', ['groups' => "coupon"]);
        return new Response(json_encode($couponNormalises));
    }
    #[Route("addcouponJson/new", name: "add")]
    public function addcouponJSON(Request $request, EntityManagerInterface $entityManager, NormalizerInterface $normalizer)
    {
        // Create a new instance of the Coupon entity
        $coupon = new Coupon();
    
        // Set the properties of the coupon entity based on the request parameters
        $coupon->setDateDebut(new \DateTime($request->get('date_debut')));
        $coupon->setDateExperatio(new \DateTime($request->get('date_experatio')));
        $coupon->setTaux($request->get('taux'));
        $coupon->setCodeCoupon($request->get('code_coupon'));
        $coupon->setNbrUtilisation($request->get('nbr_utilisation'));
        $coupon->setType($request->get('type'));
    
        // Persist the coupon entity
        $entityManager->persist($coupon);
        $entityManager->flush();
    
        // Normalize the coupon entity to JSON
        $jsonContent = $normalizer->normalize($coupon, 'json', ['groups' => 'coupon']);
    
        // Return the JSON response
        return new Response(json_encode($jsonContent));
    }
    #[Route("updatecouponJSON/{id}", name: "updatecouponJSON")]
    public function updatecouponJSON(Request $request, $id, NormalizerInterface $normalizer)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $coupon = $entityManager->getRepository(Coupon::class)->find($id);
    
        if (!$coupon) {
            throw $this->createNotFoundException('Coupon not found');
        }
    
        // Update the coupon properties
        $coupon->setDateDebut(new \DateTime($request->get('date_debut')));
        $coupon->setDateExperatio(new \DateTime($request->get('date_expiratio')));
        $coupon->setTaux($request->get('taux'));
        $coupon->setCodeCoupon($request->get('code_coupon'));
        $coupon->setNbrUtilisation($request->get('nbr_utilisation'));
        $coupon->setType($request->get('type'));
    
        $entityManager->flush();
    
        // Normalize the updated coupon
        $jsonContent = $normalizer->normalize($coupon, 'json', ['groups' => 'coupon']);
    
        return new Response("Coupon updated successfully: " . json_encode($jsonContent));
    }
    #[Route("/deletecouponJSON/{id}", name: "deletecouponJSON")]
    public function deletecouponJSON(Request $req, $id, NormalizerInterface $Normalizer)
    {

        $em = $this->getDoctrine()->getManager();
        $coupon = $em->getRepository(coupon::class)->find($id);
        $em->remove($coupon);
        $em->flush();
        $jsonContent = $Normalizer->normalize($coupon, 'json', ['groups' => 'coupon']);
        return new Response("Student deleted successfully " . json_encode($jsonContent));
    }
}