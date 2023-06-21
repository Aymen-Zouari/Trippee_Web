<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use App\Entity\Client;
use App\Entity\Utilisateur;
use App\Entity\Role;
use App\Entity\Locateur;
use App\Entity\Chauffeur;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;

class LoginMobileController extends AbstractController
{
    #[Route('/client/login/{email}/{password}', name: 'loginCl', methods: ['GET','POST'])]
    public function loginJSON(Request $request, NormalizerInterface $normalizer, ManagerRegistry $registry,String $email,String $password)
{
    $em = $registry->getManager();
    // retrieve the data from the request
   
    $cliRepository = $em->getRepository(Client::class);
    $client = $cliRepository->findOneBy(['email'=>$email]);
    $role=$client->getIdRole();
    $id_role= $role->getIdRole();
    $user=$role->getIdUser();
    $id_user =$user->getIdUser();
    $userRepository = $em->getRepository(Utilisateur::class);
    $user = $userRepository->find($id_user);
   // dd($user);
  
    
    $client->getEmail($email);
    $client->getPassword($password);
    
  
   

    // perform your authentication logic here
    // ...

    // assuming you have an $user variable containing the user data
    $jsonContent = $normalizer->normalize($client,'json', ['groups' => 'clients']);
        return new Response(json_encode($jsonContent));
}

#[Route('/chauffeur/login/{email}/{password}', name: 'login2', methods: ['GET','POST'])]
public function loginJSONch(Request $request, NormalizerInterface $normalizer, ManagerRegistry $registry,String $email,String $password)
{
$em = $registry->getManager();
// retrieve the data from the request

$cliRepository = $em->getRepository(Chauffeur::class);
$client = $cliRepository->findOneBy(['email'=>$email]);

// dd($user);


$client->getEmail($email);
$client->getPassword($password);




// perform your authentication logic here
// ...

// assuming you have an $user variable containing the user data
$jsonContent = $normalizer->normalize($client,'json', ['groups' => 'chauffeurs']);
    return new Response(json_encode($jsonContent));
}

#[Route('/locateur/login/{email}/{password}', name: 'login3', methods: ['GET','POST'])]
public function loginJSONloc(Request $request, NormalizerInterface $normalizer, ManagerRegistry $registry,String $email,String $password)
{
$em = $registry->getManager();
// retrieve the data from the request

$cliRepository = $em->getRepository(Locateur::class);
$client = $cliRepository->findOneBy(['email'=>$email]);



$client->getEmail($email);
$client->getPassword($password);




// perform your authentication logic here
// ...

// assuming you have an $user variable containing the user data
$jsonContent = $normalizer->normalize($client,'json', ['groups' => 'locateurs']);
    return new Response(json_encode($jsonContent));
}
}


