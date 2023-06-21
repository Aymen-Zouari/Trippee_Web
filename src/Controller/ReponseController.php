<?php

namespace App\Controller;

use App\Entity\Reponse;
use App\Form\ReponseType;
use App\Repository\ReponseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Reclamation;
use App\Form\Reclamation1Type;
use App\Repository\ReclamationRepository;

use App\Entity\Client;
use App\Repository\ClientRepository;

use App\Entity\Role;
use App\Repository\RoleRepository;

use App\Entity\Locateur;
use App\Repository\LocateurRepository;

use App\Entity\Chauffeur;
use App\Repository\ChauffeurRepository;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;

#[Route('/reponse')]
class ReponseController extends AbstractController
{
    #[Route('/', name: 'app_reponse_index', methods: ['GET'])]
    public function index(ReponseRepository $reponseRepository): Response
    {
        return $this->render('reponse/index.html.twig', [
            'reponses' => $reponseRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_reponse_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ReponseRepository $reponseRepository): Response
    {
        $reponse = new Reponse();
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reponseRepository->save($reponse, true);

            return $this->redirectToRoute('app_reponse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('reponse/new.html.twig', [
            'reponse' => $reponse,
            'form' => $form,
        ]);
    }


//client reponses
    #[Route('/client/{id}/{id_client}', name: 'app_reponse_front', methods: ['GET'])]
    public function front(int $id, ReponseRepository $reponseRepository, ReclamationRepository $reclamationRepository, int $id_client): Response
    {
        $userRepository = $this->getDoctrine()->getRepository(Client::class);
        $client = $userRepository->find($id_client);
        
        $reclamation = $reclamationRepository->find($id);
        $reponses = $reclamation->getReponses();

    return $this->render('reponse/showAll.html.twig', [
        'id_client' => $id_client,
        'client' => $client,
        'reclamation' => $reclamation,
        'reponses' => $reponses,
    ]);
    }

//locateur reponses
#[Route('/loc/{id}/{id_loc}', name: 'app_reponse_frontloc', methods: ['GET'])]
public function frontloc(int $id, ReponseRepository $reponseRepository, ReclamationRepository $reclamationRepository, int $id_loc): Response
{
    $userRepository = $this->getDoctrine()->getRepository(Locateur::class);
    $locateur = $userRepository->find($id_loc);
    
    $reclamation = $reclamationRepository->find($id);
    $reponses = $reclamation->getReponses();

return $this->render('reponse/showAllLoc.html.twig', [
    'id_loc' => $id_loc,
    'locateur' => $locateur,
    'reclamation' => $reclamation,
    'reponses' => $reponses,
]);
}



//chauffeur reponses
#[Route('/chauffeur/{id}/{id_ch}', name: 'app_reponse_frontCh', methods: ['GET'])]
public function frontch(int $id, ReponseRepository $reponseRepository, ReclamationRepository $reclamationRepository, int $id_ch): Response
{
    $userRepository = $this->getDoctrine()->getRepository(Chauffeur::class);
    $chauffeur = $userRepository->find($id_ch);
    
    $reclamation = $reclamationRepository->find($id);
    $reponses = $reclamation->getReponses();

return $this->render('reponse/showAllCh.html.twig', [
    'id_ch' => $id_ch,
    'chauffeur' => $chauffeur,
    'reclamation' => $reclamation,
    'reponses' => $reponses,
]);
}



    #[Route('/rep/{id}', name: 'app_reponse_show', methods: ['GET'])]
    public function show(Reponse $reponse): Response
    {
        return $this->render('reponse/show.html.twig', [
            'reponse' => $reponse,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reponse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reponse $reponse, ReponseRepository $reponseRepository): Response
    {
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reponseRepository->save($reponse, true);

            return $this->redirectToRoute('app_reponse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('reponse/edit.html.twig', [
            'reponse' => $reponse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reponse_delete', methods: ['POST'])]
    public function delete(Request $request, Reponse $reponse, ReponseRepository $reponseRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reponse->getId(), $request->request->get('_token'))) {
            $reponseRepository->remove($reponse, true);
        }

        return $this->redirectToRoute('app_reponse_index', [], Response::HTTP_SEE_OTHER);
    }






}
