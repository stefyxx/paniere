<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\ProductRepository;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(SessionInterface $session, ProductRepository $productRepository): Response
    {
        $panier =$session->get('panier', []);
        //rifaccio un paniere che ha più di un id e le quantità
        $panierWithData = [];
        foreach ($panier as $id => $quantity) {
            $panierWithData[]=[
                //'id' => $id,
                'product'=>$productRepository->find($id),        //new product()->getReposiroty()->find(id);
                'quantity' => $quantity
            ];
        }

        $total=0;
        foreach ($panierWithData as $item) {
            //totale di 1 prodotto
            $totalItem= $item['product']->getPrice() * $item['quantity'];
            //totale di tutto
            $total += $totalItem;
        }

        return $this->render('cart/index.html.twig', [
            'controller_name' => 'CartController',
            'items' => $panierWithData,
            'totale' => $total
        ]);
    }

    #[Route(path:'/panier/add/{id}', name:'cart_add')]
    public function add($id, SessionInterface $session){
        //$session = $request->getsession();

        //guarda nella session e cerca 'panier', se non trovi niente (ancora non ho scelto niente), dammi un tab vuoto
        $panier =$session->get('panier', []);

        if (!empty($panier[$id])) {
            //se riclicco sullo stesso prodotto, aumento la quantità
            $panier[$id]++;
        } else {
            //se clicco 1 volta sul prodotto, aggiungo quantità 1
            $panier[$id]=1;
        }


        $session->set('panier', $panier);

        return $this->redirectToRoute("cart_index");

    }

    #[Route(path:'/panier/remove/{id}', name:'cart_remove')]
    public function remove($id, SessionInterface $session){
        //voglio sopprimere un articolo dal paniere
        $panier = $session->get('panier', []);

        if (!empty($panier[$id])) {
            unset($panier[$id]);

        }
        $session->set('panier',$panier);
        return $this->redirectToRoute("cart_index");
    }
}
