<?php

namespace App\Controller;

use App\Entity\Voiture;
use App\Entity\Locateur;
use App\Entity\Client;
use App\Form\VoitureFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\VoitureRepository;
use App\Repository\RoleRepository;
use App\Repository\ReclamationRepository;
use App\Repository\CoVoiturageRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;


class VoitureController extends AbstractController
{
    #[Route('/voiture', name: 'app_voiture')]
    public function index(): Response
    {
        return $this->render('voiture/index.html.twig', [
            'controller_name' => 'VoitureController',
        ]);
    }

//admin
    #[Route('/voiture/Affichelist', name: 'app_voitureaffiche')]
    public function Affiche(VoitureRepository $repository, Request $request)
    {
        $voiture = $repository->findall();

        return $this->render('voiture/Affiche.html.twig', [
            'voiture' => $voiture,
        ]);
    }

//locateur
    #[Route('locateur/{id_loc}/profilloc/voiture/Affichelocateur', name: 'app_voitureaffichenonreserve')]
    public function Affichernoneserve(VoitureRepository $repository, PaginatorInterface $paginator, Request $request , int $id_loc)
    {
        $userRepository = $this->getDoctrine()->getRepository(Locateur::class);
        $locateur = $userRepository->find($id_loc);

        $voiture = $repository->findby(['id_loc'=>$id_loc]);
        //$voiture = $repository->findBy(idLocateur=);
        return $this->render('voiture/Affichereserve.html.twig',
            [
                'id_loc' => $id_loc,
                'locateur'=>$locateur,
                'voiture' => $voiture
        ]);
    }


    //client
    #[Route('client/{id_client}/profilloc/voiture/AfficheCli', name: 'app_voitureaffichenonreserveCli')]
    public function AffichernoneserveCli(VoitureRepository $repository, PaginatorInterface $paginator, Request $request , int $id_client)
    {
        $userRepository = $this->getDoctrine()->getRepository(Client::class);
        $client = $userRepository->find($id_client);

        $voiture = $repository->findall();
        //$voiture = $repository->findBy(idLocateur=);
        return $this->render('voiture/AffichereserveCli.html.twig',
            [
                'id_client' => $id_client,
                'client'=>$client,
                'voiture' => $voiture
        ]);
    }


//admin                                                                                                                                                                                   
    #[Route('voiture/deleteVoiture/{id}', name: 'app_DeleteVoiture')]
    public function deleteStatique($id, VoitureRepository $repo, ManagerRegistry $doctrine): Response
    {
        $voiture = $repo->find($id);
        $em = $doctrine->getManager();
        $em->remove($voiture);
        $em->flush();
        return $this->redirectToRoute("app_voitureaffiche");


    }

    //locateur
    #[Route('/voiture/locateurvoiture/deleteVoiture/{id}', name: 'app_DeleteVoiture2')]
    public function deleteStatique2($id, VoitureRepository $repo, ManagerRegistry $doctrine): Response
    {
        $voiture = $repo->find($id);
        $id_loc = $voiture->getIdLoc();
        $userRepository = $this->getDoctrine()->getRepository(Locateur::class);
        $locateur = $userRepository->find($id_loc);
        $em = $doctrine->getManager();
        $em->remove($voiture);
        $em->flush();
        return $this->redirectToRoute('app_voitureaffichenonreserve',['id_loc'=>$locateur->getIdLoc()]);


    }

    //admin
    #[Route('/updateVoiture/{id}', name: 'updateVoiture')]
    public function updateVoiture(Request $request, ManagerRegistry $doctrine, Voiture $voiture)
    {
        $form = $this->createForm(VoitureFormType::class, $voiture);
        // $form->add('Update',SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $file = $form->get('picture')->getData();

            if ($file) {
                $uploadsDirectory = $this->getParameter('uploads_directory');
                $imgFilename = $file->getClientOriginalName();
                $file->move($uploadsDirectory, $imgFilename);
                $voiture->setPicture($imgFilename);
            }
            $entityManager = $doctrine->getManager();
            $entityManager->flush();
            return $this->redirectToRoute('app_voitureaffiche', ['id' => $voiture->getId()]);
        }
        return $this->render('voiture/updateV.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    //locateur
    #[Route('/{id_loc}/updateVoiture/locateurvoiture/{id}', name: 'updateVoiture2')]
    public function updateVoiture2(Request $request, ManagerRegistry $doctrine, Voiture $voiture)
    {
        $form = $this->createForm(VoitureFormType::class, $voiture);
        // $form->add('Update',SubmitType::class);
        $id_loc = $voiture->getIdLoc();
        $userRepository = $this->getDoctrine()->getRepository(Locateur::class);
        $locateur = $userRepository->find($id_loc);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $file = $form->get('picture')->getData();

            if ($file) {
                $uploadsDirectory = $this->getParameter('uploads_directory');
                $imgFilename = $file->getClientOriginalName();
                $file->move($uploadsDirectory, $imgFilename);
                $voiture->setPicture($imgFilename);
            }
            
            $entityManager = $doctrine->getManager();
            $entityManager->flush();
            return $this->redirectToRoute('app_voitureaffichenonreserve',['id_loc'=>$locateur->getIdLoc()]);
        }
        return $this->render('voiture/updateV2.html.twig', [
            'id_loc'=> $id_loc,
            'locateur'=> $locateur,
            'form' => $form->createView(),
        ]);
    }

    //locateur
    #[Route('locateur/{id_loc}/profilloc/voiture/add', name: 'addVoiture')]
    public function addVoiture(ManagerRegistry $doctrine, Request $request,Locateur $locateur,int $id_loc): Response
    {
        $userRepository = $this->getDoctrine()->getRepository(Locateur::class);
        $user = $userRepository->find($id_loc);
        $voiture = new Voiture();
        $voiture->setIdLoc($user);
        $form = $this->createForm(VoitureFormType::class, $voiture);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('picture')->getData();


            if ($file) {
                $uploadsDirectory = $this->getParameter('uploads_directory');
                $imgFilename = $file->getClientOriginalName();
                $file->move($uploadsDirectory, $imgFilename);
                $voiture->setPicture($imgFilename);
            }


            $voiture->setEtat("non réservé");
            $em = $doctrine->getManager();
            $em->persist($voiture);
            $em->flush();


            return $this->redirectToRoute('app_voitureaffichenonreserve',['id_loc'=>$user->getIdLoc()]);
        }

        return $this->render('voiture/addV.html.twig', [
            'id_loc'=>$id_loc,
            'locateur'=>$locateur,
            'form' => $form->createView(),
        ]);
    }

     //client
   /*  #[Route('client/{id_client}/profilcli/voiture/add', name: 'addVoitureCli')]
     public function addVoitureCli(ManagerRegistry $doctrine, Request $request,Client $client,int $id_client): Response
     {
        $userRepository = $this->getDoctrine()->getRepository(Client::class);
        $user = $userRepository->find($id_client);
         $voiture = new Voiture();
         $voiture->setIdClient($user);
         $form = $this->createForm(VoitureFormType::class, $voiture);
 
         $form->handleRequest($request);
 
         if ($form->isSubmitted() && $form->isValid()) {
             $file = $form->get('picture')->getData();
 
 
             if ($file) {
                 $uploadsDirectory = $this->getParameter('uploads_directory');
                 $imgFilename = $file->getClientOriginalName();
                 $file->move($uploadsDirectory, $imgFilename);
                 $voiture->setPicture($imgFilename);
             }
 
 
             $voiture->setEtat("non réservé");
             $em = $doctrine->getManager();
             $em->persist($voiture);
             $em->flush();
 
 
             return $this->redirectToRoute('app_voitureaffichenonreserveCli',['id_client'=>$user->getIdClient()]);
         }
 
         return $this->render('voiture/addV.html.twig', [
             'id_client'=>$id_client,
             'client'=>$client,
             'form' => $form->createView(),
         ]);
     }*/

//admin
    #[Route('/voiture/show/{id}', name: 'app_voiture_show', methods: ['GET'])]
    public function show(Voiture $voiture, VoitureRepository $repository, int $id): Response
    {
        // Check if the car information matches the given ID
        if ($voiture->getId() !== $id) {
            throw $this->createNotFoundException();
        }

        // Get car information
        $carInfo = "Brand: " . $voiture->getMarque() . "\n" .
            "Registration Number: " . $voiture->getMatricule() . "\n" .
            "Status: " . $voiture->getEtat() . "\n" .
            "Energy: " . $voiture->getEnergie() . "\n" .
            "Price per day: " . $voiture->getEnergie() . "\n";

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

        return $this->render('voiture/show.html.twig', [
            'voiture' => $voiture,
            'qr' => $qrCode->getDataUri(),
        ]);
    }

//locateur
    #[Route('voiture/locateurvoiture/show/{id}', name: 'app_locateurvoiture_show', methods: ['GET'])]
    public function show2(Voiture $voiture, int $id, VoitureRepository $voitureRepository): Response
    {
        $voiture = $voitureRepository->find($id);
        $id_loc = $voiture->getIdLoc();
        $userRepository = $this->getDoctrine()->getRepository(Locateur::class);
        $locateur = $userRepository->find($id_loc);
        
        return $this->render('voiture/show2.html.twig', [
            'locateur' => $locateur,
            'voiture' => $voiture,
        ]);
    }

    //client
    #[Route('/voiture/AffichelistClient/{id_client}', name: 'app_voitureaffichClient')]
    public function AfficherClient(VoitureRepository $repository,int $id_client)
    {
        $userRepository = $this->getDoctrine()->getRepository(Client::class);
        $client = $userRepository->find($id_client);
        //$repo=$this->getDoctrine()->getRepository(Voiture::class);
        $voiture = $repository->findBy(['etat' => 'non réservé']);


        return $this->render('voiture/AfficheClient.html.twig',
            [
                'id_client'=>$id_client,
                'client'=> $client,
                'voiture' => $voiture
            ]);
    }

    #[Route('/voiture/Clientvoiture/show/{id_client}/{id}', name: 'app_Clientvoiture_show', methods: ['GET'])]
    public function show3(Voiture $voiture,int $id_client): Response
    {
        $userRepository = $this->getDoctrine()->getRepository(Client::class);
        $client = $userRepository->find($id_client);
        return $this->render('voiture/show3.html.twig', [
            'id_client'=>$id_client,
            'client'=>$client,
            'voiture' => $voiture,
        ]);
    }


    // stat
    #[Route('/dashboard/stat', name: 'stat', methods: ['POST','GET'])]
    public function VoitureStatistics( VoitureRepository $repo,CoVoiturageRepository $Cov_repo,RoleRepository $roleRepository,ReclamationRepository $rec_repo): Response
    {
        $total = $repo->countByLibelle('BMW') +
            $repo->countByLibelle('Mercedes') +
            $repo->countByLibelle('Audi') +
            $repo->countByLibelle('clio') +
            $repo->countByLibelle('porshe') +
            $repo->countByLibelle('peugeot') +
            $repo->countByLibelle('hamer');

        $BMWCount = $repo->countByLibelle('BMW');
        $MercedesCount = $repo->countByLibelle('Mercedes');
        $AudiCount = $repo->countByLibelle('Audi');
        $clioCount= $repo->countByLibelle('clio');
        $porsheCount = $repo->countByLibelle('porshe');
        $peugeotCount = $repo->countByLibelle('peugeot');
        $hamerCount = $repo->countByLibelle('hamer');


        $BmwPercentage = round(($BMWCount / $total) * 100);
        $MercedesPercentage = round(($MercedesCount / $total) * 100);
        $AudiPercentage = round(($AudiCount / $total) * 100);
        $clioPercentage = round(($clioCount/ $total) * 100);
        $porshePercentage = round(($porsheCount / $total) * 100);
        $peugeotPercentage = round(($peugeotCount / $total) * 100);
        $hamerPercentage = round(($hamerCount / $total) * 100);


        $total = $Cov_repo->countByLibelle('Aryanah') +
        $Cov_repo->countByLibelle('Bizerte') +
        $Cov_repo->countByLibelle('Beja') +
        $Cov_repo->countByLibelle('Tunis') +
        $Cov_repo->countByLibelle('Sfax') +
        $Cov_repo->countByLibelle('Kairouan') +
        $Cov_repo->countByLibelle('Jandouba') +
        $Cov_repo->countByLibelle('Ben Arous') +
        $Cov_repo->countByLibelle('Manouba') +
        $Cov_repo->countByLibelle('Gabes') +
        $Cov_repo->countByLibelle('Nabeul') +
        $Cov_repo->countByLibelle('Zaghouan');

    $AryanahCount = $Cov_repo->countByLibelle('Aryanah');
    $BizerteCount = $Cov_repo->countByLibelle('Bizerte');
    $BejaCount = $Cov_repo->countByLibelle('Beja');
    $TunisCount = $Cov_repo->countByLibelle('Tunis');
    $SfaxCount = $Cov_repo->countByLibelle('Sfax');
    $KairouanCount = $Cov_repo->countByLibelle('Kairouan');
    $JandoubaCount = $Cov_repo->countByLibelle('Jandouba');
    $Ben_ArousCount = $Cov_repo->countByLibelle('Ben Arous');
    $ManoubaCount = $Cov_repo->countByLibelle('Manouba');
    $NabeulCount = $Cov_repo->countByLibelle('Nabeul');
    $ZaghouanCount = $Cov_repo->countByLibelle('Zaghouan');
    $GabesCount = $Cov_repo->countByLibelle('Gabes');

    $AryanahPercentage = round(($AryanahCount / $total) * 100);
    $BizertePercentage = round(($BizerteCount / $total) * 100);
    $BejaPercentage = round(($BejaCount / $total) * 100);
    $TunisPercentage = round(($TunisCount / $total) * 100);
    $SfaxPercentage = round(($SfaxCount / $total) * 100);
    $KairouanPercentage = round(($KairouanCount / $total) * 100);
    $JandoubaPercentage = round(($JandoubaCount / $total) * 100);
    $Ben_ArousPercentage = round(($Ben_ArousCount / $total) * 100);
    $ManoubaPercentage = round(($ManoubaCount / $total) * 100);
    $NabeulPercentage = round(($NabeulCount / $total) * 100);
    $ZaghouanPercentage = round(($ZaghouanCount / $total) * 100);
    $GabesPercentage = round(($GabesCount / $total) * 100);




    $total = $Cov_repo->countByLibelle2('Aryanah') +
        $Cov_repo->countByLibelle2('Bizerte') +
        $Cov_repo->countByLibelle2('Beja') +
        $Cov_repo->countByLibelle2('Tunis') +
        $Cov_repo->countByLibelle2('Sfax') +
        $Cov_repo->countByLibelle2('Kairouan') +
        $Cov_repo->countByLibelle2('Jandouba') +
        $Cov_repo->countByLibelle2('Ben Arous') +
        $Cov_repo->countByLibelle2('Manouba') +
        $Cov_repo->countByLibelle2('Gabes') +
        $Cov_repo->countByLibelle2('Nabeul') +
        $Cov_repo->countByLibelle2('Zaghouan');

    $AryanahCount2 = $Cov_repo->countByLibelle2('Aryanah');
    $BizerteCount2 = $Cov_repo->countByLibelle2('Bizerte');
    $BejaCount2 = $Cov_repo->countByLibelle2('Beja');
    $TunisCount2 = $Cov_repo->countByLibelle2('Tunis');
    $SfaxCount2 = $Cov_repo->countByLibelle2('Sfax');
    $KairouanCount2 = $Cov_repo->countByLibelle2('Kairouan');
    $JandoubaCount2 = $Cov_repo->countByLibelle2('Jandouba');
    $Ben_ArousCount2 = $Cov_repo->countByLibelle2('Ben Arous');
    $ManoubaCount2 = $Cov_repo->countByLibelle2('Manouba');
    $NabeulCount2 = $Cov_repo->countByLibelle2('Nabeul');
    $ZaghouanCount2 = $Cov_repo->countByLibelle2('Zaghouan');
    $GabesCount2 = $Cov_repo->countByLibelle2('Gabes');

    $AryanahPercentage2 = round(($AryanahCount2 / $total) * 100);
    $BizertePercentage2 = round(($BizerteCount2 / $total) * 100);
    $BejaPercentage2 = round(($BejaCount2 / $total) * 100);
    $TunisPercentage2 = round(($TunisCount2 / $total) * 100);
    $SfaxPercentage2 = round(($SfaxCount2 / $total) * 100);
    $KairouanPercentage2 = round(($KairouanCount2 / $total) * 100);
    $JandoubaPercentage2 = round(($JandoubaCount2 / $total) * 100);
    $Ben_ArousPercentage2 = round(($Ben_ArousCount2 / $total) * 100);
    $ManoubaPercentage2 = round(($ManoubaCount2 / $total) * 100);
    $NabeulPercentage2 = round(($NabeulCount2 / $total) * 100);
    $ZaghouanPercentage2 = round(($ZaghouanCount2 / $total) * 100);
    $GabesPercentage2 = round(($GabesCount2 / $total) * 100);

    $total = $roleRepository->countByLibelle('client') +
    $roleRepository->countByLibelle('locateur') +
    $roleRepository->countByLibelle('chauffeur');

    $clientCount = $roleRepository->countByLibelle('client');
    $locateurCount = $roleRepository->countByLibelle('locateur');
    $chauffeurCount = $roleRepository->countByLibelle('chauffeur');          

$clientPercentage = round(($clientCount / $total) * 100);
$locateurPercentage = round(($locateurCount / $total) * 100);
$chauffeurPercentage = round(($chauffeurCount / $total) * 100);

$total = $rec_repo->countByLibelle('Technique') +
$rec_repo->countByLibelle('Eco') +
$rec_repo->countByLibelle('Other');

$TechniqueCount = $rec_repo->countByLibelle('Technique');
$EcoCount = $rec_repo->countByLibelle('Eco');
$OtherCount = $rec_repo->countByLibelle('Other');


$TechniquePercentage = round(($TechniqueCount / $total) * 100);
$EcoPercentage = round(($EcoCount / $total) * 100);
$OtherPercentage = round(($OtherCount / $total) * 100);




        return $this->render('voiture/stat.html.twig', [
            'BMWPercentage' => $BmwPercentage,
            'MercedesPercentage' => $MercedesPercentage,
            'AudiPercentage' => $AudiPercentage,
            'clioPercentage' => $clioPercentage,
            'porshePercentage' => $porshePercentage,
            'peugeotPercentage' => $peugeotPercentage,
            'hamerPercentage' => $hamerPercentage,
            'AryanahPercentage' => $AryanahPercentage,
            'BizertePercentage' => $BizertePercentage,
            'BejaPercentage' => $BejaPercentage,
            'TunisPercentage' => $TunisPercentage,
            'SfaxPercentage' => $SfaxPercentage,
            'KairouanPercentage' => $KairouanPercentage,
            'JandoubaPercentage' => $JandoubaPercentage,
            'Ben_ArousPercentage' => $Ben_ArousPercentage,
            'ManoubaPercentage' => $ManoubaPercentage,
            'NabeulPercentage' => $NabeulPercentage,
            'ZaghouanPercentage' => $ZaghouanPercentage,
            'GabesPercentage' => $GabesPercentage,
            'AryanahPercentage2' => $AryanahPercentage2,
            'BizertePercentage2' => $BizertePercentage2,
            'BejaPercentage2' => $BejaPercentage2,
            'TunisPercentage2' => $TunisPercentage2,
            'SfaxPercentage2' => $SfaxPercentage2,
            'KairouanPercentage2' => $KairouanPercentage2,
            'JandoubaPercentage2' => $JandoubaPercentage2,
            'Ben_ArousPercentage2' => $Ben_ArousPercentage2,
            'ManoubaPercentage2' => $ManoubaPercentage2,
            'NabeulPercentage2' => $NabeulPercentage2,
            'ZaghouanPercentage2' => $ZaghouanPercentage2,
            'GabesPercentage2' => $GabesPercentage2,
            'clientPercentage' => $clientPercentage,
            'locateurPercentage' => $locateurPercentage,
            'chauffeurPercentage' => $chauffeurPercentage,
            'TechniquePercentage' => $TechniquePercentage,
            'EcoPercentage' => $EcoPercentage,
            'OtherPercentage' => $OtherPercentage,


        ]);
    }
    #[Route('/dashboard/stat2', name: 'stat2', methods: ['POST','GET'])]
    public function VoitureStatistics2( VoitureRepository $repo): Response
    {
        $total = $repo->countByLibelle('BMW') +
            $repo->countByLibelle('Mercedes') +
            $repo->countByLibelle('Audi') +
            $repo->countByLibelle('clio') +
            $repo->countByLibelle('porshe') +
            $repo->countByLibelle('peugeot') +
            $repo->countByLibelle('hamer');

        $BMWCount = $repo->countByLibelle('BMW');
        $MercedesCount = $repo->countByLibelle('Mercedes');
        $AudiCount = $repo->countByLibelle('Audi');
        $clioCount= $repo->countByLibelle('clio');
        $porsheCount = $repo->countByLibelle('porshe');
        $peugeotCount = $repo->countByLibelle('peugeot');
        $hamerCount = $repo->countByLibelle('hamer');


        $BmwPercentage = round(($BMWCount / $total) * 100);
        $MercedesPercentage = round(($MercedesCount / $total) * 100);
        $AudiPercentage = round(($AudiCount / $total) * 100);
        $clioPercentage = round(($clioCount/ $total) * 100);
        $porshePercentage = round(($porsheCount / $total) * 100);
        $peugeotPercentage = round(($peugeotCount / $total) * 100);
        $hamerPercentage = round(($hamerCount / $total) * 100);

        return $this->render('voiture/stat2.html.twig', [
            'BMWPercentage' => $BmwPercentage,
            'MercedesPercentage' => $MercedesPercentage,
            'AudiPercentage' => $AudiPercentage,
            'clioPercentage' => $clioPercentage,
            'porshePercentage' => $porshePercentage,
            'peugeotPercentage' => $peugeotPercentage,
            'hamerPercentage' => $hamerPercentage,

        ]);
    }

    #[Route('/voiture/search', name: 'searchvoiture', methods: ['GET', 'POST'])]
    public function search2(Request $request, VoitureRepository $repo): Response
    {
        $query = $request->query->get('query');
        $id = $request->query->get('id');
        $marque = $request->query->get('marque');
        $matricule = $request->query->get('matricule');

        $voiture = $repo->advancedSearch($query, $id, $marque, $matricule);

        return $this->render('voiture/Affiche.html.twig', [
            'voiture' => $voiture,
        ]);
    }

    #[Route('/voiture/tricroi', name: 'tri', methods: ['GET','POST'])]
    public function triCroissant( VoitureRepository $VoitureRepository): Response
    {
        $voiture = $VoitureRepository->findAllSorted();

        return $this->render('voiture/Affiche.html.twig', [
            'voiture' => $voiture,
        ]);
    }


}
