<?php

namespace App\Controller;

use App\Entity\Coupon;
use App\Form\Coupon1Type;
use App\Repository\CouponRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/coupon')]
class CouponController extends AbstractController
{
    #[Route('/', name: 'app_coupon_index', methods: ['GET'])]
    public function index(Request $request, CouponRepository $couponRepository): Response
    {
        $searchQuery = $request->query->get('q');
        $sort = $request->query->get('sort');
        $order = $request->query->get('order', 'asc');
        
        if ($searchQuery) {
            $coupons = $couponRepository->findByCodeCouponOrType($searchQuery);
        } elseif ($sort === 'taux' && $order === 'asc') {
            $coupons = $couponRepository->findAllSortedByTauxReduction('ASC');
        } elseif ($sort === 'taux' && $order === 'desc') {
            $coupons = $couponRepository->findAllSortedByTauxReduction('DESC');
        } else {
            $coupons = $couponRepository->findAll();
        }
        
    
        return $this->render('coupon/index.html.twig', [
            'coupons' => $coupons,
            'sort' => $sort,
            'order' => $order,
        ]);
    }
    
    

    #[Route('/new', name: 'app_coupon_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CouponRepository $couponRepository): Response
    {
        $coupon = new Coupon();
        $form = $this->createForm(Coupon1Type::class, $coupon);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $couponRepository->save($coupon, true);

            return $this->redirectToRoute('app_coupon_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('coupon/new.html.twig', [
            'coupon' => $coupon,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_coupon_show', methods: ['GET'])]
    public function show(Coupon $coupon): Response
    {
        return $this->render('coupon/show.html.twig', [
            'coupon' => $coupon,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_coupon_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Coupon $coupon, CouponRepository $couponRepository): Response
    {
        $form = $this->createForm(Coupon1Type::class, $coupon);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $couponRepository->save($coupon, true);

            return $this->redirectToRoute('app_coupon_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('coupon/edit.html.twig', [
            'coupon' => $coupon,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_coupon_delete', methods: ['POST'])]
    public function delete(Request $request, Coupon $coupon, CouponRepository $couponRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$coupon->getid(), $request->request->get('_token'))) {
            $couponRepository->remove($coupon, true);
        }

        return $this->redirectToRoute('app_coupon_index', [], Response::HTTP_SEE_OTHER);
    }
  
}
