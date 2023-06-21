<?php

namespace App\Controller;

use App\Entity\CoVoiturage;
use App\Entity\Client;
use App\Form\CoVoiturageType;
use App\Repository\CoVoiturageRepository;
use App\Repository\ChauffeurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use App\Entity\Chauffeur;
use Symfony\Component\HttpFoundation\Session\SessionInterface ;




#[Route('/co/voiturage')]
class CoVoiturageController extends AbstractController
{
    #[Route('/', name: 'app_co_voiturage_index', methods: ['GET'])]
    public function index(Request $request, CoVoiturageRepository $coVoiturageRepository): Response
    {
       
            $coVoiturage = $coVoiturageRepository->findAll();
           
        
        return $this->render('co_voiturage/index.html.twig', [
            'co_voiturages' => $coVoiturage,
        ]);
    }

    #[Route('chauffeur/{id_ch}/profilch/index', name: 'app_co_voiturage_index_fornt', methods: ['GET','POST'])]
    public function indexfront(CoVoiturageRepository $coVoiturageRepository, SessionInterface $session,int $id_ch): Response
    {
        $userRepository = $this->getDoctrine()->getRepository(Chauffeur::class);
        $chauffeur = $userRepository->find($id_ch);
        return $this->render('co_voiturage/indexFront.html.twig', [
            'id_ch' => $id_ch,
            'chauffeur' => $chauffeur,
            'co_voiturages' => $coVoiturageRepository->findAll(),
        ]);
    }
    #[Route('client/{id_client}/profilcl/index/client', name: 'app_co_voiturage_index_client', methods: ['GET'])]
    public function indexfrontclient(CoVoiturageRepository $coVoiturageRepository, int $id_client): Response
    {
        $userRepository = $this->getDoctrine()->getRepository(Client::class);
        $client = $userRepository->find($id_client);
        return $this->render('co_voiturage/indexFrontclient.html.twig', [
            'id_client' => $id_client,
            'client' => $client,
            'co_voiturages' => $coVoiturageRepository->findAvailable(),
        ]);
    }

    #[Route('/new', name: 'app_co_voiturage_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CoVoiturageRepository $coVoiturageRepository): Response
    {
        
        $coVoiturage = new CoVoiturage();
        $form = $this->createForm(CoVoiturageType::class, $coVoiturage);
        $form->handleRequest($request);
        $file = $form->get('cov_img')->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            if ($file) {
                $uploadsDirectory = $this->getParameter('uploads_directory');
                $imgFilename = $file->getClientOriginalName();
                $file->move($uploadsDirectory, $imgFilename);
                $coVoiturage->setCovImg($imgFilename);
                // $coVoiturage->send_msg('+21692554097');
            }
            $coVoiturageRepository->save($coVoiturage, true);

            return $this->redirectToRoute('app_co_voiturage_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('co_voiturage/new.html.twig', [
            'co_voiturage' => $coVoiturage,
            'form' => $form,
        ]);
    }

    #[Route('chauffeur/{id_ch}/profilch/index/new', name: 'app_co_voiturage_new2', methods: ['GET', 'POST'])]
    public function newFront(Request $request, CoVoiturageRepository $coVoiturageRepository, SessionInterface $session,int $id_ch,ChauffeurRepository $userRepository): Response
    {
        $userRepository = $this->getDoctrine()->getRepository(Chauffeur::class);
        $chauffeur = $userRepository->find($id_ch);
        $coVoiturage = new CoVoiturage();
        $coVoiturage->setIdCh($chauffeur);
        $form = $this->createForm(CoVoiturageType::class, $coVoiturage);
        $form->handleRequest($request);
        $file = $form->get('cov_img')->getData();

        if ($form->isSubmitted() && $form->isValid()) 
        {
            
          
            if ($file) {
                $uploadsDirectory = $this->getParameter('uploads_directory');
                $imgFilename = $file->getClientOriginalName();
                $file->move($uploadsDirectory, $imgFilename);
                $coVoiturage->setCovImg($imgFilename);
                //$coVoiturage->send_msg('+21692554097');
            }
            $coVoiturageRepository->save($coVoiturage, true);

            return $this->redirectToRoute('app_co_voiturage_index_fornt', ['id_ch' => $id_ch], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('co_voiturage/newfront.html.twig', [
            'id_ch' => $id_ch,
            'chauffeur'=>$chauffeur,
            'co_voiturage' => $coVoiturage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_co_voiturage_show', methods: ['GET'])]
    public function show(CoVoiturage $coVoiturage,int $id): Response
    {
        if ($coVoiturage->getId() !== $id) {
            throw $this->createNotFoundException();
        }

        // Get car information
        $carInfo = "Welcome to Trippie \n here's some information about your car pool \n"."\n".
        "id: " . $coVoiturage->getId() . "\n" . 
        "Departure: " . $coVoiturage->getDepart() . "\n" .
        "Destination: " . $coVoiturage->getDestination() . "\n" .
        "Date of Departure: " . $coVoiturage->getDateDep()->format('Y-m-d H:i:s') . "\n" .
        "Number of seats: " . $coVoiturage->getNmbrPlace() . "\n" ;
       /* "Driver's Name: " . $coVoiturage->getDriverName() . "\n" .
        "Driver's Contact: " . $coVoiturage->getDriverContact() . "\n" .
        "Car Type: " . $coVoiturage->getCarType() . "\n" .
        "Car Plate Number: " . $coVoiturage->getCarPlateNumber() . "\n" ;*/
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
        return $this->render('co_voiturage/show.html.twig', [
            'co_voiturage' => $coVoiturage,
            'qr' => $qrCode->getDataUri(),
        ]);
    }

    #[Route('chauffeur/{id_ch}/profilch/index/{id}/', name: 'app_co_voiturage_show_front', methods: ['GET','POST'])]
    public function showFront(CoVoiturage $coVoiturage, SessionInterface $session,int $id_ch): Response
    { 
        
        $entityManager = $this->getDoctrine()->getManager();
        $chauffeur = $entityManager->getRepository(Chauffeur::class)->find($id_ch);
        
    if (!$chauffeur) {
        throw $this->createNotFoundException('Chauffeur non trouvé pour l\'ID: '.$chauffeur->getIdCh());
    }
        return $this->render('co_voiturage/showFront.html.twig', [
            'chauffeur' => $chauffeur,
            'id_ch' => $id_ch,
            'co_voiturage' => $coVoiturage,

        ]);
    }
    #[Route('/client/{id_client}/profilcl/index/client/{id}', name: 'app_co_voiturage_show_frontclient', methods: ['GET','POST'])]
    public function showFrontclient(CoVoiturage $coVoiturage, int $id_client,int $id): Response
    {
        $coVoiturageRepo = $this->getDoctrine()->getRepository(CoVoiturage::class);
        $coVoiturage = $coVoiturageRepo->find($id);
        $userRepository = $this->getDoctrine()->getRepository(Client::class);
        $client = $userRepository->find($id_client);
        return $this->render('co_voiturage/showFrontClient.html.twig', [
            'id_client'=> $id_client,
            'client'=> $client,
            'id'=>$id,
            'co_voiturage' => $coVoiturage,
        ]);
    }



    #[Route('/{id}/edit', name: 'app_co_voiturage_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CoVoiturage $coVoiturage, CoVoiturageRepository $coVoiturageRepository): Response
    {
        $form = $this->createForm(CoVoiturageType::class, $coVoiturage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coVoiturageRepository->save($coVoiturage, true);

            return $this->redirectToRoute('app_co_voiturage_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('co_voiturage/edit.html.twig', [
            'co_voiturage' => $coVoiturage,
            'form' => $form,
        ]);
    }

    #[Route('chauffeur/{id_ch}/profilch/index/{id}/edit', name: 'app_co_voiturage_edit_front', methods: ['GET', 'POST'])]
    public function editfront(Request $request, CoVoiturage $coVoiturage, CoVoiturageRepository $coVoiturageRepository, SessionInterface $session,int $id_ch): Response
    {

        $userRepository = $this->getDoctrine()->getRepository(Chauffeur::class);
        $chauffeur = $userRepository->find($id_ch);
        $form = $this->createForm(CoVoiturageType::class, $coVoiturage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coVoiturageRepository->save($coVoiturage, true);

            return $this->redirectToRoute('app_co_voiturage_index_fornt', ['id_ch'=>$id_ch], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('co_voiturage/editfront.html.twig', [
            'chauffeur'=> $chauffeur,
            'id_ch' => $id_ch,
            'co_voiturage' => $coVoiturage,
            'form' => $form,
        ]);
    }
    #[Route('/{id}', name: 'app_co_voiturage_delete', methods: ['POST'])]
    public function delete(Request $request, CoVoiturage $coVoiturage, CoVoiturageRepository $coVoiturageRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $coVoiturage->getId(), $request->request->get('_token'))) {
            $coVoiturageRepository->remove($coVoiturage, true);
        }

        return $this->redirectToRoute('app_co_voiturage_index', [], Response::HTTP_SEE_OTHER);
    }
    //chauffeur 
    #[Route('/chauffeur/{id}', name: 'app_co_voiturage_delete2', methods: ['POST'])]
    public function deleteFront(Request $request, CoVoiturageRepository $coVoiturageRepository, SessionInterface $session, int $id): Response
    {
        $coVoiturage = $coVoiturageRepository->find($id);
        $id_ch = $coVoiturage->getIdCh();
        if ($this->isCsrfTokenValid('delete' . $coVoiturage->getId(), $request->request->get('_token'))) {
            $coVoiturageRepository->remove($coVoiturage, true);
        }

        return $this->redirectToRoute('app_co_voiturage_index_fornt', ['id_ch'=>$id_ch], Response::HTTP_SEE_OTHER);
    }


    #[Route('/dashboard/stat', name: 'stat', methods: ['POST', 'GET'])]
    public function VoitureStatistics(CoVoiturageRepository $repo): Response
    {
        $total = $repo->countByLibelle('Aryanah') +
            $repo->countByLibelle('Bizerte') +
            $repo->countByLibelle('Beja') +
            $repo->countByLibelle('Tunis') +
            $repo->countByLibelle('Sfax') +
            $repo->countByLibelle('Kairouan') +
            $repo->countByLibelle('Jandouba') +
            $repo->countByLibelle('Ben Arous') +
            $repo->countByLibelle('Manouba') +
            $repo->countByLibelle('Gabes') +
            $repo->countByLibelle('Nabeul') +
            $repo->countByLibelle('Zaghouan');

        $AryanahCount = $repo->countByLibelle('Aryanah');
        $BizerteCount = $repo->countByLibelle('Bizerte');
        $BejaCount = $repo->countByLibelle('Beja');
        $TunisCount = $repo->countByLibelle('Tunis');
        $SfaxCount = $repo->countByLibelle('Sfax');
        $KairouanCount = $repo->countByLibelle('Kairouan');
        $JandoubaCount = $repo->countByLibelle('Jandouba');
        $Ben_ArousCount = $repo->countByLibelle('Ben Arous');
        $ManoubaCount = $repo->countByLibelle('Manouba');
        $NabeulCount = $repo->countByLibelle('Nabeul');
        $ZaghouanCount = $repo->countByLibelle('Zaghouan');
        $GabesCount = $repo->countByLibelle('Gabes');

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




        $total = $repo->countByLibelle2('Aryanah') +
            $repo->countByLibelle2('Bizerte') +
            $repo->countByLibelle2('Beja') +
            $repo->countByLibelle2('Tunis') +
            $repo->countByLibelle2('Sfax') +
            $repo->countByLibelle2('Kairouan') +
            $repo->countByLibelle2('Jandouba') +
            $repo->countByLibelle2('Ben Arous') +
            $repo->countByLibelle2('Manouba') +
            $repo->countByLibelle2('Gabes') +
            $repo->countByLibelle2('Nabeul') +
            $repo->countByLibelle2('Zaghouan');

        $AryanahCount2 = $repo->countByLibelle2('Aryanah');
        $BizerteCount2 = $repo->countByLibelle2('Bizerte');
        $BejaCount2 = $repo->countByLibelle2('Beja');
        $TunisCount2 = $repo->countByLibelle2('Tunis');
        $SfaxCount2 = $repo->countByLibelle2('Sfax');
        $KairouanCount2 = $repo->countByLibelle2('Kairouan');
        $JandoubaCount2 = $repo->countByLibelle2('Jandouba');
        $Ben_ArousCount2 = $repo->countByLibelle2('Ben Arous');
        $ManoubaCount2 = $repo->countByLibelle2('Manouba');
        $NabeulCount2 = $repo->countByLibelle2('Nabeul');
        $ZaghouanCount2 = $repo->countByLibelle2('Zaghouan');
        $GabesCount2 = $repo->countByLibelle2('Gabes');

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

        return $this->render('voiture/stat.html.twig', [
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

        ]);
    }

    #[Route('/co/voiturage/search', name: 'searchCov', methods: ['GET', 'POST'])]
    public function search2(Request $request, CoVoiturageRepository $repo): Response
    {
        $query = $request->query->get('query');
        $id = $request->query->get('id');
        $depart = $request->query->get('depart');
        $destination = $request->query->get('destination');
        $nmbr_place = $request->query->get('nmbr_place');

        $coVoiturage = $repo->advancedSearch($query, $id, $depart, $destination, $nmbr_place);

        return $this->render('co_voiturage/index.html.twig', [
            'co_voiturages' => $coVoiturage,
        ]);
    }






    #[Route('/co/voiturage/search2', name: 'searchvovrahali', methods: ['GET'])]
    public function search(Request $request, CoVoiturageRepository $coVoiturageRepository): Response
    {
        $searchQuery = $request->query->get('query');
        $sort = $request->query->get('sort');
        $order = $request->query->get('order', 'asc');

        if ($searchQuery) {
            $coVoiturage = $coVoiturageRepository->findByCoVoiturage($searchQuery);
        } else {
            $coVoiturage = $coVoiturageRepository->findAll();
        }

        return $this->render('co_voiturage/index.html.twig', [
            'co_voiturages' => $coVoiturage,
        ]);
    }

    #[Route('/co/voiturage/tricroi', name: 'tri', methods: ['GET','POST'])]
    public function triCroissant(CoVoiturageRepository $CoVoiturageRepository): Response
{
    $coVoiturage = $CoVoiturageRepository->findAllSorted();

    return $this->render('co_voiturage/index.html.twig', [
        'co_voiturages' => $coVoiturage,
    ]);
}

//admin
#[Route('/co/voiturage/Affichelist', name: 'app_voitureaffiche')]
public function Affiche(CoVoiturageRepository $repository, PaginatorInterface $paginator, Request $request)
{
    $coVoiturage = $repository->findall();
    $coVoiturage = $paginator->paginate(
        $coVoiturage, /* query NOT result */
        $request->query->getInt('page', 1),
        5
    );
    return $this->render('co_voiturage/index.html.twig', [
        'co_voiturages' => $coVoiturage,
    ]);
}

#[Route('client/{id_client}/profilcl/index/client/MyCarPool', name: 'app_co_voiturage_My_cov', methods: ['GET'])]
public function My_carsPool(Request $request, CoVoiturageRepository $coVoiturageRepository): Response
{
   
        $userRepository = $this->getDoctrine()->getRepository(Client::class);
        $client = $userRepository->find($id_client);
        $coVoiturage = $coVoiturageRepository->findById_client($id_client);

        return $this->render('co_voiturage/indexFront2.html.twig', [
            'id_client' => $id_client,
            'client' => $client,
            'co_voiturages' => $coVoiturage,
        ]);
}


}
