<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use HelloDi\DiDistributorsBundle\Form\HomePage\ContactType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function dashboardAction()
    {
        $em=$this->getDoctrine()->getManager();
        $Notifications=$em->getRepository('HelloDiDiDistributorsBundle:Notification')->findBy(array('Account'=>null));

        return $this->render('HelloDiDiDistributorsBundle:Dashboard:Master_dashboard.html.twig',
            array(
                'MU' => 'home',
                 'Notifications'=>$Notifications
            ));
    }

    public function indexAction($locale, Request $req)
    {


        $em = $this->getDoctrine()->getManager();

        $this->get('session')->set('_locale', $locale);
        $req->setLocale($locale);

        $Form = $this->createForm(new ContactType());
        if ($req->isMethod('POST'))
        {
            $Form->handleRequest($req);
           if($Form->isValid())
           {
            $data = $Form->getData();

            $mailer = $this->get('mailer');

            $message = \Swift_Message::newInstance()
                ->setSubject('Request of HelloDi')
                ->setFrom('taghandiky@gmail.com')
                ->setTo('taghandiky@live.com')
                ->setBody($this->renderView(
                            'HelloDiDiDistributorsBundle:HomePage:Contact.html.twig',
                            array(
                                'Name' => $data['Name'],
                                'Description' => $data['Description'],
                                'Inquiry' => $data['Inquiry'],
                                'Email' => $data['Email']
                            )
                        ), 'text/html');

            $mailer->send($message);

            $this->get('session')->getFlashBag()->add('success',
                    $this->get('translator')->trans('message_send_successfully',
                        array(),
                        'message'));

}
        }

        return $this->render('HelloDiDiDistributorsBundle:HomePage:Index.html.twig',
            array(
                'formContact' => $Form->createView(),
            )

        );


}


 public  function ExceptionsAction($flag)
 {

     $em=$this->getDoctrine()->getManager();

     if($flag=='Log')
     {
         return new Response( file_get_contents($this->container->getParameter('kernel.logs_dir').'/'.$this->container->getParameter('kernel.environment').'.log'),200,array(
             'Content-Type'          => 'text/txt',
             'Content-Disposition'   => 'attachment; filename="Log(info).txt"'
         ));
     }
     elseif($flag=='DeleteAll')
        $em->createQuery('delete from HelloDiDiDistributorsBundle:Exceptions')->execute();



     $Exceptions=$em->getRepository('HelloDiDiDistributorsBundle:Exceptions')->findAll();

     if($flag=='Export')
     {
          $result='';
         foreach($Exceptions as $Ex)
         {
             $result.=$Ex->getId().' ; '.$Ex->getDate()->format('YY/m/d').' ; '. $Ex->getUsername() .' ; '.$Ex->getDescription()."\r\n";
         }
         return new Response($result,200,array(
             'Content-Type'          => 'text/txt',
             'Content-Disposition'   => 'attachment; filename="Exceptions.txt"'
         ));
     }

     return $this->render('HelloDiDiDistributorsBundle:Exceptions:Exceptions.html.twig',
         array(
             'Exceptions' =>$Exceptions,
         )

     );
 }


 public function DeleteExceptionsAction(Request $req)
 {
     $em=$this->getDoctrine()->getManager();
       $Exception=$em->getRepository('HelloDiDiDistributorsBundle:Exceptions')->find($req->get('id'));
         $em->remove($Exception);
         $em->flush();

     $Exceptions=$em->getRepository('HelloDiDiDistributorsBundle:Exceptions')->findAll();

     return new Response(count($Exceptions));

 }







    #Logg#



    public  function LogAction($flag)
    {

        $em=$this->getDoctrine()->getManager();

       if($flag=='DeleteAll')
            $em->createQuery('delete from HelloDiDiDistributorsBundle:Log')->execute();

        $Logs=$em->getRepository('HelloDiDiDistributorsBundle:Log')->findAll();

        if($flag=='Export')
        {
            $result='';
            foreach($Logs as $Lg)
            {
                $result.=$Lg->getId().' ; '.$Lg->getDate()->format('YY/m/d').' ; '. $Lg->getUser() .' ; '.$Lg->getPath().' ; '.$Lg->getController()."\r\n";
            }
            return new Response($result,200,array(
                'Content-Type'          => 'text/txt',
                'Content-Disposition'   => 'attachment; filename="Logs.txt"'
            ));
        }

        return $this->render('HelloDiDiDistributorsBundle:Log:Logs.html.twig',
            array(
                'Logs' =>$Logs
            )

        );
    }


    public function DeleteLogAction(Request $req)
    {
        $em=$this->getDoctrine()->getManager();
        $Log=$em->getRepository('HelloDiDiDistributorsBundle:log')->find($req->get('id'));
        $em->remove($Log);
        $em->flush();

        $Logs=$em->getRepository('HelloDiDiDistributorsBundle:Log')->findAll();

        return new Response(count($Logs));

    }

}