<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Highscores;
use App\Entity\Client;

class UnityGameController extends AbstractController
{
    #[Route('/unity-game/{id_client}/', name: 'unity_game')]
    public function index(int $id_client,EntityManagerInterface $entityManager): Response
    {
        $userRepository = $this->getDoctrine()->getRepository(Client::class);
        $client = $userRepository->find($id_client);
        $entityManager = $this->getDoctrine()->getManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('h')
    ->from('App\Entity\Highscores', 'h')
    ->orderBy('h.ids', 'DESC')
    ->setMaxResults(1);
$lastHighscore = $queryBuilder->getQuery()->getOneOrNullResult();

if ($lastHighscore !== null) {
    $randomNumber = rand(1, 1000); // Generate a random number between 1 and 1000
    $newScore = $lastHighscore->getScore() + $randomNumber;
    $lastHighscore->setScore($newScore);
    $entityManager->persist($lastHighscore);
    $entityManager->flush();
}

        return $this->render('unity_game/index.html.twig',[
            'client'=>$client,
            'id_client'=>$id_client,
        ]);
    }
}
