<?php

namespace App\Controller;

use App\Entity\Cartefidelite;
use App\Form\CartefideliteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cartefidelite')]
class CartefideliteController extends AbstractController
{
    #[Route('/', name: 'app_cartefidelite_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $cartefidelites = $entityManager
            ->getRepository(Cartefidelite::class)
            ->findAll();

        return $this->render('cartefidelite/index.html.twig', [
            'cartefidelites' => $cartefidelites,
        ]);
    }

    

    #[Route('/{idCf}', name: 'app_cartefidelite_show', methods: ['GET'])]
public function show(Cartefidelite $cartefidelite): Response
{
    return $this->render('cartefidelite/show.html.twig', [
        'cartefidelite' => $cartefidelite,
    ]);
}


#[Route('/{idCf}/edit', name: 'app_cartefidelite_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Cartefidelite $cartefidelite, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(CartefideliteType::class, $cartefidelite);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();

        return $this->redirectToRoute('app_cartefidelite_show', ['idCf' => $cartefidelite->getIdCf()], Response::HTTP_SEE_OTHER);
    }

    return $this->renderForm('cartefidelite/edit.html.twig', [
        'cartefidelite' => $cartefidelite,
        'form' => $form,
    ]);
}

   
}