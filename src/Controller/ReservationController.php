<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Client;
use  Flasher\Prime\FlasherInterface;
use App\Form\ReservationFormType;
use App\Repository\VoitureRepository;
use App\Repository\ReservationRepository;
use Symfony\Component\Routing\RouterInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
//use Twilio\Rest\Client;



class ReservationController extends AbstractController
{
    #[Route('/reservation', name: 'app_reservation')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ReservationController.php',
        ]);
    }


//front
    #[Route('/reservation/Add/{id_client}/{id<\d+>}', name: 'app_reservation_add')]
    public function addReservation(Request $request, int $id, VoitureRepository $voitureRepository,int $id_client): Response
    {
        $userRepository = $this->getDoctrine()->getRepository(Client::class);
        $client = $userRepository->find($id_client);
        $voiture = $voitureRepository->find($id);

        $reservation = new Reservation();
        $reservation->setIdVoiture($voiture);
        $reservation->setIdClient($client);
   
        
    
        $form = $this->createForm(ReservationFormType::class, $reservation, [
            'data_class' => Reservation::class,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
           
            $startDate = $reservation->getDateDebut()->format('Y-m-d H:i:s');
            $endDate = $reservation->getDateFin()->format('Y-m-d H:i:s');
            $marque = $reservation->getIdVoiture()->getMarque();
            $email = (new Email())
                ->from('symfonycopte822@gmail.com')
                ->to('khmiri.iheb@esprit.tn')
                ->subject('Car Rental Reservation Confirmation')
                ->text('Dear user,

Thank you for choosing to rent ' . $marque . ' from Trippie. We are pleased to confirm your reservation for the ' . $startDate . ' to ' . $endDate . '.

As a reminder, please bring a valid driver\'s license and a credit card in your name when you come to pick up the car. If you have any additional questions or special requests, please do not hesitate to contact us.

We look forward to serving you and hope you have a safe and enjoyable rental experience with us.

Best regards,
Trippie');
            $transport = new GmailSmtpTransport('symfonycopte822@gmail.com', 'cdwgdrevbdoupxhn');
            $mailer = new Mailer($transport);
            $mailer->send($email);
        
            //dd($client);    
            $voiture->setEtat("reservé");
            $em = $this->getDoctrine()->getManager();
            $em->persist($reservation);
            $em->flush();

            return $this->redirectToRoute('app_reservationaffichefront',['id_client'=>$id_client]);
        }
        return $this->render('reservation/AddR.html.twig', [
            'id_client'=>$id_client,
            'client'=>$client,
            'id' => $id,
            'form' => $form->createView(),
        ]);

    }


//delete back

    #[Route('voiture/deleteReservation/{id}', name: 'app_DeleteReservation')]
    public function deleteStatique($id, ReservationRepository $repo, ManagerRegistry $doctrine): Response
    {

        $reservation = $repo->find($id);
        $reservation->getIdVoiture()->setEtat("non reservé");
        $em = $doctrine->getManager();
        $em->remove($reservation);
        $em->flush();
        return $this->redirectToRoute("app_reservationaffiche");


    }

    // clientsymfo
    #[Route('/reservation/client/Affichelist/{id_client}', name: 'app_reservationaffichefront')]
    public function Affichefront(ReservationRepository $repository, PaginatorInterface $paginator, Request $request,int $id_client)
    {
        $userRepository = $this->getDoctrine()->getRepository(Client::class);
        $reservation = $repository->findBy(['id_client' => $id_client]);
        $client = $userRepository->find($id_client);
        //$reservation = $repository->findAll();
        $reservation = $paginator->paginate(
            $reservation, /* query NOT result */
            $request->query->getInt('page', 1),
            5
        );
        return $this->render('reservation/Afficheclient.html.twig', [
            'reservation' => $reservation,
            'id_client'=>$id_client,
            'client'=>$client
        ]
    );
    }

    #[Route('/exportexcel', name: 'exportexcel')]
    public function exportacademieToExcelAction(ReservationRepository $repository, RouterInterface $router): Response
    {

        // Récupérer la liste des academies depuis votre source de données
        $reservation = $repository->findAll();

        // Créer un nouveau document Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('academie');

        // Écrire les en-têtes de colonnes
        $sheet->setCellValue('A1', 'Registration number ');
        $sheet->setCellValue('C1', 'Brand ');
        $sheet->setCellValue('E1', 'Power ');
        $sheet->setCellValue('G1', 'price per day ');
        $sheet->setCellValue('I1', 'Energy ');
        $sheet->setCellValue('K1', 'Staus ');
        $sheet->setCellValue('M1', 'Start date ');
        $sheet->setCellValue('O1', 'End date ');

        // Écrire les données des academies
        $row = 2;
        foreach ($reservation as $academie) {
            $sheet->setCellValue('A' . $row, $academie->getIdVoiture()->getMatricule());
            $sheet->setCellValue('C' . $row, $academie->getIdVoiture()->getMarque());
            $sheet->setCellValue('E' . $row, $academie->getIdVoiture()->getPuissance());
            $sheet->setCellValue('G' . $row, $academie->getIdVoiture()->getPrixJours());
            $sheet->setCellValue('I' . $row, $academie->getIdVoiture()->getEnergie());
            $sheet->setCellValue('K' . $row, $academie->getIdVoiture()->getEtat());
            $sheet->setCellValue('M' . $row, $academie->getDateDebut());
            $sheet->setCellValue('O' . $row, $academie->getDateFin());


            $row++;
        }

        // Créer une réponse HTTP pour le document Excel
        $response = new Response();

        // Écrire le document Excel dans la réponse HTTP
        $writer = new Xlsx($spreadsheet);
        $writer->save('listeReservations.xlsx');

        // Rediriger vers la page d'affichage des réservations
        $url = $router->generate('app_reservationaffiche');
        return $this->redirect($url);
    }


    //delete front
    #[Route('voiture/client/deleteReservation/{id}', name: 'app_DeleteReservation2')]
    public function deleteStatique2($id, ReservationRepository $repo, ManagerRegistry $doctrine, VoitureRepository $voitureRepository): Response
    {
        
        $reservation = $repo->find($id);
    
        $em = $doctrine->getManager();
        $reservation->getIdVoiture()->setEtat("non reservé");
        $em->remove($reservation);
        $em->flush();
        return $this->redirectToRoute("app_reservationaffichefront",['id_client'=>$reservation->getIdClient()]);
    }


//back
    #[Route('/reservation/Affichelist', name: 'app_reservationaffiche')]
    public function Affiche(ReservationRepository $repository, PaginatorInterface $paginator, Request $request)
    {

        $reservation = $repository->findAll();
        $reservation = $paginator->paginate(
            $reservation, /* query NOT result */
            $request->query->getInt('page', 1),
            5
        );


        return $this->render('reservation/Affiche.html.twig', ['reservation' => $reservation]);
    }


//front
    #[Route('/modifC/{id}', name: 'modifC')]
    public function modifC($id, ReservationRepository $repository, ManagerRegistry $doctrine, Request $request): Response
    {
        $userRepository = $this->getDoctrine()->getRepository(Client::class);
        
        $c = $repository->find($id);
        $id_client= $c->getIdClient();
        $client = $userRepository->find($id_client);
        $form = $this->createForm(ReservationFormType::class, $c);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $em = $doctrine->getManager();
            $em->flush();
            return $this->redirectToRoute("app_reservationaffichefront",['id_client'=>$id_client]);
        }
        return $this->renderForm('reservation/UpdateRfront.html.twig',
               
            [
                'id_client'=>$id_client,
                'client'=>$client,
                'form'=>$form
              
            ]
        );
    }

    //back
    #[Route('/modifC/back/{id}', name: 'modifC2')]
    public function modifC2($id, ReservationRepository $repository, ManagerRegistry $doctrine, Request $request): Response
    {
        $c = $repository->find($id);
        $form = $this->createForm(ReservationFormType::class, $c);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $em = $doctrine->getManager();
            $em->flush();
            return $this->redirectToRoute("app_reservationaffiche");
        }
        return $this->renderForm('reservation/UpdateR.html.twig',
            array("form" => $form)
        );
    }


    #[Route('/exportpdf', name: 'exportpdf')]
    public function exportToPdf(ReservationRepository $repository): Response
    {
        // Récupérer les données de réservation depuis votre base de données
        $reservations = $repository->findAll();

        // Créer le tableau de données pour le PDF
        $tableData = [];
        foreach ($reservations as $reservation) {
            $tableData[] = [
                'id' => $reservation->getId(),
                'Registration_number' => $reservation->getIdVoiture()->getMatricule(),
                'brand' => $reservation->getIdVoiture()->getMarque(),
                'price' => $reservation->getIdVoiture()->getPrixJours(),
                'etat' => $reservation->getIdVoiture()->getEtat(),
                'power' => $reservation->getIdVoiture()->getPuissance(),
                'energie' => $reservation->getIdVoiture()->getEnergie(),
                'date' => $reservation->getDateDebut()->format('Y-m-d H:i:s'),
                'date1' => $reservation->getDateFin()->format('Y-m-d H:i:s'),
            ];
        }

        // Créer le PDF avec Dompdf
        $dompdf = new Dompdf();
        $html = $this->renderView('reservation/export-pdf.html.twig', [
            'tableData' => $tableData,
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        // Envoyer le PDF au navigateur
        $response = new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="reservations.pdf"',
        ]);
        return $response;
    }

}
