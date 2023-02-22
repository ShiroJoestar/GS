<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClientController extends AbstractController
{
    #[Route('/client', name: 'app_client')]
    public function index(ClientRepository $clientRepository): Response
    {
        
        $clients = $clientRepository->findAll();
        return $this->render('client/list_client.html.twig', [
            'controller_name' => 'ClientController',
            'clients' => $clients,
        ]);
    }

    #[Route('/create_client', name: 'create_client')]
    public function add(Request $request, EntityManagerInterface $entityManagerInterface): Response
    {
        $client = new Client();
        $form = $this->createForm(
            ClientType::class,
            $client
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            dump($form->getData());

            $client->setName($form->getData()->getName());
            $client->setAdress($form->getData()->getAdress());
            $client->setTelephone($form->getData()->getTelephone());

            $entityManagerInterface->persist($client);
            $entityManagerInterface->flush();

            return $this->redirectToRoute('app_client');
           
        }

        return $this->render('client/create_client.html.twig', [
            'form' => $form,
        ]);
    }

}
