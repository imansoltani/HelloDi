<?php

namespace HelloDi\DiDistributorsBundle\Listener;
use Doctrine\ORM\EntityManager;
use HelloDi\DiDistributorsBundle\Entity\Exceptions;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\Tests\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;


class Exception
{
    private $em;
    private $session;
    private $kernel;
    public function __construct(EntityManager $entityManager,$session,Kernel $kernel)
    {
        $this->em= $entityManager;
        $this->session=$session;
        $this->kernel=$kernel;

    }

    public function onWriteException(GetResponseForExceptionEvent $event)
    {


        $exception = $event->getException();

        $description = sprintf(
            'This Error says: %s ',
            $exception->getMessage()
            );

        $exceptions=new Exceptions();
        $exceptions->setDate(new \DateTime('now'));
        $exceptions->setDescription($description);
        $exceptions->setUsername($this->session->get('ExceptionUser')?$this->session->get('ExceptionUser'):'out of system');
        $this->em->persist($exceptions);
        $this->em->flush($exceptions);

    }




}
