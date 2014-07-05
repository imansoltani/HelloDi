<?php

namespace HelloDi\CoreBundle\Listener;

use Doctrine\ORM\EntityManager;
use HelloDi\CoreBundle\Entity\Exceptions;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * Class Exception
 * @package HelloDi\CoreBundle\Listener
 */
class Exception
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;
    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    private $session;

    /**
     * @param EntityManager $entityManager
     * @param Session $session
     */
    public function __construct(EntityManager $entityManager, Session $session)
    {
        $this->em = $entityManager;
        $this->session = $session;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onWriteException(GetResponseForExceptionEvent $event)
    {
//        $exception = $event->getException();
//        $description = sprintf('This Error says: %s ', $exception->getMessage());
//        $exceptions = new Exceptions();
//        $exceptions->setDate(new \DateTime('now'));
//        $exceptions->setDescription($description);
//        $exceptions->setUsername($this->session->get('ExceptionUser') ? $this->session->get('ExceptionUser') : 'out of system');
//        $this->em->persist($exceptions);
//        $this->em->flush($exceptions);
    }
}
