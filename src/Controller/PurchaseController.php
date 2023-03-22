<?php

namespace App\Controller;

use App\Entity\Purchase;
use App\Entity\QuantityProduct;
use App\Entity\Stock;
use App\Form\ProductType;
use App\Form\PurchaseType;
use App\Repository\PurchaseRepository;
use App\Repository\QuantityProductRepository;
use App\Repository\StockRepository;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
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

        $purchases = $purchaseRepository->findBy(['status' => false]);
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
               
                
                array_push($koko, $product);
                
            }
            
            for ($i = 0; $i < count($koko); $i++){
                $bilanQuantity = new QuantityProduct();
                $bilanQuantity->addProduct($koko[$i]);
                $bilanQuantity->addPurchase($thepurchase);
                $bilanQuantity->setNombre($productsData[$i]['quantity']);
                dump(count($koko));
                dump($bilanQuantity);
                $entityManager->persist($bilanQuantity);
                
            }
            for ($i = 0; $i < count($koko); $i++){
                $thepurchase->addProduct($koko[$i]);
                
                $total = $total + ($koko[$i]->getPrice() * $productsData[$i]['quantity']);
            }
           

            $thepurchase->setTotal($total);
            $thepurchase->setUpdatedAt(new DateTime());
            $thepurchase->setClient($form->get('client')->getData());
            $thepurchase->setdate($form->get('date')->getData());
            $thepurchase->setPaymentMethod($form->get('paymentMethod')->getData());
            $thepurchase->setStatus(false);
            
            $entityManager->persist($thepurchase);
            $entityManager->flush();
            
            return $this->redirectToRoute("app_purchase");

            
    
          
        }
        
    
        return $this->render('purchase/create_purchase.html.twig', [
            'form' => $form,
        ]);
    }


    #[Route('/confirmCommande/{purchaseId}', name: 'confirmCommande', methods: ['POST'])]
    public function confirm(Request $request, StockRepository $stockRepository, $purchaseId, EntityManagerInterface $entityManagerInterface, PurchaseRepository $purchaseRepository){
        $purchase = $purchaseRepository->findOneby(['id' => $purchaseId]);

        if (!$purchase) {
        throw $this->createNotFoundException('No purchase found for id ' . $purchaseId);
    }

        $purchase->setStatus(true);
        
        dump($purchase->getQuantityProducts());

        $purchaseQuantityProduct = $purchase->getQuantityProducts();
        $purchaseProduct = $purchase->getProduct();


        for ($i=0; $i < count($purchaseQuantityProduct); $i++) { 
            $productStock = $purchaseProduct[$i];
            $lastStock = $stockRepository->findOneBy(['product' => $productStock], ['updatedAt' => 'DESC'], 1);
            $currentQuantity = $lastStock->getQuantity() - $purchaseQuantityProduct[$i]->getNombre(); 
            $stock = new Stock();

            $stock->setQuantity($currentQuantity);
            $stock->setProduct($productStock);
            $stock->setUpdatedAt(new DateTimeImmutable("now", new DateTimeZone('Europe/Moscow')));
            $entityManagerInterface->persist($stock);
        }
        




        $entityManagerInterface->flush();


        return $this->json(['success' => true]);
    }

    #[Route('/purchase/{id}/print', name: 'print_purchase', methods: ['GET','POST'])]
    public function print($id, PurchaseRepository $purchaseRepository, QuantityProductRepository $quantityProductRepository){

        $purchase = $purchaseRepository->findOneBy(['id' => $id]);
        dump($purchase);
        $quantityProduct = $purchase->getQuantityProducts();
        $product = $purchase->getProduct();
        dump($quantityProduct[0]->getProduct()[0]);
        dump($product);
        return $this->render('purchase/recu_purchase.html.twig', [
            'quantityProduct' => $quantityProduct,
            'product' => $product,
            'purchase' => $purchase
        ]);
    }
}
