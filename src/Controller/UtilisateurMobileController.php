<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;



class UtilisateurMobileController extends AbstractController
{
   


    

    #[Route("/addUtilisateurJSON/new/{cin}/{nom}/{prenom}", name: "addUtilisateurJSON", methods: ['GET'])]
    public function addUtilisateurJSON(Request $req,   NormalizerInterface $Normalizer)
    {

        $em = $this->getDoctrine()->getManager();
        $user = new Utilisateur();
        $user->setCin($req->get('cin'));
        $user->setNom($req->get('nom'));
        $user->setPrenom($req->get('prenom'));
        $em->persist($user);
        $em->flush();

        $jsonContent = $Normalizer->normalize($user, 'json', ['groups' => 'utilisateurs']);
        return new Response(json_encode($jsonContent));
    }


    #[Route("/user", name: "id_user", methods: ['GET'])]
    public function getById(int $id): JsonResponse
    {
        $utilisateur = $this->getDoctrine()
            ->getRepository(Utilisateur::class)
            ->find($id);

        if (!$utilisateur) {
            return $this->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $data = [
            'id' => $utilisateur->getIdUser(),
            'nom' => $utilisateur->getNom(),
            'prenom' => $utilisateur->getPrenom(),
            // Ajoutez les autres propriétés que vous voulez inclure dans la réponse JSON
        ];

        return $this->json($data);
    }

    
}


