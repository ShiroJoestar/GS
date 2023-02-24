<?php

namespace App\Controller;

use App\Entity\Purchase;
use App\Form\ProductType;
use App\Form\PurchaseType;
use App\Repository\PurchaseRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\Pure;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PurchaseController extends AbstractController
{
    #[Route('/purchase', name: 'app_purchase')]
    public function index( PurchaseRepository $purchaseRepository): Response
    {

        $purchases = $purchaseRepository->findAll();
        return $this->render('purchase/liste_purchase.html.twig', [
            'purchases' => $purchases,
        ]);
    }

    #[Route('/create_purchase', name: 'create_purchase')]
    public function add(EntityManagerInterface $entityManager, Request $request, PurchaseRepository $purchaseRepository)
    {
        $purchase = new Purchase();
        $thepurchase = new Purchase();
        $form = $this->createForm(PurchaseType::class, $purchase);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image file upload
           
            
            $data = $form->getData();
            $total = 0;
            $productsData = $form->get('product')->getData();
            $koko = [];
            for ($i = 0; $i < count($productsData); $i++){
                $productData = $productsData[$i];
                
                $product = $productData['product'];
                dump($product);
                
                array_push($koko, $product);
                
            }
            
            for ($i = 0; $i < count($koko); $i++){
                $thepurchase->addProduct($koko[$i]);
                $total = $total + ($koko[$i]->getPrice() * $productsData[$i]['quantity']);
            }
            dump($purchase);

            $thepurchase->setTotal($total);
            $thepurchase->setUpdatedAt(new DateTime());
            $thepurchase->setClient($form->get('client')->getData());
            $thepurchase->setdate($form->get('date')->getData());
            $thepurchase->setPaymentMethod($form->get('paymentMethod')->getData());
            dump($thepurchase);

            $entityManager->persist($thepurchase);
            $entityManager->flush();
            


           
    
          
        }
        
    
        return $this->render('purchase/create_purchase.html.twig', [
            'form' => $form,
        ]);
    }
}
