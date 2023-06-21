<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\Utilisateur;
use App\Form\RoleType;
use App\Repository\RoleRepository;
use App\Form\RoleResetType;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/role')]
class RoleController extends AbstractController
{
    #[Route('/', name: 'app_role_index', methods: ['GET'])]
    public function index(RoleRepository $roleRepository): Response
    {
        return $this->render('role/index.html.twig', [
            'roles' => $roleRepository->findAll(),
        ]);
    }

    #[Route('/new/{idUser<\d+>}', name: 'app_role_new', methods: ['GET', 'POST'])]
    public function new(Request $request, RoleRepository $roleRepository, UtilisateurRepository $userRepository, int $idUser): Response
    {
        $userRepository = $this->getDoctrine()->getRepository(Utilisateur::class);
        $user = $userRepository->find($idUser);
    
        if (!$user) {
            throw $this->createNotFoundException('User with id ' . $idUser . ' does not exist.');
        }
    
        $role = new Role();
        $role->setIdUser($user);
        $form = $this->createForm(RoleType::class, $role);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $roleRepository->save($role, true);
            $idRole = $role->getIdRole();
            $libelle = $form->get('libelle')->getData();
            if ($libelle === 'client') {
                return $this->redirectToRoute('app_client_new', ['idRole' => $idRole], Response::HTTP_SEE_OTHER);
            } else if ($libelle === 'locateur') {
                return $this->redirectToRoute('app_locateur_new', ['idRole' => $idRole], Response::HTTP_SEE_OTHER);
            }
            else if ($libelle === 'chauffeur'){
            return $this->redirectToRoute('app_chauffeur_new', ['idRole' => $idRole], Response::HTTP_SEE_OTHER);
        }
        }
    
        return $this->renderForm('role/new.html.twig', [
            'id_user' => $idUser,
            'role' => $role,
            'form' => $form,
        ]);
    }

    #[Route('/{id_role}', name: 'app_role_show', methods: ['GET'])]
    public function show(Role $role): Response
    {
        return $this->render('role/show.html.twig', [
            'role' => $role,
        ]);
    }

    #[Route('/{id_role}/edit', name: 'app_role_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Role $role, RoleRepository $roleRepository): Response
    {
        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $roleRepository->save($role, true);

            return $this->redirectToRoute('app_role_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('role/edit.html.twig', [
            'role' => $role,
            'form' => $form,
        ]);
    }

    #[Route('/{id_role}', name: 'app_role_delete', methods: ['POST'])]
    public function delete(Request $request, Role $role, RoleRepository $roleRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$role->getId_role(), $request->request->get('_token'))) {
            $roleRepository->remove($role, true);
        }

        return $this->redirectToRoute('app_role_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/login/role', name: 'app_role_reset', methods: ['POST','GET'])]
    public function choice(Request $request): Response
    {
        $form = $this->createForm(RoleResetType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
           
           
            $libelle = $form->get('role')->getData();
            if ($libelle === 'client') {
                return $this->redirectToRoute('app_forgot_password_request_client', [] ,Response::HTTP_SEE_OTHER);
            } else if ($libelle === 'locateur') {
                return $this->redirectToRoute('app_forgot_password_request_locateur', [], Response::HTTP_SEE_OTHER);
            }
            else if ($libelle === 'chauffeur'){
            return $this->redirectToRoute('app_forgot_password_request_chauffeur',[], Response::HTTP_SEE_OTHER);
        }
        }
    
        return $this->renderForm('login/rolereset.html.twig', [
            'form' => $form,
        ]);
    }


    #[Route('/dashboard/stat', name: 'stat', methods: ['POST','GET'])]
public function roleStatistics(RoleRepository $roleRepository): Response
{
    $total = $roleRepository->countByLibelle('client') +
             $roleRepository->countByLibelle('locateur') +
             $roleRepository->countByLibelle('chauffeur');

             $clientCount = $roleRepository->countByLibelle('client');
             $locateurCount = $roleRepository->countByLibelle('locateur');
             $chauffeurCount = $roleRepository->countByLibelle('chauffeur');          

   $clientPercentage = round(($clientCount / $total) * 100);
    $locateurPercentage = round(($locateurCount / $total) * 100);
    $chauffeurPercentage = round(($chauffeurCount / $total) * 100);

    return $this->render('voiture/stat.html.twig', [
        'clientPercentage' => $clientPercentage,
        'locateurPercentage' => $locateurPercentage,
        'chauffeurPercentage' => $chauffeurPercentage,
    ]);
}


}
