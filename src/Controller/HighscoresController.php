<?php

namespace App\Controller;

use App\Entity\Highscores;
use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

//for displaying the high scores list sorted by order 
#[Route('/highscores')]
class HighscoresController extends AbstractController
{
    #[Route('/{id_client}', name: 'app_highscores_indexClient', methods: ['GET','POST'])]
    public function indexClient(EntityManagerInterface $entityManager, Request $request,int $id_client): Response
    {
        $order = $request->query->get('order', 'desc'); // Default sorting order is descending
        $userRepository = $this->getDoctrine()->getRepository(Client::class);
        $client = $userRepository->find($id_client);
        $highscores = $entityManager
            ->getRepository(Highscores::class)
            ->findBy(['id_client' => $id_client], ['score' => $order]);
        
        return $this->render('highscores/index.html.twig', [
            'client'=>$client,
            'id_client'=>$id_client,
            'highscores' => $highscores,
            'order' => $order,
        ]);
    }
//for showing a specific high score
    #[Route('/{idS}', name: 'app_highscores_show', methods: ['GET','POST'])]
    public function show(Highscores $highscore): Response
    {
        return $this->render('highscores/show.html.twig', [
            'highscore' => $highscore,
        ]);
    }

   




   

}
