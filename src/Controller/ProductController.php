<?php

namespace App\Controller;


use App\Entity\Product;
use App\Form\ProductType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use function Symfony\Config\Framework\Workflows\type;
use function Symfony\Config\Monolog\persistent;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product')]
    public function lisAction(ManagerRegistry $doctrine): Response
    {
        $products = $doctrine->getRepository( 'App\Entity\Product')->findAll();

        return $this->render('product/index.html.twig', ['products' => $products
        ]);
    }

    #[Route('/products/details/{id}', name: 'product_details')]
    public function detailsAction(ManagerRegistry $doctrine, $id){
        $products = $doctrine->getRepository( 'App\Entity\Product')->find($id);
        return $this->render('product/details.html.twig', ['products' => $products]);
    }

    #[Route('/products/delete/{id}', name: 'product_delete')]
    public function deleteAction(ManagerRegistry $doctrine, $id): Response
    {
        $em = $doctrine->getManager();
        $products = $em->getRepository( 'App\Entity\Product')->find($id);
        $em->remove($products);
        $em->flush();

        $this->addFlash(
            'error',
            'Product deleted'
        );
        return $this->redirectToRoute('app_product');
    }

    #[Route('/products/create', name: 'product_create', methods: ['GET', 'POST'])]
    public function createAction(ManagerRegistry $doctrine,Request $request, SluggerInterface $slugger)
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // uplpad file
            $productImage = $form->get('productImage')->getData();
            if ($productImage) {
                $originalFilename = pathinfo($productImage->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $productImage->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $productImage->move(
                        $this->getParameter('productImages_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash(
                        'error',
                        'Cannot upload'
                    );// ... handle exception if something happens during file upload
                }
                $product->setImage($newFilename);
            }else{
                $this->addFlash(
                    'error',
                    'Cannot upload'
                );// ... handle exception if something happens during file upload
            }

            $em = $doctrine->getManager();
            $em->persist($product);
            $em->flush();

            $this->addFlash(
                'notice',
                'Product Added'
            );
            return $this->redirectToRoute('app_product');
        }
        return $this->renderForm('product/create.html.twig', ['form' => $form,]);
    }


 #[Route('/product/edit/{id}', name:'product_edit')]

public function editAction(ManagerRegistry $doctrine, int $id,Request $request, SluggerInterface $slugger): Response{
    $entityManager = $doctrine->getManager();
    $product = $entityManager->getRepository(Product::class)->find($id);
    $form = $this->createForm(ProductType::class, @$product);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // uplpad file
        $productImage = $form->get('productImage')->getData();
        if ($productImage) {
            $originalFilename = pathinfo($productImage->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $productImage->guessExtension();

            // Move the file to the directory where brochures are stored
            try {
                $productImage->move(
                    $this->getParameter('productImages_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                $this->addFlash(
                    'error',
                    'Cannot upload'
                );// ... handle exception if something happens during file upload
            }
            $product->setImage($newFilename);
        }else{
            $this->addFlash(
                'error',
                'Cannot upload'
            );// ... handle exception if something happens during file upload
        }


        $em = $doctrine->getManager();
        $em->persist($product);
        $em->flush();
        return $this->redirectToRoute('app_product', [
            'id' => $product->getId()
        ]);

    }
    return $this->renderForm('product/edit.html.twig', ['form' => $form,]);
}
public function saveChanges(ManagerRegistry $doctrine,$form, $request, $product)
{
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $product->setName($request->request->get('product')['name']);
        $product->setPrice($request->request->get('product')['price']);
        $product->setDescription($request->request->get('product')['description']);
        $em = $doctrine->getManager();
        $em->persist($product);
        $em->flush();

        return true;
    }

    return false;
}



}
