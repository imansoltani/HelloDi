<?php
namespace HelloDi\DiDistributorsBundle\Controller;
use HelloDi\DiDistributorsBundle\Entity\Notification;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NotificationController
{


    private  $em;
    private  $Request;
   public  function  __construct($EntityManage,$Request)
    {



        $this->em= $EntityManage;
        $this->Request=$Request;


    }

 public function NewAction($id,$type,$value=null)
 {
   if($id!=null)
       $Account=$this->em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
   else
       $Account=null;

$Exist=$this->em->getRepository('HelloDiDiDistributorsBundle:Notification')->findOneBy(array(
    'Account'=>$Account,
    'Type'=>$type,
    'Value'=>$value
));

   if(count($Exist)==0)
 {
 $Not=new Notification();
     if($id!=null)
       $Not->setAccount($this->em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id));
       $Not->setDate(new \DateTime('now'));
       $Not->setType($type);
    if($value!=null)
       $Not->setValue($value);

 $this->em->persist($Not);
 $this->em->flush();
 }
return new Response();

 }

    public function ReadAction($id)
    {

        $Notification=$this->em->getRepository('HelloDiDiDistributorsBundle:Notification')->find($id);
        $this->em->remove($Notification);
        $this->em->flush();

      return new Response();

    }


    public function CountAction($id)
    {
if($id!=null)
    $Account=$this->em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
else
    $Account=null;
        $Notification=$this->em->getRepository('HelloDiDiDistributorsBundle:Notification')->findBy(array('Account'=>$Account));

        return new Response(count($Notification));

    }


}