<?php

namespace App\Controller;

use App\Entity\Locateur;
use App\Entity\Role;
use App\Form\LocateurType;
use App\Form\EditLocType;
use App\Repository\LocateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\RoleRepository;
use App\Enum\Etat;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
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
use App\Service\TwilioService;




#[Route('/locateur')]
class LocateurController extends AbstractController
{
    #[Route('/', name: 'app_locateur_index', methods: ['GET'])]
    public function index(LocateurRepository $locateurRepository): Response
    {
        return $this->render('locateur/card.html.twig', [
            'locateurs' => $locateurRepository->findAll(),
        ]);
    }

    #[Route('/new/{idRole<\d+>}', name: 'app_locateur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, LocateurRepository $locateurRepository,RoleRepository $roleRepository, int $idRole, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $roleRepository = $this->getDoctrine()->getRepository(Role::class);
        $role = $roleRepository->find($idRole);
        
        if (!$role) {
            throw $this->createNotFoundException('Role with id ' . $idRole . ' does not exist.');
        }
        $locateur = new Locateur();
        $locateur->setIdRole($role);
        $locateur->setEtat(Etat::ENABLED);
        $form = $this->createForm(LocateurType::class, $locateur,['id_role' => $idRole]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           
            $file = $form->get('img')->getData();

            if ($file) {
                $uploadsDirectory = $this->getParameter('uploads_directory');
                $imgFilename = $file->getClientOriginalName();
                $file->move($uploadsDirectory, $imgFilename);
                $locateur->setImg($imgFilename);
            }
            $locateurRepository->save($locateur, true);

            return $this->redirectToRoute('login', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('locateur/new.html.twig', [
            'id_role' => $idRole,
            'locateur' => $locateur,
            'form' => $form,
        ]);
    }

    #[Route('/{id_loc}', name: 'app_locateur_show', methods: ['GET'])]
    public function show(Locateur $locateur): Response
    {
        return $this->render('locateur/show.html.twig', [
            'locateur' => $locateur,
        ]);
    }

    #[Route('/{id_loc}/edit', name: 'app_locateur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Locateur $locateur, LocateurRepository $repo): Response
    {
        $form = $this->createForm(EditLocType::class, $locateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repo->update($locateur);
            $file = $form->get('img')->getData();
           
            
            if ($file) {
                $uploadsDirectory = $this->getParameter('uploads_directory');
                $imgFilename = $file->getClientOriginalName();
                $file->move($uploadsDirectory, $imgFilename);
                $locateur->setImg($imgFilename);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->render('locateur/profil.html.twig', [
                'locateur' => $locateur
            ]);
        }

        return $this->render('locateur/edit.html.twig', [
            'locateur' => $locateur,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id_loc}', name: 'app_locateur_delete', methods: ['POST'])]
    public function delete(Request $request, Locateur $locateur, LocateurRepository $locateurRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$locateur->getId_loc(), $request->request->get('_token'))) {
            $locateurRepository->remove($locateur, true);
        }

        return $this->redirectToRoute('app_locateur_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/locateurs/{id_loc}/disable', name: 'disable_locateur', methods: ['PATCH','POST','GET'])]
public function disableLocateur(Request $request, Locateur $locateur): Response
{
    $locateur->setEtat(Etat::DISABLED);
    $this->getDoctrine()->getManager()->flush();
    
    return $this->redirectToRoute('app_locateur_index');
}
#[Route('/{id_loc}/profilloc', name: 'profilloc')]
public function profil(locateur $locateur): Response
{
    return $this->render('locateur/profil.html.twig', [
        'locateur' => $locateur,
        'controller_name' => 'LocateurController',
    ]);

    
}
#[Route('/locateur/search', name: 'search', methods: ['GET','POST'])]
public function search(Request $request)
{
    $query = $request->get('query');
    $cin = $request->get('cin');
    $nom = $request->get('nom');
    $prenom = $request->get('prenom');
    $email = $request->get('email');
    $etat = $request->get('etat');

    $locateurs = $this->getDoctrine()
        ->getRepository(Locateur::class)
        ->advancedSearch($query, $cin, $nom, $prenom, $email, $etat);

    return $this->render('locateur/card.html.twig', [
        'locateurs' => $locateurs,
    ]);
    
}


#[Route('/login/role/reset', name: 'app_forgot_password_request_locateur', methods: ['GET','POST'])]
public function request(Request $request, LocateurRepository $userRepository, TokenGeneratorInterface $tokenGenerator, \Swift_Mailer $mailer): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $userRepository->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('danger', 'Adresse e-mail inconnue.');

                return $this->redirectToRoute('app_forgot_password_request_locateur');
            }

            $token = $tokenGenerator->generateToken();

            try {
                $user->setResetToken($token);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());

                return $this->redirectToRoute('app_forgot_password_request_locateur');
            }
           
   

// Create the mailer using the transport
                $transport = new GmailSmtpTransport('aymen58zouari@gmail.com', 'qupoztnbdlklxbhj'); 
                $mailer = new Mailer($transport);
                $emailBody = $this->renderView('login/emailLoc.html.twig', [
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

        return $this->render('login/requestLoc.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

   
    #[Route('/login/role/reset/{token}', name: 'app_reset_password_locateur', methods: ['GET','POST'])]
    public function reset(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = $this->getDoctrine()->getRepository(Locateur::class)->findOneBy(['resetToken' => $token]);

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
#[Route('/login/role/gsm_loc', name: 'gsm_loc', methods: ['GET','POST'])]
public function gsm(): Response
{
    return $this->redirectToRoute('app_forgot_password_request_locateur_gsm', [] ,Response::HTTP_SEE_OTHER);
}

#[Route('/login/role/reset_gsm', name: 'app_forgot_password_request_locateur_gsm', methods: ['GET','POST'])]
public function requestgsm(Request $request, LocateurRepository $userRepository, TokenGeneratorInterface $tokenGenerator): Response
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

                return $this->redirectToRoute('app_forgot_password_request_locateur_gsm');
            }

            $code = random_int(100000, 999999);   
            try {
                $user->setResetToken($code);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());

                return $this->redirectToRoute('app_forgot_password_request_locateur');
            }    

            $accountSid ='ACb8ac250d94d237ea91634b8def26f57d';
            $authToken = '54d4d6dfa4a3e8c998d386857a985a8e';
            $twilioService = new TwilioService($accountSid, $authToken);

           $to = '+216' . $user->getGsm(); // recipient's phone number
           $from = '+15673132411'; // your Twilio phone number
           $body = $code;

           $twilioService->sendSms($from, $to, $body);

           
            $this->addFlash('success', 'Un e-mail de réinitialisation de mot de passe vient de vous être envoyé.');
           
            return $this->redirectToRoute('codeverif_loc', ['token' => $code ]);
        }

        return $this->render('login/gsm.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }


    #[Route('/login/role/reset_gsm/{token}', name: 'codeverif_loc', methods: ['GET','POST'])]
    public function VerifCode(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = $this->getDoctrine()->getRepository(Locateur::class)->findOneBy(['resetToken' => $token]);
        $form = $this->createForm(CodeFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           $code = $form->get('code')->getData();
           $token = $user->getResetToken();
           if($code === $token) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            

            return $this->redirectToRoute('app_reset_password_locateur',['token' => $token]);
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

#[Route('/locateur/tricroi', name: 'tricroissant5', methods: ['GET','POST'])]
public function triCroissant(LocateurRepository $locateurRepository): Response
{
    $locateurs = $locateurRepository->findAllSorted();

    return $this->render('locateur/card.html.twig', [
       'locateurs' => $locateurs,
    ]);
}

#[Route('/locateur/tridesc', name: 'tridesc6', methods: ['GET','POST'])]
public function tridesc(LocateurRepository $locateurRepository): Response
{
    $locateurs = $locateurRepository->findAllSorted2();

    return $this->render('locateur/card.html.twig', [
       'locateurs' => $locateurs,
    ]);
}

#[Route('/locateur/tricroipre', name: 'tricroissantpre7', methods: ['GET','POST'])]
public function triCroissantpre(LocateurRepository $locateurRepository): Response
{
    $locateurs = $locateurRepository->findAllSorted3();

    return $this->render('locateur/card.html.twig', [
       'locateurs' => $locateurs,
    ]);
}

#[Route('/locateur/tridescpre', name: 'tridescpre8', methods: ['GET','POST'])]
public function tridescpre(LocateurRepository $locateurRepository): Response
{
   $locateurs = $locateurRepository->findAllSorted4();

   return $this->render('locateur/card.html.twig', [
      'locateurs' => $locateurs,
   ]);
}




}
