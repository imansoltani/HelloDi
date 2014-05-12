<?php

namespace HelloDi\CoreBundle\Controller;

use HelloDi\CoreBundle\Form\ContactUsType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexPageAction($locale, Request $req)
    {
        $this->get('session')->set('_locale', $locale);
        $req->setLocale($locale);

        $Form = $this->createForm(new ContactUsType());
        if ($req->isMethod('POST')) {
            $Form->handleRequest($req);
            if ($Form->isValid()) {
                $data = $Form->getData();
                $mailer = $this->get('mailer');

                /** @var \Swift_Mime_Message $message */
                $message = \Swift_Message::newInstance()
                    ->setSubject('Request of HelloDi')
                    ->setFrom('taghandiky@gmail.com')
                    ->setTo('taghandiky@live.com')
                    ->setBody($this->renderView('HelloDiCoreBundle:Index:Contact.html.twig', array(
                        'Name' => $data['Name'],
                        'Description' => $data['Description'],
                        'Inquiry' => $data['Inquiry'],
                        'Email' => $data['Email']
                    )), 'text/html');
                $mailer->send($message);
                $this->get('session')->getFlashBag()->add('success',
                    $this->get('translator')->trans('message_send_successfully', null, 'message')
                );
            }
        }

        return $this->render('HelloDiCoreBundle:Index:Index.html.twig', array(
            'formContact' => $Form->createView()
        ));
    }
}
