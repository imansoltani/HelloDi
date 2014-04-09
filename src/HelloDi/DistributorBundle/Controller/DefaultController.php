<?php

namespace HelloDi\DistributorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('HelloDiDistributorBundle:Default:index.html.twig', array('name' => $name));
    }
}
