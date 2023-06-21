<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use App\Entity\Role;
use App\Entity\Utilisateur;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;



class RoleMobileController extends AbstractController
{
    /*#[Route("utilisateur/mobile/role/mobile/addRoleJSON/new/{id_user}/{libelle}", name: "addRoleJSON", methods: ['POST','GET'])]
    public function addRoleJSON(Request $req,   NormalizerInterface $Normalizer)
    {

        $em = $this->getDoctrine()->getManager();
        $user = new Role();   
        $user->setLibelle($req->get('libelle'));
        $user->setIdUser($req->get('id_user'));
        $em->persist($user);
        $em->flush();

        $jsonContent = $Normalizer->normalize($user, 'json', ['groups' => 'roles']);
        return new Response(json_encode($jsonContent));
    }*/
    #[Route("/role/addRoleJSON/new/{id_user}/{libelle}", name: "addRoleJSON", methods: ['POST','GET'])]
public function addRoleJSON(Request $request, NormalizerInterface $normalizer, int $id_user, ManagerRegistry $registry)
{
    $em = $registry->getManager();
    $user = new Role();   
    $userRepository = $em->getRepository(Utilisateur::class);
    $utilisateur = $userRepository->find($id_user);
    if (!$utilisateur) {
        throw $this->createNotFoundException('L\'utilisateur avec l\'id '.$id_user.' n\'a pas été trouvé.');
    }
    $user->setIdUser($utilisateur);
    $user->setLibelle($request->get('libelle'));
    $em->persist($user);
    $em->flush();

    $jsonContent = $normalizer->normalize($user, 'json', ['groups' => 'roles']);
    return new JsonResponse($jsonContent);
}

#[Route("/role/{id_role}", name: "id_role", methods: ['GET'])]
public function getById(int $id_role,Request $request, NormalizerInterface $normalizer): JsonResponse
{
    $utilisateur = $this->getDoctrine()
        ->getRepository(Role::class)
        ->find($id_role);
        dd($utilisateur);

    if (!$utilisateur) {
        return $this->json(['message' => 'Utilisateur non trouvé'], 404);
    }
    $jsonContent = $normalizer->normalize($utilisateur, 'json', ['groups' => 'roles']);
    return new JsonResponse($jsonContent);
}

    

}
