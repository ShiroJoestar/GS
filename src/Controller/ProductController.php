<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Stock;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();
        return $this->render('product/list_product.html.twig', [
            'controller_name' => 'ProductController',
            'products' => $products,
        ]);
    }

    #[Route('/create_product', name: 'create_product')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image file upload
            $imageFile = $form->get('image')->getData();

            
            list($width, $height) = getimagesize($imageFile);
            $src = imagecreatefromjpeg($imageFile);

            // Set the new dimensions
            $newWidth = 300;
            $newHeight = 300;

            // Create a new image with the new dimensions
            $dst = imagecreatetruecolor($newWidth, $newHeight);

            // Resize the image
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Output the resized image
            header('Content-Type: image/jpeg');
            
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('image_product_directory'),
                    $newFilename
                );
                $product->setFilePath($newFilename);

            }
           
            $product->setCreatedAt(new \DateTime);
            $product->setUpdatedAt(new \DateTime("now", new DateTimeZone('Europe/Moscow')));
            $entityManager->persist($product);


            $stock = new Stock();

            $stock->setProduct($product);
            $stock->setQuantity(0);
            $stock->setUpdatedAt(new \DateTimeImmutable("now", new DateTimeZone('Europe/Moscow')));
            $entityManager->persist($stock);
            $entityManager->flush();

            return $this->redirectToRoute('app_product');

           
    
          
        }
    
        return $this->render('product/create_product.html.twig', [
            'form' => $form,
        ]);
    }

}
