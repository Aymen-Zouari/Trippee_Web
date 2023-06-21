<?php

namespace App\Controller;


use App\Entity\Coupon;
use App\Entity\Cadeau;
use App\Entity\Reservation;
use App\Entity\Voiture;
use App\Repository\CouponRepository;
use App\Repository\CadeauRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Services\QrcodeService;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use Symfony\Component\Mailer\MailerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Client;


class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    

    #[Route('/home/roue/{id_client}', name: 'app_roue', methods:['POST','GET' ])]
    public function index2(CouponRepository $couponRepository,int $id_client): Response
    {
        $userRepository = $this->getDoctrine()->getRepository(Client::class);
        $client = $userRepository->find($id_client);
       

        
        // Get the taux and type values from the database
        $coupons = $couponRepository->findAll();
        $taux = [];
        $type=[];

        $userRepository = $this->getDoctrine()->getRepository(Client::class);
        $client = $userRepository->find($id_client);
        
        foreach ($coupons as $coupon) {
           
                
           $taux[] = $coupon->getTaux();
           $type[]=$coupon->getType(); 
        }
        shuffle($taux); // shuffle the array to randomize the order
       
        // Pass the taux and type values to the Twig template
        return $this->render('home/indexroue.html.twig', [
           'client'=>$client,
            'id_client'=>$id_client,
            'taux' => $taux,
            'type'=>$type,
            
        ]);
    }
    
    #[Route('/coupon/code_coupon/{taux}', name: 'app_code_coupon')]
    public function getCodeCouponByTaux(CouponRepository $couponRepository, $taux): Response
    {
        $coupon = $couponRepository->findOneBy(['taux' => $taux]);
    
        if (!$coupon) {
            throw $this->createNotFoundException('Coupon code not found');
        }
    
        return new Response($coupon->getCodeCoupon());
    }
    

    #[Route('/home/about/{id_client}', name: 'appllycoupon')]
public function appllycoupon (CouponRepository $couponRepository,Request $request, int $id_client,ReservationRepository $reserRepository):Response
{

    $userRepository = $this->getDoctrine()->getRepository(Client::class);
    $client = $userRepository->find($id_client);
    $id_cl= $client->getIdClient();
    
   
    $reservations = $reserRepository->findBy(['id_client' => $id_cl]);
   

foreach ($reservations as $reservation) {
    $price = $reservation->getIdVoiture()->getPrixJours();
    
    
    // Faites quelque chose avec $id_rev
}

   
    
   
    $form = $this->createFormBuilder()
    ->add('price', TextType::class, [
        'attr' => [
            'class' => 'form-control mb-3',
            'placeholder' => 'Price',
            'readonly' => true,
        ],
        'data' => $price, 
    ])
    ->add('code_coupon', TextType::class, [
        'attr' => [
            'class' => 'form-control mb-3',
            'placeholder' => 'Coupon code',
        ],
    ])
    ->add('submit', SubmitType::class, [
        'attr' => [
            'class' => 'btn btn-primary',
        ],
        'label' => 'Submit',
    ])
    ->getForm();
    
    $newPrice=0;

    if ($request->isMethod('POST'))
    {
    $form->handleRequest($request);
    
     if ($form->isSubmitted() && $form->isValid()) {
   
     $data=$form->getData();
     $discount=$this->getDoctrine()->getRepository(coupon::class);
         
     $coupon = $discount->findOneByCodeCoupon(['code_coupon'=>$data['code_coupon']]); 
     if ($coupon) {
        $expirationDate = $coupon->getDateExperatio();
        $today = new \DateTime();
    
        if ($expirationDate < $today) {
            // coupon has expired
            $this->addFlash('error', 'Coupon has expired.'); 
            echo '<script>alert("Coupon has expired.");</script>';
        } else {
            $price = $data['price'];
            if ($coupon->getNbrUtilisation() > 0) {
                $coupon->setNbrUtilisation($coupon->getNbrUtilisation() - 1);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($coupon);
                $entityManager->flush();
                if ($coupon->getNbrUtilisation() == 0) {
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->remove($coupon);
                    $entityManager->flush();
                }
            }
    
            $discountedPrice = $coupon->getTaux() * $price / 100;
            $newPrice = $price - $discountedPrice;
           
            return $this->render('home/discount.html.twig', [
                'client' => $client,
                'id_client'=>$id_client,
                'price' => $price,
                'newPrice' => $newPrice,
                'form' => $form->createView(),
            ]);
        }
    } else {
        // coupon not found
        $this->addFlash('error', 'Coupon not found.');
    }
    
    return $this->render('home/discount.html.twig', [
        'client' => $client,
        'id_client'=>$id_client,
        'form' => $form->createView(),
        'price' => $price,
        'newPrice' => $newPrice,
    ]);
    
    
}}

    return $this->render('home/discount.html.twig', [
        'client' => $client,
        'id_client'=>$id_client,
        'form' => $form->createView(),
        'price'=>$price,
        'newPrice'=>$newPrice,
      
        
    ]);   
    
}
#[Route('/home/Services/{id_client}', name: 'GIFT2')]
public function gift(CadeauRepository $cadeauRepository,int $id_client): Response
{
    $userRepository = $this->getDoctrine()->getRepository(Client::class);
    $client = $userRepository->find($id_client);
    $cadeaux = $cadeauRepository->findAll();

    return $this->render('home/gift2.html.twig', [
        'client'=>$client,
        'cadeaux' => $cadeaux,
    ]);
}    
#[Route('/home/Services/{idcadeau<\d+>}/{id_client}', name: 'gift')]   
public function sendEmail(Request $request, int $idcadeau, CadeauRepository $cadeauRepository, MailerInterface $mailer,int $id_client): Response
{
    $userRepository = $this->getDoctrine()->getRepository(Client::class);
    $client = $userRepository->find($id_client);
    $email=$client->getEmail();
  

    $cadeau = $cadeauRepository->find($idcadeau);   
    // create the email
    $email = (new Email())
                ->from('symfonycopte822@gmail.com')
                ->to($email)
                ->subject('GIFT Confirmation')
                ->html("
                <div style='background-color: #f2f2f2; padding: 20px;'>
                    <h3 style='color: #ff9900;'>Request for gift: {$cadeau->getNomCadeay()}</h3>
                    <p style='font-size: 16px;'>DESCRIPTION: {$cadeau->getDescription()}</p>
                    <p style='font-size: 14px;'><strong>PS: Get your gift from our location</strong></p>
                </div>
            ");
            if ($cadeau->getReccurence() > 0) {
                $cadeau->setReccurence($cadeau->getReccurence() - 1);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($cadeau);
                $entityManager->flush();
                if ($cadeau->getReccurence() == 0) {
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->remove($cadeau);
                    $entityManager->flush();
                }
            $transport = new GmailSmtpTransport('symfonycopte822@gmail.com', 'cdwgdrevbdoupxhn');
            $mailer = new Mailer($transport);
            $mailer->send($email);
            return $this->redirectToRoute('GIFT2',['id_client'=>$id_client]);
            }


    
}
 


}