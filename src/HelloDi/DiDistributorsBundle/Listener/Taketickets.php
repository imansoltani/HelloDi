<?php

namespace HelloDi\DiDistributorsBundle\Listener;
use Doctrine\ORM\EntityManager;
use HelloDi\DiDistributorsBundle\Entity\Ticket;
use HelloDi\DiDistributorsBundle\Entity\User;

class Taketickets
{
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em= $entityManager;

    }

    public function IsTake(Ticket $ticket,User $user)
    {
       $em=$this->em;
      if($ticket->getInchange()!=null)
               return true;
      if($ticket->getInchange()==null)
        {
            $ticket->setInchange($user->getId());
            $em->flush();
            return true;
        }

    }
}
