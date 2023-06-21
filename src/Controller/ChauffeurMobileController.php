<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use App\Entity\Role;
use App\Service\TwilioService;
use App\Entity\Chauffeur;
use App\Repository\ChauffeurRepository;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

class ChauffeurMobileController extends AbstractController
{
    #[Route("/chauffeur/addChauffeurJSON/new/{id_role}/{img}/{gsm}/{num_permis}/{email}/{password}", name: "addChauffeurJSON", methods: ['POST','GET'])]
    public function addChauffeurJSON(Request $request, NormalizerInterface $normalizer, int $id_role, ManagerRegistry $registry)
    {
        $em = $registry->getManager();
        $user = new Chauffeur();   
        $userRepository = $em->getRepository(Role::class);
        $utilisateur = $userRepository->find($id_role);
        if (!$utilisateur) {
            throw $this->createNotFoundException('L\'utilisateur avec l\'id '.$id_role.' n\'a pas été trouvé.');
        }
        $user->setIdRole($utilisateur);
        $user->setImg($request->get('img'));
        $user->setGsm($request->get('gsm'));
        $user->setNumPermis($request->get('num_permis'));
        $user->setEmail($request->get('email'));
        $user->setPassword($request->get('password'));
        $mail = $user->getEmail();
        $email = (new Email())
        ->from('aymen58zouari@gmail.com')
        ->to($mail)
        ->subject('Registration Confirmation')
        ->text('Dear user,
    
        We are pleased to inform you that your registration with Tripee has been completed successfully.
    
        Your account has been activated and you can now log in to our website using the username and password you provided during registration. You will have access to all the features and services that we offer to our registered users.
        
        Thank you for choosing to register with us. We look forward to providing you with the best experience possible.
        
        If you have any questions or concerns, please do not hesitate to contact our customer service team at +216 58 604 483.
    
    Best regards,
    Tripee');
    
    $transport = new GmailSmtpTransport('aymen58zouari@gmail.com', 'qupoztnbdlklxbhj');
    $mailer = new Mailer($transport);
    $mailer->send($email);
        $em->persist($user);
        $em->flush();
    
        $jsonContent = $normalizer->normalize($user, 'json', ['groups' => 'chauffeurs']);
        return new JsonResponse($jsonContent);
    }


    #[Route("/chauffeur/deleteChJSON/{id_ch}", name: "deleteChJSON")]
    public function deleteChJSON(Request $req,$id_ch, NormalizerInterface $NormalizerInterface)
    {

        $em = $this->getDoctrine()->getManager();
        $chauffeur=$em->getRepository(Chauffeur::class)->find($id_ch);

        $em->remove($chauffeur);
        $em->flush();
        $jsonContent = $NormalizerInterface->normalize($chauffeur, 'json', ['groups' => 'chauffeurs']);
        return new JsonResponse($jsonContent);
    }



    #[Route("/chauffeur/editChJson/{id}/{gsm}/{num_permis}/{email}/{password}", name: "edit", methods: ['POST','GET'])]
    public function editCh(Request $request,int $id, int $gsm, string $num_permis, string $password, string $email, NormalizerInterface $NormalizerInterface): JsonResponse
{
    // Préparation de la requête vers l'API
   

    $em = $this->getDoctrine()->getManager();
    $chauffeur=$em->getRepository(Chauffeur::class)->find($id);

    $chauffeur->setGsm($request->get('gsm'));
    $chauffeur->setNumPermis($request->get('num_permis'));
    $chauffeur->setEmail($request->get('email'));
    $chauffeur->setPassword($request->get('password'));


    $em->persist($chauffeur);
    $em->flush();

    // Envoi de la requête
    $jsonContent = $NormalizerInterface->normalize($chauffeur, 'json', ['groups' => 'chauffeurs']);
    return new JsonResponse($jsonContent);
}

#[Route('/chauffeur/forget/{gsm}', name: 'forgetCh', methods: ['POST','GET'])]
public function forgetPassword(Request $request,UserPasswordHasherInterface $userPasswordHasher, TokenGeneratorInterface $tokenGenerator,int $gsm,NormalizerInterface $NormalizerInterface): JsonResponse
{

   
    $em = $this->getDoctrine()->getManager();
    $user = $em->getRepository(Chauffeur::class)->findOneBy(['gsm' => $gsm]);
   


    $accountSid ='ACb8ac250d94d237ea91634b8def26f57d';
    $authToken = '54d4d6dfa4a3e8c998d386857a985a8e';
    $twilioService = new TwilioService($accountSid, $authToken);
    

    $to = '+216' . $user->getGsm(); // recipient's phone number
    $from = '+15673132411'; // your Twilio phone number
    $body = 'This is your new password: ' . $code;

    $twilioService->sendSms($from, $to, $body);
    $code = bin2hex(random_bytes(3));
    $user->setPassword(
        
            $code
        )
    ;
    $em->persist($user);
    $em->flush();
  
   
     
                $jsonContent = $NormalizerInterface->normalize($user, 'json', ['groups' => 'chauffeurs']);
                return new JsonResponse($jsonContent);
}


}
