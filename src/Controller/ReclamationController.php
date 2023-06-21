<?php

namespace App\Controller;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Entity\Reclamation;
use App\Form\Reclamation1Type;
use App\Repository\ReclamationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Reponse;
use App\Form\ReponseType;
use App\Repository\ReponseRepository;

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

//use Twilio\Rest\Client;
use App\Service\AjoutNotificationService;
use Dompdf\Dompdf;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;

use Doctrine\Persistence\ManagerRegistry;

#[Route('/reclamation')]
class ReclamationController extends AbstractController
{
    #[Route('/', name: 'app_reclamation_index', methods: ['GET'])]
    public function index(ReclamationRepository $reclamationRepository): Response
    {
        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $reclamationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ReclamationRepository $reclamationRepository): Response
    {
        $reclamation = new Reclamation();
        $form = $this->createForm(Reclamation1Type::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $file = $form->get('image')->getData();

            if ($file) {
                $fileName = $file->getClientOriginalName();
    
                try {
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    // Handle the exception
                }
                $reclamation->setImage($fileName);
            }

            $reclamation->setEtat("non traité");

            $reclamationRepository->save($reclamation, true);

            $email = (new Email())
                    ->from('symfonycopte822@gmail.com')
                    ->to('mohamedtaher.guerfala@esprit.tn')
                    ->subject('Reclamation Confirmation')
                    ->text('        Votre réclamation est recu');

                $transport = new GmailSmtpTransport('symfonycopte822@gmail.com', 'cdwgdrevbdoupxhn');
                $mailer = new Mailer($transport);
                $mailer->send($email);

            /*$sid    = "AC05b89f04ba1c27fc780bd3166b444e26";
            $token  = "26a66eb5c2d1d29e068f4452cecc81df";
            $twilio = new Client($sid, $token);

            $message = $twilio->messages
                ->create("+21654833493", // to
                    array(
                            "from" => "+16206282526",
                            "body" => "Votre réclamation est recu !!!"
                        )
                    );*/

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('reclamation/new.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('/search', name: 'searchRec')]
    public function search(Request $request, ReclamationRepository $repository): Response
    {
        $value = $request->request->get('value');
        $reclamation = $repository->searchBynom($value);
        return $this->render('reclamation/search.html.twig', [
            'reclamations' => $reclamation
        ]);
    }

//client ajout front
    #[Route('/{id_client}/newF', name: 'app_reclamation_newF', methods: ['GET', 'POST'])]
    public function newF(Request $request, ReclamationRepository $reclamationRepository, AjoutNotificationService $notifcationService, int $id_client): Response
    {
        
        $userRepository = $this->getDoctrine()->getRepository(Client::class);
        $client = $userRepository->find($id_client);
        $role = $client->getIdRole();
        $id = $role->getIdRole();
        $id_user = $id;
        
        $reclamation = new Reclamation();
        $form = $this->createForm(Reclamation1Type::class, $reclamation);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            
            $file = $form->get('image')->getData();

            if ($file) {
                $fileName = $file->getClientOriginalName();
    
                try {
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    // Handle the exception
                }
                $reclamation->setImage($fileName);
            }

            $reclamation->setEtat("non traité");

            $reclamation->setIdUser($id_user);

            $reclamationRepository->save($reclamation, true);

            $notifcationService->sendEmail();

            /*$sid    = "ACac8596dd282c3072d3da4dbb09625ab1";
            $token  = "e0f542fecc731d9f8e9d87a4709aae32";
            $twilio = new Client($sid, $token);

            $message = $twilio->messages
                ->create("+21654833493", // to
                    array(
                            "from" => "+12766226225",
                            "body" => "Votre réclamation est recu !!!"
                        )
                    );*/

            return $this->redirectToRoute('app_reclamation_front', ['id_client'=> $id_client], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('reclamation/newF.html.twig', [
            'client' => $client,
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }


//locateur ajout front
#[Route('/loc/{id_loc}/newF', name: 'app_reclamation_newFloc', methods: ['GET', 'POST'])]
public function newFloc(Request $request, ReclamationRepository $reclamationRepository, AjoutNotificationService $notifcationService, int $id_loc): Response
{
    
    $userRepository = $this->getDoctrine()->getRepository(Locateur::class);
    $locateur = $userRepository->find($id_loc);
    $role = $locateur->getIdRole();
    $id = $role->getIdRole();
    $id_user = $id;
    
    $reclamation = new Reclamation();
    $form = $this->createForm(Reclamation1Type::class, $reclamation);
    $form->handleRequest($request);


    if ($form->isSubmitted() && $form->isValid()) {
        
        $file = $form->get('image')->getData();

        if ($file) {
            $fileName = $file->getClientOriginalName();

            try {
                $file->move(
                    $this->getParameter('uploads_directory'),
                    $fileName
                );
            } catch (FileException $e) {
                // Handle the exception
            }
            $reclamation->setImage($fileName);
        }

        $reclamation->setEtat("non traité");

        $reclamation->setIdUser($id_user);

        $reclamationRepository->save($reclamation, true);

        $notifcationService->sendEmail();

        /*$sid    = "ACac8596dd282c3072d3da4dbb09625ab1";
        $token  = "e0f542fecc731d9f8e9d87a4709aae32";
        $twilio = new Client($sid, $token);

        $message = $twilio->messages
            ->create("+21654833493", // to
                array(
                        "from" => "+12766226225",
                        "body" => "Votre réclamation est recu !!!"
                    )
                );*/

        return $this->redirectToRoute('app_reclamation_frontloc', ['id_loc'=> $id_loc], Response::HTTP_SEE_OTHER);
    }

    return $this->renderForm('reclamation/newF.html.twig', [
        'locateur' => $locateur,
        'reclamation' => $reclamation,
        'form' => $form,
    ]);
}


//chauffeur ajout front
    #[Route('/ch/{id_ch}/newF', name: 'app_reclamation_newFCh', methods: ['GET', 'POST'])]
    public function newFch(Request $request, ReclamationRepository $reclamationRepository, AjoutNotificationService $notifcationService, int $id_ch): Response
    {
           
    $userRepository = $this->getDoctrine()->getRepository(Chauffeur::class);
    $chauffeur = $userRepository->find($id_ch);
    $role = $chauffeur->getIdRole();
    $id = $role->getIdRole();
    $id_user = $id;
    
    $reclamation = new Reclamation();
    $form = $this->createForm(Reclamation1Type::class, $reclamation);
    $form->handleRequest($request);


    if ($form->isSubmitted() && $form->isValid()) {
        
        $file = $form->get('image')->getData();

        if ($file) {
            $fileName = $file->getClientOriginalName();

            try {
                $file->move(
                    $this->getParameter('uploads_directory'),
                    $fileName
                );
            } catch (FileException $e) {
                // Handle the exception
            }
            $reclamation->setImage($fileName);
        }

        $reclamation->setEtat("non traité");

        $reclamation->setIdUser($id_user);

        $reclamationRepository->save($reclamation, true);

        $notifcationService->sendEmail();

        /*$sid    = "ACac8596dd282c3072d3da4dbb09625ab1";
        $token  = "e0f542fecc731d9f8e9d87a4709aae32";
        $twilio = new Client($sid, $token);

        $message = $twilio->messages
            ->create("+21654833493", // to
                array(
                        "from" => "+12766226225",
                        "body" => "Votre réclamation est recu !!!"
                    )
                );*/

        return $this->redirectToRoute('app_reclamation_frontCh', ['id_ch'=> $id_ch], Response::HTTP_SEE_OTHER);
    }

    return $this->renderForm('reclamation/newF.html.twig', [
        'chauffeur' => $chauffeur,
        'reclamation' => $reclamation,
        'form' => $form,
    ]);

    }


    
//client front
     #[Route('/client/{id_client}/front', name: 'app_reclamation_front', methods: ['GET'])]
    public function front(ReclamationRepository $reclamationRepository, int $id_client): Response
    {
        $userRepository = $this->getDoctrine()->getRepository(Client::class);
        $client = $userRepository->find($id_client);
        $role = $client->getIdRole();
        $id_user = $role->getIdRole();
        $reclamations = $reclamationRepository->findBy(['id_user'=>$id_user]);
   
        return $this->render('reclamation/showAll.html.twig', [
            'id_client' => $id_client,
            'client' => $client,
            'reclamations' => $reclamations,
        ]);
    }


//locateur front
#[Route('/loc/{id_loc}/front', name: 'app_reclamation_frontloc', methods: ['GET'])]
public function frontloc(ReclamationRepository $reclamationRepository, int $id_loc): Response
{
    $userRepository = $this->getDoctrine()->getRepository(Locateur::class);
    $locateur = $userRepository->find($id_loc);
    $role = $locateur->getIdRole();
    $id_user = $role->getIdRole();
    $reclamations = $reclamationRepository->findBy(['id_user'=>$id_user]);
   
    return $this->render('reclamation/showAllLoc.html.twig', [
        'id_loc' => $id_loc,
        'locateur' => $locateur,
        'reclamations' => $reclamations,
    ]);
}


//chauffeur front
#[Route('/chauffeur/{id_ch}/front', name: 'app_reclamation_frontCh', methods: ['GET'])]
public function frontlocCh(ReclamationRepository $reclamationRepository, int $id_ch): Response
{
    $userRepository = $this->getDoctrine()->getRepository(Chauffeur::class);
    $chauffeur = $userRepository->find($id_ch);
    $role = $chauffeur->getIdRole();
    $id_user = $role->getIdRole();
    $reclamations = $reclamationRepository->findBy(['id_user'=>$id_user]);
   
    return $this->render('reclamation/showAllCh.html.twig', [
        'id_ch' => $id_ch,
        'chauffeur' => $chauffeur,
        'reclamations' => $reclamations,
    ]);
}


    #[Route('/{id}/rep', name: 'app_reclamation_add', methods: ['GET', 'POST'])]
    public function add(Reclamation $reclamation, Request $request, ReponseRepository $reponseRepository, ReclamationRepository $reclamationRepository): Response
    {
        $reponse = new Reponse();
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $reponse->setIdRec($reclamation);
            $reponseRepository->save($reponse, true);

            $reclamation->setEtat("en cours de traitement");
            $reclamationRepository->save($reclamation, true);

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('reclamation/add.html.twig', [
            'reponse' => $reponse,
            'form' => $form,
            'reclamation' => $reclamation,
        ]);
        
    }

    #[Route('/{id}/rec', name: 'app_reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation, ReclamationRepository $repository, int $id): Response
    {
        $carInfo = "Id: " . $reclamation->getId() . "\n" .
            "Type : " . $reclamation->getType() . "\n" .
            "Commentaire : " . $reclamation->getCommentaire() . "\n" .
            "Etat : " . $reclamation->getEtat() . "\n";

        // Generate QR code with car information
        $qrCode = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($carInfo)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(300)
            ->margin(10)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->labelText("")
            ->labelFont(new NotoSans(20))
            ->labelAlignment(new LabelAlignmentCenter())
            ->build();

        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
            'qr' => $qrCode->getDataUri(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, ReclamationRepository $reclamationRepository): Response
    {
        $form = $this->createForm(Reclamation1Type::class, $reclamation);
        $form->handleRequest($request);
        $originalImage = $reclamation->getImage();

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData(); // get the uploaded file

            if ($imageFile) {
                // generate a unique filename
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
    
                // move the file to the images directory
                $imageFile->move(
                    $this->getParameter('reclamation_images_directory'),
                    $newFilename
                );
    
                // update the entity with the new filename
                $reclamation->setImage($newFilename);
    
                // delete the original image file, if it exists
                if ($originalImage) {
                    $originalImagePath = $this->getParameter('reclamation_images_directory').'/'.$originalImage;
                    if (file_exists($originalImagePath)) {
                        unlink($originalImagePath);
                    }
                }
            } else {
                // use the original image filename
                $reclamation->setImage($originalImage);
            }
            
            $reclamationRepository->save($reclamation, true);

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, ReclamationRepository $reclamationRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->request->get('_token'))) {
            $reclamationRepository->remove($reclamation, true);
        }

        return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }


//delete client
    #[Route('/delcli/front/{id}/{id_client}', name: 'app_reclamation_Fdelete', methods: ['POST','GET'])]
    public function deleteF(Request $request, Reclamation $reclamation, ReclamationRepository $reclamationRepository, int $id_client, ManagerRegistry $doctrine, int $id): Response
    {
        $userRepository = $this->getDoctrine()->getRepository(Client::class);
        $client = $userRepository->find($id_client);
        $id_client = $client->getIdClient();
        $em = $doctrine->getManager();
        $em->remove($reclamation);
        $em->flush();
        return $this->redirectToRoute('app_reclamation_front',['id_client'=>$id_client]);
    }

//delete locatuer
#[Route('/delloc/front/{id}/{id_loc}', name: 'app_reclamation_Fdeleteloc', methods: ['POST','GET'])]
public function deleteFLoc(Request $request, Reclamation $reclamation, ReclamationRepository $reclamationRepository, int $id_loc, ManagerRegistry $doctrine, int $id): Response
{
    $userRepository = $this->getDoctrine()->getRepository(Locateur::class);
    $locateur = $userRepository->find($id_loc);
    $id_loc = $locateur->getIdLoc();
    $em = $doctrine->getManager();
    $em->remove($reclamation);
    $em->flush();
    return $this->redirectToRoute('app_reclamation_frontloc',['id_loc'=>$id_loc]);
}

//delete chauffeur
#[Route('/delch/front/{id}/{id_ch}', name: 'app_reclamation_FdeleteCh', methods: ['POST','GET'])]
public function deleteFch(Request $request, Reclamation $reclamation, ReclamationRepository $reclamationRepository, int $id_ch, ManagerRegistry $doctrine, int $id): Response
{
    $userRepository = $this->getDoctrine()->getRepository(Chauffeur::class);
    $chauffeur = $userRepository->find($id_ch);
    $id_ch = $chauffeur->getIdCh();
    $em = $doctrine->getManager();
    $em->remove($reclamation);
    $em->flush();
    return $this->redirectToRoute('app_reclamation_frontCh',['id_ch'=>$id_ch]);
}


    #[Route('/{id}/traite', name: 'app_reclamation_traite', methods: ['GET', 'POST'])]
    public function traite(Request $request, Reclamation $reclamation, ReclamationRepository $reclamationRepository): Response
    {
        $reclamation->setEtat("traité");
        $reclamationRepository->save($reclamation, true);

        return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/stat', name: 'app_reclamation_stat', methods: ['POST','GET'])]
    public function stat( ReclamationRepository $repo): Response
    {
        $total = $repo->countByLibelle('Technique') +
            $repo->countByLibelle('Eco') +
            $repo->countByLibelle('Other');

        $TechniqueCount = $repo->countByLibelle('Technique');
        $EcoCount = $repo->countByLibelle('Eco');
        $OtherCount = $repo->countByLibelle('Other');


        $TechniquePercentage = round(($TechniqueCount / $total) * 100);
        $EcoPercentage = round(($EcoCount / $total) * 100);
        $OtherPercentage = round(($OtherCount / $total) * 100);
        return $this->render('reclamation/stat.html.twig', [
            'TechniquePercentage' => $TechniquePercentage,
            'EcoPercentage' => $EcoPercentage,
            'OtherPercentage' => $OtherPercentage,

        ]);
    }

    #[Route('/exportpdf', name: 'exportpdf_rec')]
    public function exportToPdf(ReclamationRepository $repository): Response
    {
        // Récupérer les données de réservation depuis votre base de données
        $reclamations = $repository->findAll();

        // Créer le tableau de données pour le PDF
        $tableData = [];
        foreach ($reclamations as $reclamation) {
            $tableData[] = [
                'type' => $reclamation->getType(),
                'commentaire' => $reclamation->getCommentaire(),
                'etat' => $reclamation->getEtat(),
                //'price' => $reservation->getIdVoiture()->getPrixJours(), jointure
                'date_creation' => $reclamation->getDateCreation()->format('Y-m-d H:i:s'),
            ];
        }

        // Créer le PDF avec Dompdf
        $dompdf = new Dompdf();
        $html = $this->renderView('reclamation/export-pdf-rec.html.twig', [
            'tableData' => $tableData,
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        // Envoyer le PDF au navigateur
        $response = new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="reclamation.pdf"',
        ]);
        return $response;
    }



}
