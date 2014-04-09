<?php

namespace HelloDi\RetailerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('HelloDiRetailerBundle:Default:index.html.twig', array('name' => $name));
    }
}
