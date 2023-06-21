<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Role;
use App\Entity\Participation;
use App\Form\ClientType;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\EditCliType;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\RoleRepository;
use App\Enum\Etat;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mailer\Mailer;
use App\Form\GsmFormType;
use App\Form\CodeFormType;
use App\Entity\Highscores;
use App\Service\TwilioService;




#[Route('/client')]
class ClientController extends AbstractController
{
    #[Route('/', name: 'app_client_index', methods: ['GET'])]
    public function index(ClientRepository $clientRepository): Response
    {
        return $this->render('client/card.html.twig', [
            'clients' => $clientRepository->findAll(),
        ]);
    }

    #[Route('/new/{idRole<\d+>}', name: 'app_client_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ClientRepository $clientRepository,RoleRepository $roleRepository, int $idRole, EntityManagerInterface $entityManager ): Response
    {
       
        $roleRepository = $this->getDoctrine()->getRepository(Role::class);
        $role = $roleRepository->find($idRole);
        
        if (!$role) {
            throw $this->createNotFoundException('Role with id ' . $idRole . ' does not exist.');
        }
        $client = new Client();
        $client->setIdRole($role);
        $client->setEtat(Etat::ENABLED);
        $form = $this->createForm(ClientType::class, $client,['id_role' => $idRole]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $highscores = new Highscores();
            $highscores->setIdClient($client); // associe le client à l'entité Highscores
            $highscores->setScore(0); // initialise le score à 0
   
            
            
            $client->setPassword( 
                $form->get('password')->getData()
            );
            $file = $form->get('img')->getData();

            if ($file) {
                $uploadsDirectory = $this->getParameter('uploads_directory');
                $imgFilename = $file->getClientOriginalName();
                $file->move($uploadsDirectory, $imgFilename);
                $client->setImg($imgFilename);
            }
            $clientRepository->save($client, true);
            $entityManager->persist($highscores);
            $entityManager->flush();

            return $this->redirectToRoute('login', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('client/new.html.twig', [
            'id_role' => $idRole,
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id_client}', name: 'app_client_show', methods: ['GET'])]
    public function show(Client $client): Response
    {
        return $this->render('client/show.html.twig', [
            'client' => $client,
        ]);
    }

    #[Route('/{id_client}/edit', name: 'app_client_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Client $client, ClientRepository $repo): Response
    {
        $form = $this->createForm(EditCliType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repo->update($client);
            $file = $form->get('img')->getData();
           
            
            if ($file) {
                $uploadsDirectory = $this->getParameter('uploads_directory');
                $imgFilename = $file->getClientOriginalName();
                $file->move($uploadsDirectory, $imgFilename);
                $client->setImg($imgFilename);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->render('client/profil.html.twig', [
                'client' => $client
            ]);
        }

        return $this->render('client/edit.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id_client}', name: 'app_client_delete', methods: ['POST'])]
    public function delete(Request $request, Client $client, ClientRepository $clientRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$client->getId_client(), $request->request->get('_token'))) {
            $clientRepository->remove($client, true);
        }

        return $this->redirectToRoute('app_client_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/clients/{id_client}/disable', name: 'disable_client', methods: ['PATCH','POST','GET'])]
public function disableClient(Request $request, Client $client): Response
{
    $client->setEtat(Etat::DISABLED);
    $this->getDoctrine()->getManager()->flush();
    
    return $this->redirectToRoute('app_client_index');
}
#[Route('/{id_client}/profilcl', name: 'profilcl')]
public function profil(Client $client): Response
{
    return $this->render('client/profil.html.twig', [
        'client' => $client,
        'controller_name' => 'ClientController',
    ]);

    
}

#[Route('/client/search', name: 'search2', methods: ['GET','POST'])]
public function search2(Request $request)
{
    $query = $request->get('query');
    $cin = $request->get('cin');
    $nom = $request->get('nom');
    $prenom = $request->get('prenom');
    $email = $request->get('email');
    $etat = $request->get('etat');

    $clients = $this->getDoctrine()
        ->getRepository(Client::class)
        ->advancedSearch($query, $cin, $nom, $prenom, $email, $etat);

    return $this->render('client/card.html.twig', [
        'clients' => $clients,
    ]);
}

#[Route('/login/role/reset', name: 'app_forgot_password_request_client', methods: ['GET','POST'])]
public function request(Request $request, ClientRepository $userRepository, TokenGeneratorInterface $tokenGenerator, \Swift_Mailer $mailer): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $userRepository->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('danger', 'Adresse e-mail inconnue.');

                return $this->redirectToRoute('app_forgot_password_request_client');
            }

            $token = $tokenGenerator->generateToken();

            try {
                $user->setResetToken($token);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());

                return $this->redirectToRoute('app_forgot_password_request_client');
            }
           
   

// Create the mailer using the transport
                $transport = new GmailSmtpTransport('aymen58zouari@gmail.com', 'qupoztnbdlklxbhj'); 
                $mailer = new Mailer($transport);
                $emailBody = $this->renderView('login/emailCl.html.twig', [
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

        return $this->render('login/requestCl.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

   
    #[Route('/login/role/reset/{token}', name: 'app_reset_password_client', methods: ['GET','POST'])]
    public function reset(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = $this->getDoctrine()->getRepository(Client::class)->findOneBy(['resetToken' => $token]);

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
     #[Route('/login/role/gsm_client', name: 'gsm_client', methods: ['GET','POST'])]
     public function gsm(): Response
     {
         return $this->redirectToRoute('app_forgot_password_request_client_gsm', [] ,Response::HTTP_SEE_OTHER);
     }
 
     #[Route('/login/role/reset_client_gsm', name: 'app_forgot_password_request_client_gsm', methods: ['GET','POST'])]
     public function requestgsm(Request $request, ClientRepository $userRepository, TokenGeneratorInterface $tokenGenerator): Response
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
     
                     return $this->redirectToRoute('app_forgot_password_request_client_gsm');
                 }
     
                 $code = random_int(100000, 999999);   
                 try {
                     $user->setResetToken($code);
                     $entityManager = $this->getDoctrine()->getManager();
                     $entityManager->persist($user);
                     $entityManager->flush();
                 } catch (\Exception $e) {
                     $this->addFlash('warning', $e->getMessage());
     
                     return $this->redirectToRoute('app_forgot_password_request_client');
                 }    
     
                 $accountSid ='ACb8ac250d94d237ea91634b8def26f57d';
                 $authToken = '54d4d6dfa4a3e8c998d386857a985a8e';
                 $twilioService = new TwilioService($accountSid, $authToken);

                $to = '+216' . $user->getGsm(); // recipient's phone number
                $from = '+15673132411'; // your Twilio phone number
                $body = $code;

                $twilioService->sendSms($from, $to, $body);
     
                
                 $this->addFlash('success', 'Un e-mail de réinitialisation de mot de passe vient de vous être envoyé.');
                
                 return $this->redirectToRoute('codeverif_cl', ['token' => $code ]);
             }
     
             return $this->render('login/gsm.html.twig', [
                 'requestForm' => $form->createView(),
             ]);
         }
 
 
         #[Route('/login/role/reset_client_gsm/{token}', name: 'codeverif_cl', methods: ['GET','POST'])]
         public function VerifCode(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder): Response
         {
             $user = $this->getDoctrine()->getRepository(Client::class)->findOneBy(['resetToken' => $token]);
             $form = $this->createForm(CodeFormType::class);
             $form->handleRequest($request);
     
             if ($form->isSubmitted() && $form->isValid()) {
                $code = $form->get('code')->getData();
                $token = $user->getResetToken();
                if($code === $token) {
     
                 $entityManager = $this->getDoctrine()->getManager();
                 $entityManager->persist($user);
                 $entityManager->flush();
                 
     
                 return $this->redirectToRoute('app_reset_password_client',['token' => $token]);
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
 
     #[Route('/client/tricroi', name: 'tricroissant1', methods: ['GET','POST'])]
     public function triCroissant(ClientRepository $clientRepository): Response
 {
     $clients = $clientRepository->findAllSorted();
 
     return $this->render('client/card.html.twig', [
         'clients' => $clients,
     ]);
 }
 
     #[Route('/client/tridesc', name: 'tridesc2', methods: ['GET','POST'])]
     public function tridesc(ClientRepository $clientRepository): Response
     {
        $clients = $clientRepository->findAllSorted2();
 
         return $this->render('client/card.html.twig', [
            'clients' => $clients,
         ]);
     }
 
     #[Route('/client/tricroipre', name: 'tricroissantpre3', methods: ['GET','POST'])]
     public function triCroissantpre(ClientRepository $clientRepository): Response
 {
    $clients = $clientRepository->findAllSorted3();
 
    return $this->render('client/card.html.twig', [
       'clients' => $clients,
    ]);
 }
 
     #[Route('/client/tridescpre', name: 'tridescpre4', methods: ['GET','POST'])]
     public function tridescpre(ClientRepository $clientRepository): Response
     {
        $clients = $clientRepository->findAllSorted4();
 
        return $this->render('client/card.html.twig', [
           'clients' => $clients,
        ]);
     }


}
