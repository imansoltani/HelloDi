<?php

namespace HelloDi\AggregatorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('HelloDiAggregatorBundle:Default:index.html.twig', array('name' => $name));
    }
}
