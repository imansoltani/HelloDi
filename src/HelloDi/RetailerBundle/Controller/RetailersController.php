<?php

namespace HelloDi\RetailerBundle\Controller;

use Doctrine\ORM\EntityRepository;
use HelloDi\CoreBundle\Entity\B2BLog;
use HelloDi\CoreBundle\Entity\Item;
use HelloDi\CoreBundle\Entity\OrderCode;
use HelloDi\PricingBundle\Entity\Price;
use HelloDi\CoreBundle\Entity\Ticket;
use HelloDi\CoreBundle\Entity\TicketNote;
use HelloDi\CoreBundle\Entity\User;
use HelloDi\DiDistributorsBundle\Form\User\NewUserType;
use HelloDi\DiDistributorsBundle\Helper\SoapClientTimeout;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use HelloDi\AccountingBundle\Entity\Transaction;
use Symfony\Component\Yaml\Dumper;

class RetailersController extends Controller
{


#notifications#
  public function CountNotificationAction()
  {

    return $this->forward('hello_di_di_notification:CountAction',array('id'=>$this->getUser()->getAccount()->getId()));
  }

    public function ShowNotificationAction()
    {
        $em=$this->getDoctrine()->getManager();
        $Notifications=$em->getRepository('HelloDiDiDistributorsBundle:Notification')->findBy(array('Account'=>$this->getUser()->getAccount()));

        return $this->render('HelloDiDiDistributorsBundle:Retailers:Notifications.html.twig',
            array(
                'Account' => $this->getUser()->getAccount(),
                'Entity' => $this->getUser()->getAccount()->getEntiti(),
                'Notifications'=>$Notifications
            ));

    }

    public function ReadNotificationAction(Request $req)
    {

      return  $this->forward('hello_di_di_notification:ReadAction',array('id'=>$req->get('id')));

    }


    public function ShowLastNotificationAction(){

        $em=$this->getDoctrine()->getManager();
        $Notifications=$em->getRepository('HelloDiDiDistributorsBundle:Notification')->findBy(array('Account'=>$this->getUser()->getAccount()));
        $i=0;
        $str='';
        foreach($Notifications as $Notif)
        {
            $str.='<li id="Notif'.$Notif->getId().'" ><a href="'.$this->generateUrl('RetailerShowNotification').'" >';

            if($Notif->getType()==31)
                $str.= $this->get('translator')->trans('Retailer_account_balance_is_lower_than_equal_%value%',array('value'=>$Notif->getValue()),'notification');

            elseif($Notif->getType()==32)
                $str.= $this->get('translator')->trans('Balance_increased_%value%',array('value'=>$Notif->getValue()),'notification');

            elseif($Notif->getType()==33)
                $str.=   $this->get('translator')->trans('CreditLimit_increased_%value%',array('value'=>$Notif->getValue()),'notification');

            elseif($Notif->getType()==34)
                $str.=  $this->get('translator')->trans('CreditLimit_decreased_%value%',array('value'=>$Notif->getValue()),'notification');

            elseif($Notif->getType()==35)
                $str.=  $this->get('translator')->trans('Edited_account',array(),'notification');

            elseif($Notif->getType()==36)
                $str.=  $this->get('translator')->trans('Edited_entity',array(),'notification');

            elseif($Notif->getType()==37)
                $str.=  $this->get('translator')->trans('Added_user_with_username_%value%',array('value'=>$Notif->getValue()),'notification');

            $str.='</a></li>';



            if(++$i==3)break;
        }
        $str.= '<li><a href="'.$this->generateUrl("RetailerShowNotification").'">'.$this->get('translator')->trans('Notifications',array(),'notification').'</a></li>';
        return new Response($str);
    }



    public  function  countnoteAction()
  {
      $User = $this->getUser();
      $users=$User->getAccount()->getUsers();
      $em=$this->getDoctrine()->getManager();
      $Countnote=$em->createQueryBuilder();
      $Countnote->select('Note')
          ->from('HelloDiDiDistributorsBundle:TicketNote','Note')
          ->innerJoin('Note.Ticket','NoteTic')
          ->Where('NoteTic.Accountretailer = :Accr')->setParameter('Accr',$User->getAccount())
          ->andWhere('Note.User NOT IN(:usr)')->setParameter('usr',$users->toArray())
          ->andWhere('Note.view = 0');

      return new Response(count($Countnote->getQuery()->getResult()));
  }

    public function RetailerProfileAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $Account = $user->getAccount();

        return $this->render('HelloDiDiDistributorsBundle:Retailers:RetailerProfile.html.twig', array('Account' => $Account, 'Entiti' => $Account->getEntiti(), 'User' => $user));
    }

    public function RetailerStaffAction()
    {
        $em=$this->getDoctrine()->getManager();
        $user=$this->getUser();

        $qb=$em->createQueryBuilder()
                  ->select('USR')
                  ->from('HelloDiDiDistributorsBundle:User','USR')
                  ->Where('USR.Account = :Acc')->setParameter('Acc',$user->getAccount())
                  ->andWhere('USR.Entiti = :Ent')->setParameter('Ent',$user->getEntiti());
        $qb=$qb->getQuery();

        return $this->render('HelloDiDiDistributorsBundle:Retailers:Staff.html.twig',
            array(
                'Entiti' => $user->getEntiti(),
                'pagination' => $qb->getResult(),
                'Account'=>$user->getAccount()
            ));

    }

    public function RetailerStaffAddAction(Request $request)
    {
        $user = new User();
        $em = $this->getDoctrine()->getManager();
        $Account = $this->get('security.context')->getToken()->getUser()->getAccount();
        $Entiti = $Account->getEntiti();

        $form = $this->createForm(new NewUserType('HelloDiDiDistributorsBundle\Entity\User',2), $user, array('cascade_validation' => true));
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $user->setAccount($Account);
            $user->setEntiti($Entiti);
            if ($form->isValid()) {
                $em->persist($user);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl('RetailerStaff', array('id' => $Account->getId())));

            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Retailers:StaffAdd.html.twig', array(
            'Entiti' => $Account->getEntiti(),
            'Account' => $Account,
            'form' => $form->createView(),));

    }

    public function RetailerStaffEditAction(Request $request, $id)
    {
        $this->check_User($id);

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('HelloDiDiDistributorsBundle:User')->find($id);
        $form = $this->createForm(new NewUserType('HelloDiDiDistributorsBundle\Entity\User',2), $user, array('cascade_validation' => true));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->flush();
                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl('RetailerStaff', array('id' => $user->getAccount()->getId())));
            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Retailers:StaffEdit.html.twig',
            array(
                'Account' => $user->getAccount(),
                'Entiti' => $user->getEntiti(),
                'userid' => $id,
                'form' => $form->createView()));

    }


    public function TransactionAction(Request $req)
    {
        $paginator = $this->get('knp_paginator');
        $User= $this->get('security.context')->getToken()->getUser();
        $Account=$User->getAccount();
        $em=$this->getDoctrine()->getManager();
        $qb=array();

        $form=$this->createFormBuilder()

            ->add('TypeDate','choice', array(
                'expanded'   => true,
                'choices'    => array(
                    0 => 'TradeDate',
                    1 => 'BookingDate',
                )))

            ->add('DateStart','date',array(
                'widget'=>'single_text',
                'format'=>'yyyy/MM/dd',
                'required'=>false,
                'label'=>'From',
                'translation_domain'=>'transaction'
                                          ))

            ->add('DateEnd','date',array(
                'widget'=>'single_text',
                'format'=>'yyyy/MM/dd',
                'required'=>false,
                'label'=>'To',
                'translation_domain'=>'transaction'
                                         ))

            ->add('Type','choice',array('label'=>'Type','translation_domain'=>'transaction',
                'choices'=> array(
                    2=>'All',
                    1=>'Credit',
                    0=>'Debit'
                )))

            ->add('Action','choice',array('label'=>'Action','translation_domain'=>'transaction',
                'choices'=> array(
                    'All'=>'All',
                    'sale'=>'debit_balance_when_the_retailer_sell_a_code',
                    'crnt'=>'issue_a_credit_note_for_a_sold_code',
                    'tran'=>'transfer_credit_from_distributor,s_account_to_a_retailer,s_account',
                    'ogn_pmt'=>'ogone_payment_on_its_own_account'
                )))->getForm();
$datetype=0;

        if($req->isMethod('POST'))
        {


            $form->handleRequest($req);
            $data=$form->getData();
            $qb=$em->createQueryBuilder();
            $qb->select('Tran')
                ->from('HelloDiAccountingBundle:Transaction','Tran')
                ->where('Tran.Account =:Acc')->setParameter('Acc',$Account);
            if($data['TypeDate']==0)
            {
             if($data['DateStart']!='')
                $qb->andwhere('Tran.tranDate >= :DateStart')->setParameter('DateStart',$data['DateStart']);
             if($data['DateEnd']!='')
                $qb->andwhere('Tran.tranDate <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);

            }

            if($data['TypeDate']==1)
            {$datetype=1;
                if($data['DateStart']!='')
                $qb->andwhere('Tran.tranInsert >= :DateStart')->setParameter('DateStart',$data['DateStart']);
                if($data['DateEnd']!='')
                $qb->andwhere('Tran.tranInsert <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);

            }

            if($data['Type']!=2)
                 $qb->andWhere($qb->expr()->eq('Tran.tranType',$data['Type']));

            if($data['Action']!='All')
                $qb->andWhere($qb->expr()->like('Tran.tranAction',$qb->expr()->literal($data['Action'])));


            $qb->addOrderBy('Tran.tranInsert','desc')->addOrderBy('Tran.id','desc');

            $qb=$qb->getQuery();
            $count = count($qb->getResult());
             $qb->setHint('knp_paginator.count', $count);



        }

        $pagination = $paginator->paginate(
            $qb,
            $req->get('page') /*page number*/,
            10/*limit per page*/
        );

        return $this->render('HelloDiDiDistributorsBundle:Retailers:Transaction.html.twig',
            array(
                'pagination'=>$pagination,
                'form'=>$form->createView(),
                'Account' =>$Account,
                'Entiti' =>$User->getEntiti(),
                'typedate'=>$datetype
            ));

    }

    ////function Report/Sales
    public  function SaleAction(Request $req)
    {
        $User= $this->get('security.context')->getToken()->getUser();

        $em=$this->getDoctrine()->getManager();
         $qb=array();
        //load first list search




        $form=$this->createFormBuilder()

            ->add('ItemType','choice',
            array('label'=>'ItemType','translation_domain'=>'item',
                'choices'=>array(
                    'All' => 'All',
                    'dmtu'=>'Mobile',
                    'clcd'=>'Calling_Card',
                    'epmt'=>'E-payment',
                    'imtu' => 'IMTU',
                  )))

            ->add('ItemName', 'entity',
                  array('translation_domain'=>'item',
                      'required'=>false,
                 'label'=>'Item',
                 'empty_data' => '',
                 'empty_value'=>'All',
                 'class' => 'HelloDiDiDistributorsBundle:Item',
                 'property' => 'itemName',
                      'query_builder' => function(EntityRepository $er) use ($User) {
                          return $er->createQueryBuilder('u')
                               ->innerJoin('u.Prices','up')
                              ->where('up.Account = :Acc')->setParameter('Acc',$User->getAccount())
                              ->andWhere('up.priceStatus = 1');
                      }

            ));

  $roles = $User->getRoles() ;
  if($roles[0]=='ROLE_RETAILER_ADMIN')
  {
      $form=$form->add('Staff', 'entity',
                array('label'=>'Staff','translation_domain'=>'user',
                   'empty_value'=>'All',
                   'empty_data'=>'',
                    'required'=>false,
                'class' => 'HelloDiDiDistributorsBundle:User',
                'property' => 'username',
                'query_builder' => function(EntityRepository $er) use ($User) {
                    return $er->createQueryBuilder('u')
                           ->where('u.Account = :ua')
                           ->orderBy('u.username', 'ASC')
                           ->setParameter('ua',$User->getAccount());
                }
                ));

  }


  $form=$form->add('DateStart','date',array(
      'widget'=>'single_text',
      'format'=>'yyyy/MM/dd',
      'data'=>(new \DateTime('now'))->sub(new \DateInterval('P7D')),
      'required'=>false,'label'=>'From','translation_domain'=>'transaction'))
             ->add('DateEnd','date',array(
          'data'=>new \DateTime('now'),
          'widget'=>'single_text',
          'format'=>'yyyy/MM/dd',
          'required'=>false,'label'=>'To','translation_domain'=>'transaction'))->getForm();

        if($req->isMethod('POST'))
        {
            $form->handleRequest($req);
            $data=$form->getData();
            $qb=$em->createQueryBuilder();
            $qb->select(array('Tr'))
                ->from('HelloDiAccountingBundle:Transaction','Tr')
                /*for groupBy*/
                ->leftJoin('Tr.Code','TrCo')->leftJoin('TrCo.Item','TrCoIt')
                ->leftJoin('Tr.B2BLog','Trb2b')->leftJoin('Trb2b.Item','Trb2bIt')
                /**/
                ->Where('Tr.Account = :Acc')->setParameter('Acc',$User->getAccount())
                ->andWhere($qb->expr()->like('Tr.tranAction',$qb->expr()->literal('sale')));
            if($data['DateStart'])
                $qb->andwhere('Tr.tranDate >= :DateStart')->setParameter('DateStart',$data['DateStart']);
            if($data['DateEnd'])
                $qb->andwhere('Tr.tranDate <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);
            if($roles[0]=='ROLE_RETAILER_ADMIN')
            {
            if($data['Staff'])
              $qb->andWhere('Tr.User = :usr')->setParameter('usr',$data['Staff']);
            }
            else
                $qb->andWhere('Tr.User = :usr')->setParameter('usr',$User);
//
            if($data['ItemType']!='All')
                $qb->andwhere('TrCoIt.itemType LIKE :type or Trb2bIt.itemType LIKE :type')->setParameter("type",$data['ItemType']);


            if($data['ItemName'])
                $qb->andWhere('TrCoIt = :item or Trb2bIt = :item')->setParameter('item',$data['ItemName']);

            $qb->orderBy('Tr.tranInsert','desc');

             $qb=$qb->getQuery();
            $count = count($qb->getResult());

             $qb->setHint('knp_paginator.count', $count);
          }


        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $qb,
            $req->get('page', 1) /*page number*/,
            10/*limit per page*/
        );



        return $this->render('HelloDiDiDistributorsBundle:Retailers:ReportSales.html.twig',

            array(
            'pagination'=>$pagination,
            'form'=>$form->createView(),
             'User'=>$User,
            'Account' =>$User->getAccount(),
            'Entiti' =>$User->getEntiti(),

            ));

    }



    public function ticketsAction(Request $req)
    {

        $User=$this->get('security.context')->getToken()->getUser();
        $em=$this->getDoctrine()->getManager();

        $form=$this->createFormBuilder()
            ->add('Type','choice',array('choices'=>array(
                -1=>'All',
                0=>'Payment_issue',
                1=>'new_item_request',
                2=>'price_change_request',
                3=>'address_change',
                4=>'account_change_requests',
                5=>'bug_reporting',
                6=>'support'
            )))
            ->add('Status','choice',array(
                'expanded'=>true,
                'multiple'=>false,
                'choices'=>array(0=>'Close',1=>'Open')
            ))
            ->add('Contact','choice',array(
                'expanded'=>true,
                'multiple'=>false,
                'choices'=>array(0=>'Distributors',1=>'Support Team')
            ))
            ->getForm();

        $tickets=$em->getRepository('HelloDiDiDistributorsBundle:Ticket')->findBy(
            array(
                'Accountretailer'=>$User->getAccount()
            ));


        if($req->isMethod('POST'))
        {
            $form->handleRequest($req);
            $data=$form->getData();
            $tickets=$em->createQueryBuilder();

            $tickets->select('Tic')
                ->from('HelloDiDiDistributorsBundle:Ticket','Tic')
                ->Where('Tic.Status =:sta')->setParameter('sta',$data['Status'])
                ->andWhere('Tic.Accountretailer = :Acc')->setParameter('Acc',$User->getAccount());
            if($data['Type']!=-1)
                $tickets->andwhere('Tic.type =:type')->setParameter('type',$data['Type']);
            if($data['Contact']==0)
                $tickets->andwhere('Tic.Accountdist = :Accdist')->setParameter('Accdist',$User->getAccount()->getParent());
            elseif($data['Contact']==1)
                $tickets->andWhere($tickets->expr()->isNull('Tic.Accountdist'));
            $tickets=$tickets->getQuery();

            $count = count($tickets->getResult());
            $tickets->setHint('knp_paginator.count', $count);

        }


        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $tickets,
            $req->get('page', 1) /*page number*/,
           10/*limit per page*/
        );

        return $this->render('HelloDiDiDistributorsBundle:Retailers:Tickets.html.twig',array(
            'pagination'=>$pagination,
            'Account'=>$User->getAccount(),
            'User'=>$User,
            'form'=>$form->createView()
        ));

    }


    public  function ticketsnewAction(Request $req,$data=null)
    {
        $User=$this->get('security.context')->getToken()->getUser();

        $em=$this->getDoctrine()->getManager();
        $Tick=new Ticket();
        $TickNote=new TicketNote();

        $form=$this->createFormBuilder(array('Type'=>$data))
            ->add('Contact','choice',array('translation_domain'=>'ticket',
                'expanded'=>true,
                'multiple'=>false,
                'choices'=>array(0=>'Distributors',1=>'SupportTeam')
            ))
            ->add('Subject','text',array('label'=>'Subject','translation_domain'=>'ticket',))
            ->add('Type','choice',array('label'=>'Type','translation_domain'=>'ticket',
                'choices'=>array(
                    0=>'Payment_issue',
                    1=>'new_item_request',
                    2=>'price_change-request',
                    3=>'address_change',
                    4=>'account_change_requests',
                    5=>'bug_reporting',
                    6=>'support',

                )
            ))
            ->add('Description','textarea',array('label'=>'Description','translation_domain'=>'ticket','required'=>true))
            ->getForm();

        if($req->isMethod('POST'))
        {
            $form->handleRequest($req);
            $data=$form->getData();


            $Tick->setAccountretailer($User->getAccount());

            if($data['Contact']==0)
                $Tick->setAccountdist($User->getAccount()->getParent());

            $Tick->setTicketStart(new \DateTime('now'));
            $Tick->setTicketEnd(null);
            $Tick->setType($data['Type']);
            $Tick->setSubject($data['Subject']);
            $Tick->setStatus(1);
            $Tick->setLastUser($User);

            $TickNote->setUser($User);
            $TickNote->setDate(new \DateTime('now'));
            $TickNote->setDescription($data['Description']);
            $TickNote->setTicket($Tick);
            $TickNote->setView(0);

            $em->persist($TickNote);
            $em->persist($Tick);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success',
                $this->get('translator')->trans('ticket_create_successfully',array(),'message'));
        }


        return $this->render('HelloDiDiDistributorsBundle:Retailers:TicketNew.html.twig',array(

            'form'=>$form->createView(),
            'User'=>$User,
            'Account'=>$User->getAccount()
        ));


    }

    public  function  ticketsnoteAction(Request $req,$id)
    {
        $this->check_Ticket($id);

        $ticketNote=new TicketNote();
        $User=$this->get('security.context')->getToken()->getUser();
        $em=$this->getDoctrine()->getManager();
        $ticket=$em->getRepository('HelloDiDiDistributorsBundle:Ticket')->find($id);



        $form=$this->createFormBuilder()
            ->add('Description','textarea',array('required'=>true,
                'label'=>'Description','translation_domain'=>'ticket'
            ))->getForm();

        if($req->isMethod('POST'))
        {

            $form->handleRequest($req);
            $data=$form->getData();
            $ticketNote->setTicket($ticket);
            $ticketNote->setDescription($data['Description']);
            $ticketNote->setDate(new \DateTime('now'));
            $ticketNote->setUser($User);
            $ticketNote->setView(0);
            $ticket->setLastUser($User);
            $em->persist($ticketNote);
            $em->flush();

        }

        $noteslist=$em->getRepository('HelloDiDiDistributorsBundle:TicketNote')->findBy(array(
            'Ticket'=>$ticket,


        ));


///update vi
        $notesview=$em->createQueryBuilder();
        $notesview->update('HelloDiDiDistributorsBundle:TicketNote Note')
            ->set('Note.view',1)
            ->Where('Note.User Not in (:usr)')->setParameter('usr',$User->getAccount()->getUsers()->toArray())
            ->andWhere('Note.Ticket = :tic')->setParameter('tic',$ticket)
            ->andWhere('Note.view = 0')
            ->getQuery()->execute();


        return $this->render('HelloDiDiDistributorsBundle:Retailers:TicketNote.html.twig',array(
            'form'=>$form->createView(),
            'Ticket'=>$ticket,
            'User'=>$User,
            'Account'=>$User->getAccount(),
            'pagination'=>array_reverse($noteslist),
        ));

    }
    public  function  ticketschangestatusAction($id)
    {
        $this->check_Ticket($id);
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

        return $this->redirect($this->generateUrl('RetailerTickets'));
    }


    public  function  ticketsstatusAction($id)
    {
        $this->check_Ticket($id);
        $em=$this->getDoctrine()->getManager();

        $ticket=$em->getRepository('HelloDiDiDistributorsBundle:Ticket')->find($id);

        $ticket->setStatus(1);
        $ticket->setTicketStart(new \DateTime('now'));
        $ticket->setTicketEnd(null);

        $em->flush();

        return $this->redirect($this->generateUrl('RetailerTicketsNote',array('id'=>$id)));

    }






//--------endkazem--------//

// Start kamal

    public function BuyAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        $item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($request->get('item_id'));
        $accountRet = $user->getAccount();
        $priceRet = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Item'=>$item,'Account'=>$accountRet));
        $priceDist = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Item'=>$item,'Account'=>$accountRet->getParent()));

        $taxhistory = $em->getRepository('HelloDiDiDistributorsBundle:TaxHistory')->findOneBy(array('Tax'=>$priceDist->getTax(),'taxend'=>null));

        $codeselector = $this->get('hello_di_di_distributors.codeselector');
        $codes = $codeselector->lookForAvailableCode($accountRet, $priceRet, $item, $request->get('numberOfsale'));

        $com = $priceRet->getprice() - $priceDist->getprice();

        if ($codes)
        {
            $ordercode = new OrderCode();
            $ordercode->setLang($request->get('language'));
            foreach ($codes as $code)
            {
                $tranretailer = new Transaction();
                $tranretailer->setAccount($accountRet);
                $tranretailer->setTranAmount(-($priceRet->getPrice()));
                $tranretailer->setTranFees(0);
                $tranretailer->setTranDescription('Code id: ' . $code->getId());
                $tranretailer->setTranCurrency($accountRet->getAccCurrency());
                $tranretailer->setTranDate(new \DateTime('now'));
                $tranretailer->setTranInsert(new \DateTime('now'));
                $tranretailer->setCode($code);
                $tranretailer->setTranAction('sale');
                $tranretailer->setTranType(0);
                $tranretailer->setUser($user);
                $tranretailer->setTranBookingValue(null);
                $tranretailer->setTranBalance($accountRet->getAccBalance());
                $tranretailer->setTaxHistory($taxhistory);
                $tranretailer->setOrder($ordercode);
                $ordercode->addTransaction($tranretailer);

                // For distributors
                $trandist = new Transaction();
                $trandist->setAccount($accountRet->getParent());
                $trandist->setTranAmount($com);
                $trandist->setTranFees(0);
                $trandist->setTranDescription('Code id: ' . $code->getId());
                $trandist->setTranCurrency($accountRet->getParent()->getAccCurrency());
                $trandist->setTranDate(new \DateTime('now'));
                $trandist->setTranInsert(new \DateTime('now'));
                $trandist->setCode($code);
                $trandist->setTranAction('com');
                $trandist->setTranType(1);
                $trandist->setUser($user);
                $trandist->setTranBookingValue(null);
                $trandist->setTranBalance($accountRet->getParent()->getAccBalance());
                $trandist->setTaxHistory($taxhistory);
                $trandist->setBuyingprice($priceDist->getPrice());
                $trandist->setOrder($ordercode);
                $em->persist($tranretailer);
                $em->persist($trandist);
                $ordercode->addTransaction($trandist);
            }
            $em->persist($ordercode);
            $em->flush();

            if (count($item->getCodes())<=$item->getAlertMinStock())
              $this->forward('hello_di_di_notification:NewAction',array('id'=>null,'type'=>11,'value'=>$item->getItemName()));

            if($accountRet->getAccBalance()+$accountRet->getAccCreditLimit()<=15000)
                $this->forward('hello_di_di_notification:NewAction',array('id'=>$accountRet->getId(),'type'=>31,'value'=>'15000 ' .$accountRet->getAccCurrency()));

            $request->getSession()->set('firstprintcode', true);

            return $this->redirect($this->generateUrl('Retailer_Shop_print'));
        }

        return $this->redirect($this->generateUrl('Retailer_Shop_Error_print'));

    }

    public function PrintAction(Request $request,$print)
    {
        $em = $this->getDoctrine()->getManager();

        $lasttran = $em->getRepository('HelloDiAccountingBundle:Transaction')->findOneBy(array('User'=>$this->getUser(),'tranAction'=>'sale','B2BLog'=>null),array('id'=>'desc'));
        if($lasttran)
        {
            $trans = $em->getRepository('HelloDiAccountingBundle:Transaction')->findBy(array('Order'=>$lasttran->getOrder(),'tranAction'=>'sale'));
            $description = $em->getRepository('HelloDiDiDistributorsBundle:ItemDesc')->findOneBy(array('Item'=>$lasttran->getCode()->getItem(),'desclang'=>$lasttran->getOrder()->getLang()))->getDescdesc();
        }
        else
            $trans = null;

        if($trans == null)
        {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('You_entered_an_invalid',array(),'message'));
            return $this->redirect($this->generateUrl('Retailer_Shop_Error_print'));
        }

        $duplicate = !$request->getSession()->has('firstprintcode');
        $request->getSession()->remove('firstprintcode');

        $html = $this->render('HelloDiDiDistributorsBundle:Retailers:CodePrint.html.twig',array(
                'trans'=>$trans,
                'description'=>str_replace('{{duplicate}}','{{duplicate|raw}}',$description),
                'duplicate'=>$duplicate,
                'print' => $print
            ));

        if($print == 'web')
            return $html;
        else
            return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($html->getContent()),
                200,
                array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => 'attachment; filename="Codes.pdf"'
                )
            );
    }

    private function BuyImtu(Request $request)
    {
        ini_set('max_execution_time', 80);
        $em = $this->getDoctrine()->getManager();

        $mobileNumber = $request->get('receiverMobileNumber');
        $senderMobileNumber = $request->get('senderMobileNumber');
        $senderEmail = $request->get('email');
        $denominationSelected = $request->get('denomination');
        $user = $this->getUser();
        $accountRet = $user->getAccount();
        $item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($denominationSelected);
        $denomination = $em->getRepository('HelloDiDiDistributorsBundle:Denomination')->findOneBy(array('Item'=>$item,'currency'=>$accountRet->getAccCurrency()))->getDenomination();
        $priceRet = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Item'=>$item,'Account'=>$accountRet));
        $priceDist = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Item'=>$item,'Account'=>$accountRet->getParent()));
        $clientTranId= $this->CreateTranId();

        $taxhistory = $em->getRepository('HelloDiDiDistributorsBundle:TaxHistory')->findOneBy(array('Tax'=>$priceDist->getTax(),'taxend'=>null));
        $com = $priceRet->getprice() - $priceDist->getprice();

        $b2blog = new B2BLog();
        $b2blog->setUser($user);
        $b2blog->setAmount($denomination);
        $b2blog->setClientTransactionID($clientTranId);
        $b2blog->setDate(new \DateTime());
        $b2blog->setMobileNumber($mobileNumber);
        $b2blog->setItem($item);
        $b2blog->setSenderMobileNumber($senderMobileNumber);
        $b2blog->setSenderEmail($senderEmail);
        $b2blog->setItem($item);
        $em->persist($b2blog);
        $em->flush();

//        try
//        {
            $client = new SoapClientTimeout($this->container->getParameter('B2BServer.WSDL'));//,array('trace'=>true));
            $client->__setTimeout(60);
            $result = $client->CreateAccount(array(
                    'CreateAccountRequest' => array(
                        'UserInfo' => array(
                            'UserName'=>$this->container->getParameter('B2BServer.UserName'),
                            'Password'=>$this->container->getParameter('B2BServer.Password')
                        ),
                        'ClientReferenceData' => array(
                            'Service'=>'imtu',
                            'ClientTransactionID'=>$clientTranId,
                            'IP'=>$this->container->getParameter('B2BServer.IP'),
                            'TimeStamp'=>  date_format(new \DateTime(),DATE_ATOM)
                        ),
                        'Parameters' => array(
                            'CarrierCode'=>$item->getOperator()->getName(),
                            'CountryCode'=>$item->getCountry()->getIso(),
                            'Amount'=>$denomination*100,
                            'MobileNumber'=>$mobileNumber,
                            'StoreID'=>$this->container->getParameter('B2BServer.StoreID'),
                            'ChargeType'=>'transfer',
                            'Recharge'=>'Y',
                            'SendSMS'=>($senderMobileNumber!=""?"Y":"N"),
                            'SenderNumber'=>$senderMobileNumber,
                            'NotificationMobile'=>$senderMobileNumber,
                            'SendEmail'=>($senderEmail!=""?"Y":"N"),
                            'NotificationEmail'=>$senderEmail,
                        ),
                    )
                ));

                $CreateAccountResponse = $result->CreateAccountResponse;

                if($CreateAccountResponse->ResponseReferenceData->Success == 'N')
                {
                    $b2blog->setTransactionID($CreateAccountResponse->ResponseReferenceData->TransactionID);
                    $b2blog->setStatus(0);
                    $messages = $CreateAccountResponse->ResponseReferenceData->MessageList;
                    $error_codes = "";
                    foreach ($messages as $message)
                    {
                        $error_codes.= $message->StatusCode.',';
                        $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans($message->StatusCode,array(),'message'));
                    }
                    $b2blog->setStatusCode($error_codes);
                    $em->flush();

//                    $s  = "request: ".$client->__getLastRequest() . "<br/>";
//                    $s .= "response: ".$client->__getLastResponse() . "<br/>";
//                    die($s);
                }
                else
                {
                    $b2blog->setTransactionID($CreateAccountResponse->ResponseReferenceData->TransactionID);
                    $b2blog->setStatus(1);

                    // For retailers
                    $tranretailer = new Transaction();
                    $tranretailer->setAccount($accountRet);
                    $tranretailer->setTranAmount(-($priceRet->getPrice()));
                    $tranretailer->setTranFees(0);
                    $tranretailer->setTranDescription('ClientTransactionID: ' . $clientTranId);
                    $tranretailer->setTranDate(new \DateTime('now'));
                    $tranretailer->setSellerB2BLog($b2blog);
                    $b2blog->setSellerTransaction($tranretailer);
                    $em->persist($tranretailer);

                    // For distributors
                    $trandist = new Transaction();
                    $trandist->setAccount($accountRet->getParent());
                    $trandist->setTranAmount($com);
                    $trandist->setTranFees(0);
                    $trandist->setTranDescription('ClientTransactionID: ' . $clientTranId);
                    $trandist->setTranDate(new \DateTime('now'));
                    $trandist->setCommissionerB2BLog($b2blog);
                    $b2blog->setCommissionerTransaction($trandist);
                    $em->persist($trandist);

                    $em->flush();
//                    $s  = "request: ".$client->__getLastRequest() . "<br/>";
//                    $s .= "response: ".$client->__getLastResponse() . "<br/>";
//                    die($s);

                    $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('mobile_number_%mobilenumber%_charged',array('mobilenumber'=>$mobileNumber),'message'));

                    if($accountRet->getAccBalance()+$accountRet->getAccCreditLimit()<=15000)
                        $this->forward('hello_di_di_notification:NewAction',array('id'=>$accountRet->getId(),'type'=>31,'value'=>'15000 ' .$accountRet->getAccCurrency()));

                    return $b2blog->getId();
                }


//            die(print_r($CreateAccountResponse));
//        }
//        catch(\Exception $e)
//        {
//            if($e->getCode() == -99)
//            {
//                $b2blog->setStatusCode("noResponse");
//                $b2blog->setStatus(0);
//                $em->flush();
//
//                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('server_no_response',array(),'message'));
//            }
//            else
//                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('error_b2b',array(),'message'));
//        }
        $em->flush();
        return null;
    }

    public function PrintImtuAction($id,$print)
    {
        $em = $this->getDoctrine()->getManager();
        $b2blog = $em->getRepository("HelloDiDiDistributorsBundle:B2BLog")->find($id);

        if(!$b2blog) throw $this->createNotFoundException("Unable find b2b id.");

        if($b2blog->getUser() != $this->getUser()) throw $this->createNotFoundException("Unable find b2b id. (2)");

        if($b2blog->getStatus() != 1) throw $this->createNotFoundException("imtu buy failed.");

        $description = $em->getRepository('HelloDiDiDistributorsBundle:ItemDesc')->findOneBy(array('Item'=>$b2blog->getItem(),'desclang'=>$this->getUser()->getLanguage()));
        if(!$description) $description = $em->getRepository('HelloDiDiDistributorsBundle:ItemDesc')->findOneBy(array('Item'=>$b2blog->getItem()));
        $description = $description->getDescdesc();

        $denomination = $em->getRepository('HelloDiDiDistributorsBundle:Denomination')->findOneBy(array('Item'=>$b2blog->getItem(),'currency'=>$this->getUser()->getAccount()->getAccCurrency()));

        $html = $this->render('HelloDiDiDistributorsBundle:Retailers:ImtuPrint.html.twig',array(
            'b2b'=>$b2blog,
            'description'=>$description,
            'denomination' => $denomination->getDenomination()." ".$denomination->getCurrency(),
            'print' => $print
        ));

        if($print == 'web')
            return $html;
        else
            return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($html->getContent()),
                200,
                array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => 'attachment; filename="Imtu.pdf"'
                )
            );
    }

    public function ErrorOnPrintAction()
    {
        return $this->render("HelloDiDiDistributorsBundle:Retailers:ErrorOnPrint.html.twig");
    }

    public function DmtuAction(){
        $em = $this->getDoctrine()->getManager();
        $Account = $this->get('security.context')->getToken()->getUser()->getAccount();

        $qb = $em->createQueryBuilder()
            ->select('p')
            ->from('HelloDiDiDistributorsBundle:Price','p')
            ->innerJoin('p.Item','i')
            ->where('i.itemType = :type')->setParameter('type','dmtu')
            ->andWhere('p.Account = :account')->setParameter('account',$Account)
            ->andWhere('p.priceStatus = 1');

        $prices=$qb->getQuery()->getResult();

        return $this->render('HelloDiDiDistributorsBundle:Retailers:ShopDmtu.html.twig',array(
            'Prices'=>$prices,
            'Account'=>$Account,
        ));
    }

    public function CallingCardAction() {
        $em = $this->getDoctrine()->getManager();
        $Account = $this->get('security.context')->getToken()->getUser()->getAccount();

        $qb = $em->createQueryBuilder()
            ->select('p')
            ->from('HelloDiDiDistributorsBundle:Price','p')
            ->innerJoin('p.Item','i')
            ->where('i.itemType = :type')->setParameter('type','clcd')
            ->andWhere('p.Account = :account')->setParameter('account',$Account)
            ->andWhere('p.priceStatus = 1');

        $prices=$qb->getQuery()->getResult();

        return $this->render('HelloDiDiDistributorsBundle:Retailers:CallingCard.html.twig',array(
            'Prices'=>$prices,
            'Account'=>$Account,
        ));
    }

    public function EpaymentAction() {
        $em = $this->getDoctrine()->getManager();
        $Account = $this->get('security.context')->getToken()->getUser()->getAccount();

        $qb = $em->createQueryBuilder()
            ->select('p')
            ->from('HelloDiDiDistributorsBundle:Price','p')
            ->innerJoin('p.Item','i')
            ->where('i.itemType = :type')->setParameter('type','epmt')
            ->andWhere('p.Account = :account')->setParameter('account',$Account)
            ->andWhere('p.priceStatus = 1');

        $prices=$qb->getQuery()->getResult();

        return $this->render('HelloDiDiDistributorsBundle:Retailers:E-payment.html.twig',array(
            'Prices'=>$prices,
            'Account'=>$Account,
        ));
    }

    public function ImtuAction(Request $request)
    {
//        $n = "1934567";
//
//        $a = "12";
//        $b = "19";
//        $c = "1933";
//        $d = "1934";
//        $e = "1935";
//        $f = "2345";
//
//        $resa = strcmp($n,$a);
//        $resb = strcmp($n,$b);
//        $resc = strcmp($n,$c);
//        $resd = strcmp($n,$d);
//        $rese = strcmp($n,$e);
//        $resf = strcmp($n,$f);
//
//        die('|'.$resa.'|'.$resb.'|'.$resc.'|'.$resd.'|'.$rese.'|'.$resf.'|');
        $B2B_log_id = null;
        if($request->isMethod("post"))
        {
            $result = $this->BuyImtu($request);
            if($result) $B2B_log_id = $result;

        }
        return $this->render('HelloDiDiDistributorsBundle:Retailers:ShopImtu.html.twig',array(
                'b2b_log_id' => $B2B_log_id
            ));
    }

    public function readNumberAction(Request $request)
    {
        $number = $request->get("receiver");

        $em = $this->getDoctrine()->getManager();

        if(!$number || !is_numeric($number)) return  new Response("  <option value=''>Invalid Mobile Number</option>");
        $number = ltrim($number,"0+-");
        if(strlen($number)<6)  return  new Response("  <option value=''>Invalid Mobile Number</option>");

        $mobile_country_roles=$this->container->getParameter('mobile_country_roles');

        $role = null;
        foreach($mobile_country_roles as $mobile_country_role)
        {
            $number_country_code = (int)substr($number,0,strlen($mobile_country_role['country_code']));
            if($number_country_code == $mobile_country_role['country_code'])
                if($mobile_country_role['mobile_min_length'] <= strlen($number) && strlen($number) <= $mobile_country_role['mobile_max_length'])
                    $role = $mobile_country_role;
        }

        if (!$role) return new Response("  <option value=''>Not found Operator</option>");

        $operators = $em->createQueryBuilder()
            ->select('operator')
            ->from("HelloDiDiDistributorsBundle:Operator",'operator')
            ->innerJoin('operator.Item','item')
            ->innerJoin('item.Prices','price')
            ->where('price.Account = :account')->setParameter("account",$this->getUser()->getAccount())
            ->andWhere('price.priceStatus = :true')->setParameter("true",true)
            ->innerJoin('item.Country','country')
            ->andWhere('country.iso = :country_iso')->setParameter('country_iso',$role['country_iso'])
            ->getQuery()->getResult();

        $result = "";
        foreach($operators as $operator)
            $result .= "<option value='".$operator->getId()."'>".$operator->getName()."</option>";

//        $file = file("../app/Resources/phones_rules/phones_rules.csv");
//
//        $array = array();
//
//        foreach($file as $line)
//        {
//            $row = str_getcsv($line,",");
//            $length = strlen($row[2]);
//            if(!isset($array[$length])) $array[$length] = array();
//            $array[$length] []= array(
//                "country_iso"=>$row[1],
//                "number_min_length"=>(int)$row[3],
//                "number_max_length"=>(int)$row[4],
//                "operator_code"=>(int)$row[2],
//                "operator_name"=>$row[5],
//            );
//        }
//
//        $dumper = new Dumper();
//
//        $yaml = $dumper->dump($array,2);
//
//        file_put_contents('phones_rules.yml', $yaml);

        $country_name = $em->getRepository("HelloDiDiDistributorsBundle:Country")->findOneBy(array("iso"=>$role['country_iso']));

        return  new Response($result?$country_name->getName().$result:"  <option value=''>Not found Operator</option>");
    }

    public function getPricesAction(Request $request)
    {
        $operatorID = $request->get("operatorID");
        $country_name = $request->get("country");

        $em = $this->getDoctrine()->getManager();

        $country = $em->getRepository("HelloDiDiDistributorsBundle:Country")->findOneBy(array("name"=>$country_name));

        $operator = $em->getRepository("HelloDiDiDistributorsBundle:Operator")->find($operatorID);

        if(!$operator) return  new Response("<option value=''>Not found Denomination.</option>");

        $prices = $em->createQueryBuilder()
            ->select('price')
            ->from("HelloDiDiDistributorsBundle:Price","price")
            ->where('price.Account = :account')->setParameter("account",$this->getUser()->getAccount())
            ->andWhere('price.priceStatus = :true')->setParameter("true",true)
            ->innerJoin("price.Item","item")
            ->andWhere("item.operator = :operator")->setParameter("operator",$operator)
            ->andWhere('item.Country = :country')->setParameter('country',$country)
            ->innerJoin("item.Denominations","denomination")
            ->andWhere('denomination.currency = :currency')->setParameter('currency',$this->getUser()->getAccount()->getAccCurrency())
            ->getQuery()->getResult();

        $result = "";
        foreach($prices as $price)
            $result .= "<option value='".$price->getItem()->getId()."'>"
                .$price->getItem()->getDenominationByCurrency($price->getAccount()->getAccCurrency())." ".$price->getAccount()->getAccCurrency()
                ." (".$price->getItem()->getItemFaceValue()." ".$price->getItem()->getItemCurrency().")"
                ."</option>";

        return  new Response($result?:"<option value=''>Not found Denomination.</option>");
    }

    public  function FavouritesAction()
    {
        $em = $this->getDoctrine()->getManager();
        $Account = $this->get('security.context')->getToken()->getUser()->getAccount();

        $qb = $em->createQueryBuilder()
            ->select('p')
            ->from('HelloDiDiDistributorsBundle:Price','p')
            ->innerJoin("p.Item","i")
            ->where('p.isFavourite = 1')
            ->andWhere('p.Account = :account')->setParameter('account',$Account)
            ->andWhere("i.itemType != :type")->setParameter("type","imtu")
            ->andWhere('p.priceStatus = 1');

        $prices=$qb->getQuery()->getResult();

        return $this->render('HelloDiDiDistributorsBundle:Retailers:favourite.html.twig',array(
            'Prices'=>$prices,
            'Account'=>$Account,
        ));
    }
 // End kamal

//start mostafa
    public function ShowItemsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $myaccount = $this->getUser()->getAccount();

        $prices = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findBy(array('Account'=>$myaccount,'priceStatus'=>1));

        return $this->render('HelloDiDiDistributorsBundle:Retailers:items.html.twig', array(
                'prices' => $prices,
                'Account' => $myaccount
            ));
    }

    public function SwitchFavoriteItemAction($priceid)
    {
        $this->check_Price($priceid);
        $em = $this->getDoctrine()->getManager();
        $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->find($priceid);

        $price->setIsFavourite(!$price->getIsFavourite());
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
        return $this->redirect($this->generateUrl('Retailer_Items_Show'));
    }
//end mostafa

// check functions
    private function check_User($userid)
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('HelloDiDiDistributorsBundle:User')->find($userid);
        if($user == null || $user->getAccount() == null || $user->getAccount() != $myaccount)
        {
            throw new \Exception($this->get('translator')->trans('have_not_permission_%object%',array('object'=>$this->get('translator')->trans('User',array(),'user')),'message'));
        }
    }

    private function check_Transaction($tranid)
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
        $em = $this->getDoctrine()->getManager();
        $tran = $em->getRepository('HelloDiAccountingBundle:Transaction')->find($tranid);
        if($tran == null || $tran->getAccount() == null || $tran->getAccount() != $myaccount)
        {
            throw new \Exception($this->get('translator')->trans('have_not_permission_%object%',array('object'=>$this->get('translator')->trans('Transaction',array(),'transaction')),'message'));
        }
    }

    private function check_Ticket($ticketid)
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
        $em = $this->getDoctrine()->getManager();
        $ticket = $em->getRepository('HelloDiDiDistributorsBundle:Ticket')->find($ticketid);
        if($ticket == null || $ticket->getAccountretailer() == null || $ticket->getAccountretailer() != $myaccount)
        {
            throw new \Exception($this->get('translator')->trans('have_not_permission_%object%',array('object'=>$this->get('translator')->trans('Ticket',array(),'ticket')),'message'));
        }
    }

    private function check_Price($priceid)
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
        $em = $this->getDoctrine()->getManager();
        $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->find($priceid);
        if($price == null || $price->getAccount() == null || $price->getAccount() != $myaccount)
        {
            throw new \Exception($this->get('translator')->trans('have_not_permission_%object%',array('object'=>$this->get('translator')->trans('Price',array(),'price')),'message'));
        }
    }
// end check


//kezem

    public function RetailerLoadActiowOwnAction(Request $req)
    {
        $id=$req->get('id',0);
        $value='';


        switch($id)
        {
            case 0:

                $value.='<option value="sale">'.
                    $this->get('translator')->trans('debit_balance_when_the_retailer_sell_a_code',[],'transaction')
                    .'</option>';
                break;

            case 1:


                $value.='<option value="All">'.
                    $this->get('translator')->trans('All',[],'transaction')
                    .'</option>';

                $value.='<option value="crnt">'.
                    $this->get('translator')->trans('issue_a_credit_note_for_a_sold_code',[],'transaction')
                    .'</option>';

                $value.='<option value="tran">'.
                    $this->get('translator')->trans('transfer_credit_from_distributor,s_account_to_a_retailer,s_account',[],'transaction')
                    .'</option>';

                $value.='<option value="ogn_pmt">'.
                    $this->get('translator')->trans( 'ogone_payment_on_its_own_account',[],'transaction')
                    .'</option>';


                break;

            case 2:

                $value.='<option value="All">'.
                    $this->get('translator')->trans('All',[],'transaction')
                    .'</option>';

                $value.='<option value="sale">'.
                    $this->get('translator')->trans('debit_balance_when_the_retailer_sell_a_code',[],'transaction')
                    .'</option>';

                $value.='<option value="crnt">'.
                    $this->get('translator')->trans('issue_a_credit_note_for_a_sold_code',[],'transaction')
                    .'</option>';

                $value.='<option value="tran">'.
                    $this->get('translator')->trans('transfer_credit_from_distributor,s_account_to_a_retailer,s_account',[],'transaction')
                    .'</option>';

                $value.='<option value="ogn_pmt">'.
                    $this->get('translator')->trans('ogone_payment_on_its_own_account',[],'transaction')
                    .'</option>';


                break;
        }
        return new Response($value);
    }

    public function CreateTranId()
    {
        $userid = $this->getUser()->getId();
        return "HD-".sprintf("%05s",$userid).'-'.(new \DateTime())->getTimestamp();
    }
}

