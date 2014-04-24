<?php

namespace HelloDi\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('HelloDiCoreBundle:Default:index.html.twig', array('name' => $name));
    }
}
