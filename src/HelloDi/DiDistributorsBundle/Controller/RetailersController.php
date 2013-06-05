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

    public function ShopCallingAction(){
        return $this->render('HelloDiDiDistributorsBundle:Retailers:ShopCode.html.twig');
    }

    public function DmtuAction(){
        return $this->render('HelloDiDiDistributorsBundle:Retailers:ShopDmtu.html.twig');
    }
}