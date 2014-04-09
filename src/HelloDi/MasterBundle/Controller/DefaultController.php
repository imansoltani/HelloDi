<?php

namespace HelloDi\MasterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('HelloDiMasterBundle:Default:index.html.twig', array('name' => $name));
    }
}
