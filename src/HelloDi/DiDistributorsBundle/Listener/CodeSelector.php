<?php

namespace HelloDi\DiDistributorsBundle\Listener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use HelloDi\DiDistributorsBundle\Entity\Code;
use HelloDi\DiDistributorsBundle\Entity\Item;

class CodeSelector
{
    private $doctrine;

    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function lookForAvailableCode(Item $item)
    {
        $em = $this->doctrine->getManager();

        $code = $em->getRepository('HelloDiDiDistributorsBundle:Code')->findOldestAvailableCodeByItem($item);

        if ($code) {
            $code->setStatus(0);
        }

        return $code;
    }
}
