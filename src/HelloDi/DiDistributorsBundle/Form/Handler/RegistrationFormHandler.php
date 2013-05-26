<?php

namespace HelloDi\DiDistributorsBundle\Form\Handler;

use FOS\UserBundle\Form\Handler\RegistrationFormHandler as BaseHandler;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;

class RegistrationFormHandler extends BaseHandler
{
    private $security;

    public function __construct(FormInterface $form, Request $request, UserManagerInterface $userManager, MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator,SecurityContext $security)
    {
        $this->security = $security;
        parent::__construct($form, $request, $userManager, $mailer, $tokenGenerator);
    }

    protected function onSuccess(UserInterface $user, $confirmation)
    {
        $myentity = $this->security->getToken()->getUser()->getEntiti();
        $user->setEntiti($myentity);
        parent::onSuccess($user, $confirmation);
    }
}