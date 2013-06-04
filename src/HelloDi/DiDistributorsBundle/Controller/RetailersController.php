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
}