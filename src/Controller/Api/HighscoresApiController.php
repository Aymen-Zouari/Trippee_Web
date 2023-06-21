<?php

namespace App\Controller\Api;

use App\Entity\Highscores;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/highscores')]
class HighscoresApiController extends AbstractController
{
    #[Route('', name: 'api_highscores_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $highscore = new Highscores();
        $highscore->setScore($data['score']);
      
        $entityManager->persist($highscore);
        $entityManager->flush();

        return $this->json(['message' => 'Highscore created!'], Response::HTTP_CREATED);
    }
}