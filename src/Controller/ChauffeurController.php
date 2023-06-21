<?php

namespace App\Controller;

use App\Entity\Chauffeur;
use App\Entity\Role;
use App\Entity\Utilisateur;
use App\Form\ChauffeurType;
use App\Form\ChangePasswordFormType;
use App\Form\GsmFormType;
use App\Form\CodeFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\ChauffeurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Repository\RoleRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\File;
use App\Enum\Etat;
use App\Form\EditChType;
use Symfony\Component\Mailer\Mailer;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Annotations\Annotation\Enum;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\HttpFoundation\Cookie;
use Twilio\Rest\Client;





#[Route('/chauffeur')]
class ChauffeurController extends AbstractController
{
    #[Route('/', name: 'app_chauffeur_index', methods: ['GET'])]
    public function index(ChauffeurRepository $chauffeurRepository): Response
    {
        return $this->render('chauffeur/card.html.twig', [
            'chauffeurs' => $chauffeurRepository->findAll(),
        ]);
    
}


    #[Route('/new/{idRole<\d+>}', name: 'app_chauffeur_new', methods: ['GET', 'POST'])]
    public function new(Request $request,ChauffeurRepository $chauffeurRepository,  RoleRepository $roleRepository, int $idRole): Response
    {
        $roleRepository = $this->getDoctrine()->getRepository(Role::class);
        $role = $roleRepository->find($idRole);
        
        if (!$role) {
            throw $this->createNotFoundException('Role with id ' . $idRole . ' does not exist.');
        }
        $chauffeur = new Chauffeur();
        $chauffeur->setEtat(Etat::ENABLED);
        $chauffeur->setIdRole($role);
        $form = $this->createForm(ChauffeurType::class, $chauffeur,['id_role' => $idRole]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           

            $file = $form->get('img')->getData();
           
            if ($file) {
                $uploadsDirectory = $this->getParameter('uploads_directory');
                $imgFilename = $file->getClientOriginalName();
                $file->move($uploadsDirectory, $imgFilename);
                $chauffeur->setImg($imgFilename);
            }
    
            $chauffeurRepository->save($chauffeur, true);
    

            return $this->redirectToRoute('login', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('chauffeur/new.html.twig', [
            'id_role' => $idRole,
            'chauffeur' => $chauffeur,
            'form' => $form,
        ]);
    }

    #[Route('/{id_ch}', name: 'app_chauffeur_show', methods: ['GET'])]
    public function show(Chauffeur $chauffeur): Response
    {
        return $this->render('chauffeur/show.html.twig', [
            'chauffeur' => $chauffeur,
        ]);
    }

    #[Route('/{id_ch}/edit', name: 'app_chauffeur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Chauffeur $chauffeur, ChauffeurRepository $chauffeurRepository): Response
    {
        $form = $this->createForm(EditChType::class, $chauffeur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $chauffeurRepository->update($chauffeur);
            $file = $form->get('img')->getData();
        
            if ($file) {
                $uploadsDirectory = $this->getParameter('uploads_directory');
                $imgFilename = $file->getClientOriginalName();
                $file->move($uploadsDirectory, $imgFilename);
                $chauffeur->setImg($imgFilename);
            }
           
            $this->getDoctrine()->getManager()->flush();

            return $this->render('chauffeur/profil.html.twig', [
                'chauffeur' => $chauffeur
            ]);
        }

        return $this->render('chauffeur/edit.html.twig', [
            'chauffeur' => $chauffeur,
            'form' => $form->createView(),
        ]);
    }
   /* public function edit(Request $request, Chauffeur $chauffeur, ManagerRegistry $doctrine): Response
    {
        $cin=$chauffeur->getIdRole()->getIdUser()->getCin();
        $nom=$chauffeur->getIdRole()->getIdUser()->getNom();
        $prenom=$chauffeur->getIdRole()->getIdUser()->getPrenom();
       
        $form = $this->createForm(EditChType::class, $chauffeur,[
            'cin' => $cin,
            'nom' => $nom,
            'prenom' => $prenom,

        ]);
        $form->handleRequest($request);
        
       
      
     
        if ($form->isSubmitted() && $form->isValid()) {
            $chauffeur = $form->getData();
            
            
            $file = $form->get('img')->getData();
           
            
            if ($file) {
                $uploadsDirectory = $this->getParameter('uploads_directory');
                $imgFilename = $file->getClientOriginalName();
                $file->move($uploadsDirectory, $imgFilename);
                $chauffeur->setImg($imgFilename);
            }
            $chauffeur->getIdRole()->getIdUser()->setCin($cin);
            $chauffeur->getIdRole()->getIdUser()->setNom($nom);
            $chauffeur->getIdRole()->getIdUser()->setPrenom($prenom);
            $entityManager = $doctrine->getManager();
            $entityManager->flush();

            return $this->render('chauffeur/profil.html.twig', [
                'chauffeur' => $chauffeur
            ]);
        }

        return $this->renderForm('chauffeur/edit.html.twig', [
            'cin' => $cin,
            'nom' => $nom,
            'prenom' => $prenom,
            'chauffeur' => $chauffeur,
            'form' => $form,
        ]);
    }*/

    #[Route('/{id_ch}', name: 'app_chauffeur_delete', methods: ['POST'])]
    public function delete(Request $request, Chauffeur $chauffeur, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$chauffeur->getIdCh(), $request->request->get('_token'))) {
            $entityManager->remove($chauffeur);
            $entityManager->flush();
        }

        return $this->redirectToRoute('login', [], Response::HTTP_SEE_OTHER);
    }

   
#[Route('/chauffeurs/{id_ch}/disable', name: 'disable_chauffeur', methods: ['PATCH','POST','GET'])]
public function disableChauffeur(Request $request, Chauffeur $chauffeur): Response
{
    $chauffeur->setEtat(Etat::DISABLED);
    $this->getDoctrine()->getManager()->flush();
    
    return $this->redirectToRoute('app_chauffeur_index');
}


#[Route('/{id_ch}/profilch', name: 'profilch')]
public function profil(Chauffeur $chauffeur): Response
{
    return $this->render('chauffeur/profil.html.twig', [
        'chauffeur' => $chauffeur,
        'controller_name' => 'ChauffeurController',
    ]);

    
}

#[Route('/chauffeur/search', name: 'search3', methods: ['GET','POST'])]
public function search3(Request $request)
{
    $query = $request->get('query');
    $cin = $request->get('cin');
    $nom = $request->get('nom');
    $prenom = $request->get('prenom');
    $email = $request->get('email');
    $etat = $request->get('etat');

    $chauffeurs = $this->getDoctrine()
        ->getRepository(Chauffeur::class)
        ->advancedSearch($query, $cin, $nom, $prenom, $email, $etat);

    return $this->render('chauffeur/card.html.twig', [
        'chauffeurs' => $chauffeurs,
    ]);
}

#[Route('/login/role/reset', name: 'app_forgot_password_request_chauffeur', methods: ['GET','POST'])]
public function request(Request $request, ChauffeurRepository $userRepository, TokenGeneratorInterface $tokenGenerator): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $userRepository->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('danger', 'Adresse e-mail inconnue.');

                return $this->redirectToRoute('app_forgot_password_request_chauffeur');
            }

            $token = $tokenGenerator->generateToken();

            try {
                $user->setResetToken($token);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());

                return $this->redirectToRoute('app_forgot_password_request_chauffeur');
            }
           
   

// Create the mailer using the transport
                $transport = new GmailSmtpTransport('aymen58zouari@gmail.com', 'qupoztnbdlklxbhj'); 
                $mailer = new Mailer($transport);
                $emailBody = $this->renderView('login/emailCh.html.twig', [
                    'token' => $token,
                ]);
            $message = (new TemplatedEmail())
                ->from('aymen58zouari@gmail.com')
                ->to($user->getEmail())
                ->subject('Réinitialisation de mot de passe')
                ->html($emailBody);
                
                
                $mailer->send($message);

            $this->addFlash('success', 'Un e-mail de réinitialisation de mot de passe vient de vous être envoyé.');

            return $this->redirectToRoute('login');
        }

        return $this->render('login/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    

   
   
    #[Route('/login/role/reset/{token}', name: 'app_reset_password_chauffeur', methods: ['GET','POST'])]
    public function reset(Request $request, string $token): Response
    {
        $user = $this->getDoctrine()->getRepository(Chauffeur::class)->findOneBy(['resetToken' => $token]);

        if (!$user) {
            $this->addFlash('danger', 'Token de réinitialisation de mot de passe inconnu.');

            return $this->redirectToRoute('login');
        }

        $form = $this->createForm(ChangePasswordFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setResetToken(null);
            $user->setPassword(
                $form->get('plainPassword')->getData()
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Mot de passe mis à jour avec succès !');

            return $this->redirectToRoute('login');
        }

        return $this->render('login/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }


    //////////////////////////////////////////////////////////////////GSM//////////////////////////////////////////////
    #[Route('/login/role/gsm', name: 'gsm', methods: ['GET','POST'])]
    public function gsm(): Response
    {
        return $this->redirectToRoute('app_forgot_password_request_chauffeur_gsm', [] ,Response::HTTP_SEE_OTHER);
    }

    #[Route('/login/role/reset_gsm', name: 'app_forgot_password_request_chauffeur_gsm', methods: ['GET','POST'])]
    public function requestgsm(Request $request, ChauffeurRepository $userRepository, TokenGeneratorInterface $tokenGenerator): Response
        {
            $form = $this->createForm(GsmFormType::class);
    
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
                $gsm = $form->get('gsm')->getData();
                //$gsm = '+216' . ltrim($gsm, '0');
                $user = $userRepository->findOneBy(['gsm' => $gsm]);
               // dd($user);
                if (!$user) {
                    $this->addFlash('danger', 'Adresse e-mail inconnue.');
    
                    return $this->redirectToRoute('app_forgot_password_request_chauffeur_gsm');
                }
    
                $code = random_int(100000, 999999);   
                try {
                    $user->setResetToken($code);
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($user);
                    $entityManager->flush();
                } catch (\Exception $e) {
                    $this->addFlash('warning', $e->getMessage());
    
                    return $this->redirectToRoute('app_forgot_password_request_chauffeur');
                }    
    
                $accountSid ='ACb8ac250d94d237ea91634b8def26f57d';
                $authToken = '54d4d6dfa4a3e8c998d386857a985a8e';
                $client = new Client($accountSid, $authToken);
                $message = $client->messages->create(
                    '+216' . $user->getGsm(), // recipient's phone number
            array(
                'from' => '+15673132411', // your Twilio phone number
                'body' => $code
            )
        );
    
               
                $this->addFlash('success', 'Un e-mail de réinitialisation de mot de passe vient de vous être envoyé.');
               
                return $this->redirectToRoute('codeverif_ch', ['token' => $code ]);
            }
    
            return $this->render('login/gsm.html.twig', [
                'requestForm' => $form->createView(),
            ]);
        }


        #[Route('/login/role/reset_gsm/{token}', name: 'codeverif_ch', methods: ['GET','POST'])]
        public function VerifCode(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder): Response
        {
            $user = $this->getDoctrine()->getRepository(Chauffeur::class)->findOneBy(['resetToken' => $token]);
            $form = $this->createForm(CodeFormType::class);
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
               $code = $form->get('code')->getData();
               $token = $user->getResetToken();
               if($code === $token) {
    
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
                
    
                return $this->redirectToRoute('app_reset_password_chauffeur',['token' => $token]);
            }
            else {
                $this->addFlash('error', 'erreur !');
            }
            }
    
            return $this->render('login/code.html.twig', [
                'resetForm' => $form->createView(),
            ]);
        }
    
    
    
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    #[Route('/chauffeur/tricroi', name: 'tricroissant', methods: ['GET','POST'])]
    public function triCroissant(ChauffeurRepository $chauffeurRepository): Response
{
    $chauffeurs = $chauffeurRepository->findAllSorted();

    return $this->render('chauffeur/card.html.twig', [
        'chauffeurs' => $chauffeurs,
    ]);
}

    #[Route('/chauffeur/tridesc', name: 'tridesc', methods: ['GET','POST'])]
    public function tridesc(ChauffeurRepository $chauffeurRepository): Response
    {
        $chauffeurs = $chauffeurRepository->findAllSorted2();

        return $this->render('chauffeur/card.html.twig', [
            'chauffeurs' => $chauffeurs,
        ]);
    }

    #[Route('/chauffeur/tricroipre', name: 'tricroissantpre', methods: ['GET','POST'])]
    public function triCroissantpre(ChauffeurRepository $chauffeurRepository): Response
{
    $chauffeurs = $chauffeurRepository->findAllSorted3();

    return $this->render('chauffeur/card.html.twig', [
        'chauffeurs' => $chauffeurs,
    ]);
}

    #[Route('/chauffeur/tridescpre', name: 'tridescpre', methods: ['GET','POST'])]
    public function tridescpre(ChauffeurRepository $chauffeurRepository): Response
    {
        $chauffeurs = $chauffeurRepository->findAllSorted4();

        return $this->render('chauffeur/card.html.twig', [
            'chauffeurs' => $chauffeurs,
        ]);
    }
   
   
 
}