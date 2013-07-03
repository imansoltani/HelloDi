<?php

namespace HelloDi\DiDistributorsBundle\Listener;

use Doctrine\Bundle\DoctrineBundle\Registry;

class GetOrderNumber
{
    private $doctrine;

    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;

    }

    public function GetOrderNumber()
    {
        $em = $this->doctrine->getManager();
        $code = $em->getRepository('HelloDiDiDistributorsBundle:Setting')->find(1)->getOrderId();
        return $code;
    }

}
