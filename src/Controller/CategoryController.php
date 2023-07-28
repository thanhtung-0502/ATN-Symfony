<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Config\Monolog\persistent;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function listAction(ManagerRegistry $doctrine): Response
    {
        $categories = $doctrine->getRepository( 'App\Entity\Category')->findAll();

        return $this->render('category/index.html.twig', ['categories' => $categories
        ]);
    }

    #[Route('/category/create', name: 'category_create', methods: ['GET', 'POST'])]
    public function createAction(ManagerRegistry $doctrine,Request $request)
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $em->persist($category);
            $em->flush();

            $this->addFlash(
                'notice',
                'Category Added'
            );
            return $this->redirectToRoute('app_category');
        }
        return $this->renderForm('category/create.html.twig', ['form' => $form,]);
    }
}
