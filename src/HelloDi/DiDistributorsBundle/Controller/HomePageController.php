<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use HelloDi\DiDistributorsBundle\Form\HomePage\ContactType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomePageController extends Controller
{

    public function indexAction($locale,Request $req)
    {
        $em=$this->getDoctrine()->getManager();
        $this->get('session')->set('_locale',$locale);

       $Form=$this->createForm(new ContactType());
if($req->isMethod('POST'))
{
  $Form->handleRequest($req);
    if($Form->isValid())
    {
$data=$Form->getData();
        $message = \Swift_Message::newInstance()
            ->setSubject('HelloDi -- from '. $data['Email'].' have a request')
            ->setTo('taghandiky2020@localhost')
//            ->setFrom($data['Email'])
            ->setBody(
                $this->renderView(
                    'HelloDiDiDistributorsBundle:HomePage:Contact.html.twig',
                      array(
                        'Name' => $data['Name'],
                        'Description'=>$data['Description'],
                        'Inquiry'=>$data['Inquiry'],
                        'Email'=>$data['Email']
                          )
                ), 'text/html'
    )
     ->addPart('My amazing body in plain text', 'text/plain');

        $this->get('mailer')->send($message);


        $this->get('session')->getFlashBag()->add('success',
            $this->get('translator')->trans('message_send_successfully',
                array(),
                   'message'));

    }
}

       return $this->render('HelloDiDiDistributorsBundle:HomePage:Index.html.twig',
           array(
               'formContact'=>$Form->createView(),
           )

       );

    }

}