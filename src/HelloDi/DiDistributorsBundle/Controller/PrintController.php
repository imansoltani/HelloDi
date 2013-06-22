<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

class PrintController extends Controller
{

public  function RetailerStatementAction()
{

    return $this->render('HelloDiDiDistributorsBundle:Print:SaleStatementPrint.html.twig');

}

}
