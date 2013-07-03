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
        return 200;
    }

}
