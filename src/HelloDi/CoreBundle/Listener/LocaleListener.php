<?php
namespace HelloDi\CoreBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class LocaleListener
 * @package HelloDi\CoreBundle\Listener
 */
class LocaleListener
{
    /**
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType())
            return;

        $request = $event->getRequest();
        $locale = $request->getSession()->get('_locale');
        $request->setLocale($locale);
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onLogin(InteractiveLoginEvent $event)
    {
        $session = $event->getRequest()->getSession();
        $user = $event->getAuthenticationToken()->getUser();

        $session->set('_locale', $user->getLanguage());
        $session->set('ExceptionUser',$user->getUsername());
    }
}