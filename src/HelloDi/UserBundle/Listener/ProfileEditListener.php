<?php

namespace HelloDi\UserBundle\Listener;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\FOSUserEvents;
use HelloDi\CoreBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProfileEditListener implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::PROFILE_EDIT_COMPLETED => 'onProfileEditCompleted',
        );
    }

    public function onProfileEditCompleted(FilterUserResponseEvent $event)
    {
        /** @var User $user */
        $user = $event->getUser();

        $event->getRequest()->getSession()->set('_locale',$user->getLanguage());
        $event->getRequest()->setLocale($user->getLanguage());
    }
}