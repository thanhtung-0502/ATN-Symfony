<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CustomerController extends AbstractController
{
    #[Route('/customer', name: 'app_customer')]
    public function lisAction(ManagerRegistry $doctrine): Response
    {
        $products = $doctrine->getRepository( 'App\Entity\Product')->findAll();

        return $this->render('customer/index.html.twig', ['products' => $products
        ]);
    }

    #[Route('/customer/details/{id}', name: 'customer_details')]
    public function detailsAction(ManagerRegistry $doctrine, $id){
        $products = $doctrine->getRepository( 'App\Entity\Product')->find($id);
        return $this->render('customer/details.html.twig', ['products' => $products]);
    }

}
