<?php

namespace HelloDi\DiDistributorsBundle\Listener;

use Doctrine\ORM\EntityManager;

class GetOrderNumber
{
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;

    }

    public function GetOrderNumber()
    {
        $code = $this->em->getRepository('HelloDiDiDistributorsBundle:Setting')->find(1)->getOrderId();
        return $code;
    }

}
