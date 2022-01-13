<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Entity\Products;
use Stripe\Checkout\Session;
use App\Repository\ProductsRepository;
use App\Service\LineItems;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/cart', name: 'cart_')]
class CartController extends AbstractController
{
    // 1 - Creation de la page panier
    #[Route('/', name: 'index')]
    public function index(SessionInterface $session, ProductsRepository $productsRepository)
    {
        // on récupere la session du panier
        $panier = $session->get("panier", []);

        // On initialise les données du panier (dataPanier) et le total
        $dataPanier = [];
        $total = 0;

        //  on determine chaque panier selon $id et la quantite selon $id
        foreach($panier as $id => $quantite)
        {
            // les produits
            $product = $productsRepository->find($id);

            // les données du panier
            $dataPanier[] = 
            [
                "produit" => $product,
                "quantite" => $quantite
            ];

            // le total
            $total += $product->getPrice() * $quantite;
        }

        // la page du panier
        return $this->render('cart/index.html.twig', compact("dataPanier", "total"));
    }

    // 2 - Bouton plus
    #[Route('/add/{id}', name: 'add')]
    public function add(Products $product, SessionInterface $session)
    {
        // On récupère la session du panier actuel
        $panier = $session->get("panier", []);

        // On récupère l'id
        $id = $product->getId();

        // si le panier n'est pas vide selon id
        if(!empty($panier[$id]))
        {
            $panier[$id]++;
        }else
        {
            $panier[$id] = 1;
        }

        // On sauvegarde la session
        $session->set("panier", $panier);

        // redirection vers la page panier
        return $this->redirectToRoute("cart_index");
    }

    // 3 - Bouton moins
    #[Route('/remove/{id}', name: 'remove')]
    public function remove(Products $product, SessionInterface $session)
    {
        // On récupère la session du panier actuel
        $panier = $session->get("panier", []);

        // on recupere l'id
        $id = $product->getId();

        // si le panier n'est pas vide
        if(!empty($panier[$id]))
        {
            // si le panier > 1
            if($panier[$id] > 1)
            {
                $panier[$id]--;
            }
            // sinon on supprimer le panier
            else
            {
                unset($panier[$id]);
            }
        }

        // On sauvegarde la session
        $session->set("panier", $panier);

        // redirection vers la page panier
        return $this->redirectToRoute("cart_index");
    }

    // 4 - Supprimer un article dans le panier
    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Products $product, SessionInterface $session)
    {
        // On récupère la session du panier actuel
        $panier = $session->get("panier", []);

        // on recupere l'id
        $id = $product->getId();

        // si le panier n'est pas vide
        if(!empty($panier[$id]))
        {
            unset($panier[$id]);
        }

        // On sauvegarde la session
        $session->set("panier", $panier);

        // redirection vers la page panier
        return $this->redirectToRoute("cart_index");
    }

    // 5 - Vider le panier
    #[Route('/delete', name: 'delete_all')]
    public function deleteAll(SessionInterface $session)
    {
        // supprimer tous les articles dans le panier
        $session->remove("panier");

        // redirection vers la page panier
        return $this->redirectToRoute("cart_index");
    }

    // ----------------------------STRIPE---------------------------------

    #[Route('/checkout', name: 'checkout')]
    public function checkout(LineItems $lineItems ,Products $product, SessionInterface $session, ProductsRepository $productsRepository)
    {
        
        // $panier = $session->get("panier", []);
        // $cart = $productsRepository->findOneBy(['user' => $this->getUser(), 'status' => 'active']);

        Stripe::setApiKey('sk_test_51KG0R8B70WhTmRhmtnjAaylND1ngWwYozes0xzcDaTswo3LHbbcFzEqrzNlEiNA8uT15muemkhKGENo1SxUgIMsy00WTJxx7p8');
		
		$session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $this->generateUrl('success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->redirect($session->url, 303);
    }

    

    #[Route('/success', name: 'success')]
    public function successUrl(): Response
    {
        return $this->render('cart/success.html.twig', []);
    }


    #[Route('/cancel', name: 'cancel')]
    public function cancelUrl(): Response
    {
        return $this->render('cart/cancel.html.twig', []);
    }

}


