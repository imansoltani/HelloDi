<?php
namespace HelloDi\DiDistributorsBundle\Listener;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class SecurityListener
{
    private $security;
    private $session;

    public function __construct(SecurityContext $security, Session $session)
    {
        $this->security = $security;
        $this->session = $session;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $locale = $this->security->getToken()->getUser()->getLanguage();
        $this->session->set('_locale', ($locale?$locale:"en"));
    }
}