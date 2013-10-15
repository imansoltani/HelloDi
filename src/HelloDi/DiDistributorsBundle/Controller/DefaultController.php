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



    public  function LogAction($flag,Request $request)
    {

        $em=$this->getDoctrine()->getManager();

       $form=$this->createFormBuilder()
           ->add('username','text',array(
               'required'=>false,
               'label'=>'UserName','translation_domain'=>'user'
           ))
           ->add('from','date',array(
               'widget'=>'single_text',
               'format'=>'yyyy/MM/dd',
               'data'=>(new \DateTime('now'))->sub(new \DateInterval('P1D')),
               'label'=>'From','translation_domain'=>'transaction'

            ))
           ->add('to','date',array(
               'widget'=>'single_text',
               'format'=>'yyyy/MM/dd',
               'data'=>new \DateTime('now'),
               'label'=>'To','translation_domain'=>'transaction'
           ))->getForm();

        $Logs=$em->getRepository('HelloDiDiDistributorsBundle:Log')->findAll();

        if($request->isMethod('POST'))
        {
try{
           $form->handleRequest($request);
            $data=$form->getData();
            $qb=$em->createQueryBuilder();
              $qb->select('Lgs')
                  ->from('HelloDiDiDistributorsBundle:Log','Lgs');
                  if($data['username'])
                  $qb->where($qb->expr()->like('Lgs.User',$qb->expr()->literal('%'.$data['username'].'%')));
                  $qb->andwhere('Lgs.Date >= :from')->setParameter('from',$data['from'])
                  ->andWhere('Lgs.Date <= :to')->setParameter('to',$data['to']);
            $qb=$qb->getQuery();
            $Logs=$qb->getResult();
           $this->get('session')->set('From',$data['from']);
           $this->get('session')->set('To',$data['to']);
           $this->get('session')->set('User',$data['username']);
           $this->get('session')->set('Logs',$Logs);
        }
        catch(\Exception $e){
            $this->get('session')->getFlashBag()->add('error',
                $this->get('translator')->trans('You_entered_an_invalid',array(),'message'));
        }

            }



  if($this->get('session')->has('Logs') and $flag=='DeleteSearch')
         {

            $em->createQuery('delete from HelloDiDiDistributorsBundle:Log as Lgs

                              where Lgs.Date >= :frDate
                              and Lgs.Date <= :toDate
                              or Lgs.User
                              LIKE  :usr '
                            )
                ->setParameters(
                    array(
                        'usr'=>$this->get('session')->get('User'),
                        'frDate'=>$this->get('session')->get('From'),
                        'toDate'=>$this->get('session')->get('To')
                    ))
                ->execute();

         $this->get('session')->remove('Logs');
         $Logs=array();
         }


        if($flag=='DeleteAll')
            $em->createQuery('delete from HelloDiDiDistributorsBundle:Log')->execute();


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
                'Logs' =>$Logs,
                'form'=>$form->createView()
            )

        );
    }

}