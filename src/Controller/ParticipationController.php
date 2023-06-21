<?php

namespace App\Controller;

use App\Entity\Participation;
use App\Entity\CoVoiturage;
use App\Entity\Client;
use App\Form\CoVoiturageType;
use App\Form\ParticipationType;
use App\Repository\ParticipationRepository;
use App\Repository\CoVoiturageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use Symfony\Component\Mailer\Bridge\Google\Transport;
use Symfony\Component\Form\FormTypeInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use SebastianBergmann\Environment\Console;
use Knp\Component\Pager\PaginatorInterface;
use Dompdf\Dompdf;



#[Route('/participation')]
class ParticipationController extends AbstractController
{
    #[Route('/', name: 'app_participation_index', methods: ['GET'])]
    public function index(ParticipationRepository $participationRepository, PaginatorInterface $paginator, Request $request): Response
    {
        // Retrieve all participations from the database
        $participations = $participationRepository->findAll();

        // Paginate the query result
        $participations = $paginator->paginate(
            $participations, // Pass in the query, not the result
            $request->query->getInt('page', 1),
            5
        );

        // Pass the paginated participations to the template
        return $this->render('participation/index.html.twig', [
            'participations' => $participations,
        ]);
    }


    #[Route('client/{id_client}/profilcl/participate/{id}', name: 'app_participate', methods: ['GET', 'POST'])]
    public function participate(Request $request, CoVoiturageRepository $coVoiturageRepository, ParticipationRepository $participationRepository, int $id, int $id_client): Response
    {
        $cov = $coVoiturageRepository->find($id);
        $userRepository = $this->getDoctrine()->getRepository(Client::class);
        $client = $userRepository->find($id_client);
        
        if (!$cov) {
            throw $this->createNotFoundException('The CoVoiturage does not exist');
        }

        $participation = new Participation();

        $form = $this->createForm(ParticipationType::class, $participation);


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $cov->getNmbrPlace() >= $participation->getNmbrPlacePart()) {
           
            try {
                $email = (new Email())
                    ->from('symfonycopte822@gmail.com')
                    ->to('aymen.rahali@esprit.tn')
                    ->subject('Car Pool Participation Confirmation')
                    ->text('        Dear user Mr.{{ user name }},

                    Thank you for choosing Trippie. 
                    We are pleased to confirm your Participation for ' . $participation->getNmbrPlacePart() . ' personne(s) from ' . $cov->getDepart() . ' to ' . $cov->getDestination() . ' at ' . $cov->getDateDep()->format('Y-m-d H:i:s') . ' . 
                    If you have any additional questions or special requests, please do not hesitate to contact us.
                    We look forward to serving you and hope you have a safe and enjoyable rental experience with us.
                    
                    Best regards,
        
                    Trippie');

                $transport = new GmailSmtpTransport('symfonycopte822@gmail.com', 'cdwgdrevbdoupxhn');
                $mailer = new Mailer($transport);
                $mailer->send($email);
            } catch (TransportExceptionInterface $e) {
                // Handle any errors that occur during email sending
                $this->addFlash('error', 'An error occurred while sending the email');
                return $this->redirectToRoute('app_co_voiturage_index_client', ['id_client' => $id_client], Response::HTTP_SEE_OTHER);
            }

            $participation->setIdClient($client);
            $participation->setIdCo($cov);
            $participationRepository->save($participation, true);
            $cov->setNmbrPlace($cov->getNmbrPlace() - $participation->getNmbrPlacePart());
            $em = $this->getDoctrine()->getManager();
            $em->persist($participation);
            $em->flush();
            //$participation->send_msg('+21692554097');



            return $this->redirectToRoute('app_co_voiturage_index_client',['id_client' => $id_client]);
        }

        return $this->render('participation/newfront.html.twig', [
            'id_client'=>$id_client,
            'client'=>$client,   
            'cov' => $cov,
            'participation'=> $participation,
            'form' => $form->createView(),
        ]);
    }



    #[Route('/new', name: 'app_participation_new', methods: ['GET', 'POST'])]
    public function new2(Request $request, ParticipationRepository $participationRepository): Response
    {
        $participation = new Participation();
        $form = $this->createForm(ParticipationType::class, $participation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $participationRepository->save($participation, true);
            //$participation->send_msg('+21692554097');

            return $this->redirectToRoute('app_participation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('participation/new.html.twig', [
            'participation' => $participation,
            'form' => $form,
        ]);
    }
    #[Route('index/client/{id}', name: 'app_participation_show', methods: ['GET'])]
    public function show(Participation $participation): Response
    {
        
        return $this->render('participation/show.html.twig', [
            'participation' => $participation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_participation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Participation $participation, ParticipationRepository $participationRepository): Response
    {
        $form = $this->createForm(ParticipationType::class, $participation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $participationRepository->save($participation, true);

            return $this->redirectToRoute('app_participation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('participation/edit.html.twig', [
            'participation' => $participation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_participation_delete', methods: ['POST'])]
    public function delete(Request $request, Participation $participation, ParticipationRepository $participationRepository, CoVoiturageRepository $coVoiturageRepository, int $id): Response
    {
        $cov = $coVoiturageRepository->find($id);
        
        if($cov) { // Check if $cov is not null before accessing its methods
            $cov->setNmbrPlace($cov->getNmbrPlace() + $participation->getNmbrPlacePart());
            $em = $this->getDoctrine()->getManager();
            $em->persist($cov); // Fix the error by persisting $cov instead of $participation
            $em->flush();
        }
    
        if ($this->isCsrfTokenValid('delete' . $participation->getId(), $request->request->get('_token'))) {
            $participationRepository->remove($participation, true);
        }
    
        return $this->redirectToRoute('app_participation_index', [], Response::HTTP_SEE_OTHER);
    }
    

    #[Route('/participation/search', name: 'searchpartrahali', methods: ['GET', 'POST'])]
    public function search2(Request $request, CoVoiturageRepository $repo, Client $id_client): Response
    {
        $query = $request->query->get('query');
        $id = $request->query->get('id');
        $nmbrPlacePart = $request->query->get('Nmbr_place_part');
        $id_co = $request->query->get('id_co');
        $id_client = $request->query->get('id_client');

        $participation = $repo->advancedSearch($query, $id, $nmbrPlacePart, $id_co, $id_client);

        return $this->render('participation/index.html.twig', [
            'participations' => $participation,
        ]);
    }
    #[Route('/cov/exportpdf', name: 'exportpdf_cov')]
    public function exportToPdf(ParticipationRepository $repository): Response
    {
        // Récupérer les données de réservation depuis votre base de données
        $participations = $repository->findAll();

        // Créer le tableau de données pour le PDF
        $tableData = [];
        foreach ($participations as $participation) {
            $tableData[] = [

                'id' => $participation->getId(),
                'id_co' => $participation->getIdCo()->getId(),
                'nmbr_place_part' => $participation->getNmbrPlacePart(),


            ];
        }

        // Créer le PDF avec Dompdf
        $dompdf = new Dompdf();
        $html = $this->renderView('participation/export-pdf-cov.html.twig', [
            'tableData' => $tableData,
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        // Envoyer le PDF au navigateur
        $response = new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Participations.pdf"',
        ]);
        return $response;
    }
}
