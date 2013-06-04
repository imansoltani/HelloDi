<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


class RetailersController extends Controller
{
    public function dashboardAction()
    {
        return $this->render('HelloDiDiDistributorsBundle:Retailers:dashboard.html.twig');
    }

    public function ShopCodeAction(){

        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder()
            ->select('Entity')
            ->from('HelloDiDiDistributorsBundle:Item','Entity')
            ->getQuery();

            /,  $products = $qb->getResult();

        return $this->render('HelloDiDiDistributorsBundle:Retailers:ShopCode.html.twig',array('products'=>$products));
    }

    public function ShopDmtuAction(){
        return $this->render('HelloDiDiDistributorsBundle:Retailers:ShopDmtu.html.twig');
    }

}