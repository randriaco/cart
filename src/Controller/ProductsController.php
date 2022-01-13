<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProductsController extends AbstractController
{
    /**
     * @Route("/", name="product_index")
     */
    public function index(ProductsRepository $productsRepository, PaginatorInterface $paginator, Request $request)
    {
        // utiliser tous les articles dans la BDD de table Products
        $datas = $productsRepository->findAll();

        $products = $paginator->paginate(
            $datas,
            $request->query->getInt('page', 1),
            1
        );
        
        // Page produits
        return $this->render('products/index.html.twig', 
        [
            'products' => $products
        ]);
    }
}
