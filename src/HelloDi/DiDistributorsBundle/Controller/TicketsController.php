<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\ORM\EntityRepository;
use FOS\UserBundle\Mailer\Mailer;
use HelloDi\DiDistributorsBundle\Entity\DetailHistory;
use HelloDi\DiDistributorsBundle\Entity\Entiti;
use HelloDi\DiDistributorsBundle\Entity\Price;
use HelloDi\DiDistributorsBundle\Entity\PriceHistory;
use HelloDi\DiDistributorsBundle\Entity\Ticket;
use HelloDi\DiDistributorsBundle\Entity\TicketNote;
use HelloDi\DiDistributorsBundle\Entity\Transaction;
use HelloDi\DiDistributorsBundle\Form\Distributors\NewRetailersType;
use HelloDi\DiDistributorsBundle\Entity\User;
use HelloDi\DiDistributorsBundle\Form\Distributors\NewUserRetailersType;
use HelloDi\DiDistributorsBundle\Form\Distributors\NewUserDistributorsType;
use HelloDi\DiDistributorsBundle\Form\Distributors\RetailerSearchType;
use HelloDi\DiDistributorsBundle\Form\Entiti\EntitiType;
use HelloDi\DiDistributorsBundle\Form\PriceEditType;
use HelloDi\DiDistributorsBundle\Form\Retailers\AccountRetailerSettingType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HelloDi\DiDistributorsBundle\Entity\Account;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Routing\RouteCollection;

class TicketsController extends Controller
{

    /////tickets


public  function  ticketsAction(Request $req)
{


////    print_r($req->getPathInfo().'==='.$req->getBasePath().'==='.$req->getBaseUrl());
//
//    die(str_split($req->getPathInfo(),7)[0]);
//
//    die(substr($req->getPathInfo(),0,6));

    $em=$this->getDoctrine()->getEntityManager();
    $usermaster=$em->getRepository('HelloDiDiDistributorsBundle:User')->findBy(array('Account'=>null));


    $User=$this->get('security.context')->getToken()->getUser();
    $Account=$User->getAccount();

    $form=$this->createFormBuilder()
        ->add('Type','choice',array(
         'choices'=>array(
             5=>'All',
             0=>'Payment issue',
             1=>'new item request',
             2=>'price change request')
        ))

        ->add('Status','choice',array(
        'expanded'=>true,
         'choices'=>array(
           0=>'Close',
           1=>'Open'
         )))
        ->add('Distributors','checkbox',array(
            'required'=>false    ))
        ->add('Retailers','checkbox',array(
            'required'=>false ))
        ->getForm();

    $tickets=$em->createQueryBuilder();
    $tickets->select('Tic')
        ->from('HelloDiDiDistributorsBundle:Ticket','Tic')
          ->orWhere( $tickets->expr()->isNull('Tic.Accountretailer'))
          ->orWhere($tickets->expr()->isNull('Tic.Accountdist'))
          ->orWhere($tickets->expr()->isnotNull('Tic.inchange'));

    foreach($User->getEntiti()->getUsers() as $Userm)
       {
           $tickets->andWhere('Tic.lastUser != :user  ')->setParameter('user',$Userm);
       }


if($req->isMethod('POST'))
{
   $form->submit($req);
    $data=$form->getData();

    $tickets=$em->createQueryBuilder();
     $tickets->select('Tic')
         ->from('HelloDiDiDistributorsBundle:Ticket','Tic')
         ->Where('Tic.Status = :status')->setParameter('status',$data['Status']);
    if($data['Distributors']==1 and $data['Retailers']==0)
        $tickets->andWhere($tickets->expr()->isNull('Tic.Accountretailer'));
    elseif($data['Retailers']==1 and $data['Distributors']==0)
        $tickets->andWhere($tickets->expr()->isNull('Tic.Accountdist'));
    elseif(($data['Retailers']==0 and $data['Distributors']==0)or($data['Retailers']==1 and $data['Distributors']==1))
    {
        $tickets->andWhere($tickets->expr()->isNull('Tic.Accountdist'));
        $tickets->orWhere($tickets->expr()->isNull('Tic.Accountretailer'));
    }

    if($data['Type']!=5)

         $tickets->andWhere('Tic.type = :type')->setParameter('type',$data['Type']);
         $tickets->orWhere($tickets->expr()->isnotNull('Tic.inchange'));

}
    $tickets=$tickets->getQuery()->getResult();

    return $this->render('HelloDiDiDistributorsBundle:Tickets:Tickets.html.twig',array(
        'Account'=>$Account,
         'form'=>$form->createView(),
        'pagination'=>$tickets,
        'usermaster'=>$usermaster
    ));

}




public  function ticketsnoteAction(Request $req,$id)
{
    $em=$this->getDoctrine()->getEntityManager();
    $usermaster=$em->getRepository('HelloDiDiDistributorsBundle:User')->findBy(array('Account'=>null));


    $note=new TicketNote();

    $User=$this->get('security.context')->getToken()->getUser();
    $ticket=$em->getRepository('HelloDiDiDistributorsBundle:Ticket')->find($id);


    $form=$this->createFormBuilder()
        ->add('Description','textarea',array('required'=>true))->getForm();


    if($req->isMethod('POST'))
    {
       $form->submit($req);
        $data=$form->getData();
        $note->setTicket($ticket);
        $note->setView(0);
        $note->setUser($User);
        $note->setDate(new \DateTime('now'));
        $note->setDescription($data['Description']);
      $em->persist($note);
        $ticket->setLastUser($User);
      $em->flush();
    }

    ///update vi
    $notesview=$em->createQueryBuilder();
    $notesview->update('HelloDiDiDistributorsBundle:TicketNote','Note')
        ->set('Note.view',1)
        ->Where('Note.User != :usr')->setParameter('usr',$User)
        ->andWhere('Note.Ticket = :tic')->setParameter('tic',$ticket)
        ->andWhere('Note.view = 0')
        ->getQuery()->execute();

    $notes=$em->getRepository('HelloDiDiDistributorsBundle:TicketNote')->findBy(array('Ticket'=>$ticket));
    return $this->render('HelloDiDiDistributorsBundle:Tickets:TicketNote.html.twig',array(
        'Ticket'=>$ticket,
        'pagination'=> array_reverse($notes),
        'Account'=>$User->getAccount(),
        'form'=>$form->createView(),
        'usermaster'=>$usermaster
    ));


}

public  function  ticketschangestatusAction(Request $req,$id)
{
$em=$this->getDoctrine()->getEntityManager();

    $ticket=$em->getRepository('HelloDiDiDistributorsBundle:Ticket')->find($id);

    if($ticket->getStatus()==1)
    {
        $ticket->setStatus(0);
        $ticket->setTicketEnd(new \DateTime('now'));
    }

   else
   {
       $ticket->setStatus(1);
       $ticket->setTicketStart(new \DateTime('now'));
       $ticket->setTicketEnd(null);
   }

$em->flush();

    return $this->redirect($this->generateUrl('MasterTickets'));
}


    public  function  ticketsstatusAction(Request $req,$id)
    {
        $em=$this->getDoctrine()->getEntityManager();

        $ticket=$em->getRepository('HelloDiDiDistributorsBundle:Ticket')->find($id);

            $ticket->setStatus(1);
            $ticket->setTicketStart(new \DateTime('now'));
            $ticket->setTicketEnd(null);

              $em->flush();

        return $this->redirect($this->generateUrl('MasterTicketsNote',array('id'=>$id)));
    }


    public  function  releaseticketsAction($id)
    {
        $em=$this->getDoctrine()->getEntityManager();

        $ticket=$em->getRepository('HelloDiDiDistributorsBundle:Ticket')->find($id);
        $ticket->setInchange(null);
        $em->flush();

        return $this->redirect($this->generateUrl('MasterTickets'));
    }



public  function taketicketsAction($id)
{
    $istake=$this->get('hello_di_di_distributors.Tickets');
    $em=$this->getDoctrine()->getEntityManager();
    $User=$this->get('security.context')->getToken()->getUser();
    $ticket=$em->getRepository('HelloDiDiDistributorsBundle:Ticket')->find($id);
    $istake->IsTake($ticket,$User);
    return $this->redirect($this->generateUrl('MasterTicketsNote',array('id'=>$id)));
}

    public  function  countnoteAction()
    {
        $User = $this->get('security.context')->getToken()->getUser();
        $em=$this->getDoctrine()->getEntityManager();
        $Countnote=$em->createQueryBuilder();
        $Countnote->select('Note')
            ->from('HelloDiDiDistributorsBundle:TicketNote','Note')
            ->innerJoin('Note.Ticket','NoteTic')
            ->orWhere($Countnote->expr()->isNull('NoteTic.Accountretailer'))
            ->orWhere($Countnote->expr()->isNull('NoteTic.Accountdist'))
            ->andWhere('Note.User != :usr')->setParameter('usr',$User)
            ->andWhere('Note.view = 0');

        return new Response(count($Countnote->getQuery()->getResult()));
    }



    /////end tickets


}

