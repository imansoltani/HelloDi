<?php

namespace HelloDi\DiDistributorsBundle\Listener;
use Doctrine\ORM\EntityManager;
use HelloDi\DiDistributorsBundle\Entity\Ticket;
use HelloDi\DiDistributorsBundle\Entity\User;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;


class ClearCache implements CacheClearerInterface
{
    public function clear($cacheDir)
    {
        $this->clear('app\cache');
    }

}