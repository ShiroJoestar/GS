<?php

namespace App\Controller;

use App\Entity\Stock;
use App\Form\StockType;
use App\Repository\StockRepository;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StockController extends AbstractController
{
    #[Route('/stock', name: 'app_stock')]
    public function index(StockRepository $stockRepository): Response
    {

        $products = $stockRepository->findLatestStockForEachProduct();
        dump($products);
        return $this->render('stock/list_stock.html.twig', [
            'products' => $products,
            'controller_name' => 'StockController',
        ]);
    }

    #[Route('/create_stock', name: 'create_stock')]
    public function add(Request $request,StockRepository $stockRepository, EntityManagerInterface $entityManagerInterface): Response
    {
        $stock = new Stock();


        $form = $this->createForm(StockType::class, $stock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $stockToChange = $stockRepository->findLatestStockForOneProduct($form->getData()->getProduct());
            dump($stockToChange);
            $stock->setQuantity($form->getData()->getQuantity() + $stockToChange[0]->getQuantity());
            $stock->setUpdatedAt(new \DateTimeImmutable("now", new DateTimeZone('Europe/Moscow')));
            
            $stock->setProduct($form->getData()->getProduct()); 
            dump($stock);

            $entityManagerInterface->persist($stock);
            $entityManagerInterface->flush();
            
            return $this->redirectToRoute('app_stock');
        }


        return $this->render('stock/create_stock.html.twig', [
            'form' => $form,
        ]);
    }
}
