<?php

namespace App\Service;

use App\Repository\ProductsRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LineItems
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function getStripeLineItems(SessionInterface $session, ProductsRepository $productsRepository)
    {
        $panier = $this->session->get('panier', []);
        
        $lineItems = [];

        foreach($panier as $id => $quantity)
        {
            $product = $productsRepository->find($id);
            
            $line = [
                    'price_data' => [
                        'currency' => 'eur',
                        'unit_amount' => $product->getPrice(),
                        'product_data' => [
                            'name' => $product->getTitle(),
                        ],
                    ],
                    'quantity' => $quantity,
                ];

            $lineItems[] = $line;
        }

        return $lineItems;
    }
}