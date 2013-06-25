<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('HelloDiDiDistributorsBundle::Home.html.twig');
    }

    public function dashboardAction()
    {
        return $this->render('HelloDiDiDistributorsBundle:Dashboard:Master_dashboard.html.twig', array('MU' => 'home'));
    }

}