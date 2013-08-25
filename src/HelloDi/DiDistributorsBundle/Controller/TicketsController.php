<?php

namespace HelloDi\DiDistributorsBundle\Controller;
use HelloDi\DiDistributorsBundle\Entity\Ticket;
use HelloDi\DiDistributorsBundle\Entity\TicketNote;
use HelloDi\DiDistributorsBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

class TicketsController extends Controller
{

    /////tickets


public  function  ticketsAction(Request $req)
{

    $paginator = $this->get('knp_paginator');

    $em=$this->getDoctrine()->getManager();
    $usermaster=$em->getRepository('HelloDiDiDistributorsBundle:User')->findBy(array('Account'=>null));


    $User=$this->get('security.context')->getToken()->getUser();
    $Account=$User->getAccount();

    $form=$this->createFormBuilder()
        ->add('Type','choice',array('label'=>'Type','translation_domain'=>'ticket',
         'choices'=>array(
             -1=>'All',
             0=>'payment_issue',
             1=>'new_item_request',
             2=>'price_change_request',
             3=>'address_change',
             4=>'account_change_requests',
             5=>'bug_reporting',
             6=>'support'
         )
        ))

        ->add('Status','choice',array(
        'expanded'=>true,
         'choices'=>array(
           0=>'Close',
           1=>'Open'
         )))
        ->add('Distributors','checkbox',array('label'=>'Distributors','translation_domain'=>'accounts',
            'required'=>false    ))

        ->add('Retailers','checkbox',array('label'=>'Retailers','translation_domain'=>'accounts',
            'required'=>false ))
        ->getForm();

    $tickets=$em->createQueryBuilder();
    $tickets->select('Tic')
        ->from('HelloDiDiDistributorsBundle:Ticket','Tic')
        ->Where(
        $tickets->expr()->orX(
            $tickets->expr()->andX(

                $tickets->expr()->isNull('Tic.Accountretailer')  ,
                $tickets->expr()->isNotNull('Tic.Accountdist')

            ),

            $tickets->expr()->andX(

                $tickets->expr()->isNull('Tic.Accountdist') ,
                $tickets->expr()->isNotNull('Tic.Accountretailer')

            )

        )
    )
 ->orWhere($tickets->expr()->isnotNull('Tic.inchange'));

if($req->isMethod('POST'))
{
   $form->submit($req);
    $data=$form->getData();

    $tickets=$em->createQueryBuilder();
     $tickets->select('Tic')
         ->from('HelloDiDiDistributorsBundle:Ticket','Tic')
         ->Where('Tic.Status = :status')->setParameter('status',$data['Status']);
if($data['Retailers']==1 and $data['Distributors']==1)
    {
     $tickets->andWhere(
      $tickets->expr()->orX(
          $tickets->expr()->andX(

              $tickets->expr()->isNull('Tic.Accountretailer')  ,
              $tickets->expr()->isNotNull('Tic.Accountdist')

                                 ),

          $tickets->expr()->andX(

              $tickets->expr()->isNull('Tic.Accountdist') ,
              $tickets->expr()->isNotNull('Tic.Accountretailer')

                                )

                           )
                        );
    }
    elseif($data['Distributors']==1 and $data['Retailers']==0)
    {
        $tickets->andWhere($tickets->expr()->isNull('Tic.Accountretailer'));
    }

    elseif($data['Retailers']==1 and $data['Distributors']==0)
    {
        $tickets->andWhere($tickets->expr()->isNull('Tic.Accountdist'));

    }

    if($data['Type']!=-1)

         $tickets->andWhere('Tic.type = :type')->setParameter('type',$data['Type']);

         $tickets->orWhere($tickets->expr()->isnotNull('Tic.inchange'));


}

    $tickets=$tickets->getQuery();
    $count = count($tickets->getResult());
    $tickets->setHint('knp_paginator.count', $count);

    $pagination = $paginator->paginate(
        $tickets,
        $req->get('page',1) /*page number*/,
        10/*limit per page*/
    );
    return $this->render('HelloDiDiDistributorsBundle:Tickets:Tickets.html.twig',array(
        'Account'=>$Account,
         'form'=>$form->createView(),
        'pagination'=>$pagination,
        'usermaster'=>$usermaster
    ));

}




public  function ticketsnoteAction(Request $req,$id)
{
    $em=$this->getDoctrine()->getManager();


    $note=new TicketNote();

    $User=$this->get('security.context')->getToken()->getUser();
    $usermaster=$User->getEntiti()->getUsers();
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
        ->Where('Note.User not in (:usr)')->setParameter('usr',$User->getEntiti()->getUsers()->ToArray())
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
$em=$this->getDoctrine()->getManager();

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
        $em=$this->getDoctrine()->getManager();

        $ticket=$em->getRepository('HelloDiDiDistributorsBundle:Ticket')->find($id);

            $ticket->setStatus(1);
            $ticket->setTicketStart(new \DateTime('now'));
            $ticket->setTicketEnd(null);

              $em->flush();

        return $this->redirect($this->generateUrl('MasterTicketsNote',array('id'=>$id)));
    }


    public  function  releaseticketsAction($id)
    {
        $em=$this->getDoctrine()->getManager();

        $ticket=$em->getRepository('HelloDiDiDistributorsBundle:Ticket')->find($id);
        $ticket->setInchange(null);
        $em->flush();

        return $this->redirect($this->generateUrl('MasterTickets'));
    }



public  function taketicketsAction($id)
{
    $istake=$this->get('hello_di_di_distributors.Tickets');
    $em=$this->getDoctrine()->getManager();
    $User=$this->get('security.context')->getToken()->getUser();
    $ticket=$em->getRepository('HelloDiDiDistributorsBundle:Ticket')->find($id);
    $istake->IsTake($ticket,$User);
    return $this->redirect($this->generateUrl('MasterTicketsNote',array('id'=>$id)));
}

    public  function  countnoteAction()
    {
        $User = $this->get('security.context')->getToken()->getUser();
        $users=$User->getEntiti()->getUsers();
        $em=$this->getDoctrine()->getManager();
        $Countnote=$em->createQueryBuilder();
        $Countnote->select('Note')
            ->from('HelloDiDiDistributorsBundle:TicketNote','Note')
            ->innerJoin('Note.Ticket','NoteTic')
            ->Where(
                $Countnote->expr()->orX(
                     $Countnote->expr()->andX(

                         $Countnote->expr()->isNull('NoteTic.Accountretailer')  ,
                         $Countnote->expr()->isNotNull('NoteTic.Accountdist')

                     ),

                     $Countnote->expr()->andX(

                         $Countnote->expr()->isNull('NoteTic.Accountdist') ,
                         $Countnote->expr()->isNotNull('NoteTic.Accountretailer')

                     )

                 )
            );

            $Countnote->andWhere('Note.User NOT IN(:usr)')->setParameter('usr',$users->toArray());


        $Countnote->andWhere('Note.view = 0');
        return new Response(count($Countnote->getQuery()->getResult()));

    }



    /////end tickets


}

