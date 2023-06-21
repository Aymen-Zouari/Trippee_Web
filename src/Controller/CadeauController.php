<?php

namespace App\Controller;

use App\Entity\Cadeau;
use App\Form\Cadeau1Type;
use App\Repository\CadeauRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cadeau')]
class CadeauController extends AbstractController
{
    #[Route('/', name: 'app_cadeau_index', methods: ['GET'])]
    public function index(Request $request,CadeauRepository $cadeauRepository): Response
    {
        $searchQuery = $request->query->get('q');
        $sort = $request->query->get('sort');
        $order = $request->query->get('order', 'asc');
        if ($searchQuery) {
            $cadeaus = $cadeauRepository->findBycadeau($searchQuery);
        } elseif ($sort === 'valeur' && $order === 'asc') {
            $cadeaus = $cadeauRepository->findAllSortedByvaleur('ASC');
        } elseif ($sort === 'valeur' && $order === 'desc') {
            $cadeaus = $cadeauRepository->findAllSortedByvaleur('DESC');
        } else {
            $cadeaus = $cadeauRepository->findAll();
        }
        
        return $this->render('cadeau/index.html.twig', [
            'cadeaus' => $cadeaus,
            'sort' => $sort,
            'order' => $order,
        ]);
    }

    #[Route('/new', name: 'app_cadeau_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CadeauRepository $cadeauRepository): Response
    {
        $cadeau = new Cadeau();
        $form = $this->createForm(Cadeau1Type::class, $cadeau);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cadeauRepository->save($cadeau, true);

            return $this->redirectToRoute('app_cadeau_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cadeau/new.html.twig', [
            'cadeau' => $cadeau,
            'form' => $form,
        ]);
    }

    #[Route('/{idcadeau}', name: 'app_cadeau_show', methods: ['GET'])]
    public function show(Cadeau $cadeau): Response
    {
        return $this->render('cadeau/show.html.twig', [
            'cadeau' => $cadeau,
        ]);
    }

    #[Route('/{idcadeau}/edit', name: 'app_cadeau_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cadeau $cadeau, CadeauRepository $cadeauRepository): Response
    {
        $form = $this->createForm(Cadeau1Type::class, $cadeau);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cadeauRepository->save($cadeau, true);

            return $this->redirectToRoute('app_cadeau_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cadeau/edit.html.twig', [
            'cadeau' => $cadeau,
            'form' => $form,
        ]);
    }

    #[Route('/{idcadeau}', name: 'app_cadeau_delete', methods: ['POST'])]
    public function delete(Request $request, Cadeau $cadeau, CadeauRepository $cadeauRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cadeau->getIdcadeau(), $request->request->get('_token'))) {
            $cadeauRepository->remove($cadeau, true);
        }

        return $this->redirectToRoute('app_cadeau_index', [], Response::HTTP_SEE_OTHER);
    }
}
