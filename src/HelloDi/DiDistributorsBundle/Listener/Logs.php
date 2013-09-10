<?php

namespace HelloDi\DiDistributorsBundle\Listener;
use Doctrine\ORM\EntityManager;
use HelloDi\DiDistributorsBundle\Entity\Exceptions;
use HelloDi\DiDistributorsBundle\Entity\Log;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\Tests\Controller;
use Symfony\Component\HttpKernel\Tests\Logger;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;


class Logs
{
    private $em;
    private $session;
    private $request;
    public function __construct(EntityManager $entityManager,$session,Request $request)
    {
        $this->em= $entityManager;
        $this->session=$session;
        $this->request=$request;

    }

    public function OnWritLog(GetResponseEvent $event)
    {
        $log=new Log();
        $date=new \DateTime('now');
        $user=$this->session->get('ExceptionUser')?$this->session->get('ExceptionUser'):'out of system';
        $qb=$this->em->createQueryBuilder();
            $qb->select('Lgs')
             ->from('HelloDiDiDistributorsBundle:Log','Lgs')
              ->where($qb->expr()->like('Lgs.User',$qb->expr()->literal($user)))
              ->Where($qb->expr()->like('Lgs.Path',$qb->expr()->literal($this->request->getPathInfo())))
              ->andWhere('Lgs.Date = :date')->setParameter('date',$date);
     $qb=$qb->getQuery();
        if(count($qb->getResult())==0)
        {
        $log->setDate($date);
        $log->setController($this->request->get('_controller'));
        $log->setPath($this->request->getPathInfo());
        $log->setUser($user);
        $this->em->persist($log);
        $this->em->flush();
        }

    }




}
