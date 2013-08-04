<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Entity\OrderCode;
use HelloDi\DiDistributorsBundle\Entity\Ticket;
use HelloDi\DiDistributorsBundle\Entity\TicketNote;
use \HelloDi\DiDistributorsBundle\Form\Retailers\NewUserType;
use HelloDi\DiDistributorsBundle\Entity\User;
use HelloDi\DiDistributorsBundle\Form\Distributors\NewUserDistributorsType;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HelloDi\DiDistributorsBundle\Entity\Account;
use HelloDi\DiDistributorsBundle\Listener\BalanceChecker;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use HelloDi\DiDistributorsBundle\Entity\Transaction;
use HelloDi\DiDistributorsBundle\Form\TransactionType;


class RetailersController extends Controller
{
    public function dashboardAction()
    {

        $user = $this->get('security.context')->getToken()->getUser();
        $Account = $user->getAccount();

        return $this->render('HelloDiDiDistributorsBundle:Retailers:dashboard.html.twig',array(
            'Account' => $Account,


        ));
    }


  public  function  countnoteAction()
  {
      $User = $this->get('security.context')->getToken()->getUser();
      $em=$this->getDoctrine()->getEntityManager();
      $Countnote=$em->createQueryBuilder();
      $Countnote->select('Note')
          ->from('HelloDiDiDistributorsBundle:TicketNote','Note')
          ->innerJoin('Note.Ticket','NoteTic')
          ->Where('NoteTic.Accountretailer = :Accr')->setParameter('Accr',$User->getAccount())
          ->andWhere('NoteTic.Accountdist = :Accd')->setParameter('Accd',$User->getAccount()->getParent())
          ->orWhere($Countnote->expr()->isNull('NoteTic.Accountdist'))
          ->andWhere('Note.User != :usr')->setParameter('usr',$User)
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
        $em=$this->getDoctrine()->getEntityManager();
        $user=$this->get('security.context')->getToken()->getUser();

        $qb=$em->createQueryBuilder()
                  ->select('USR')
                  ->from('HelloDiDiDistributorsBundle:User','USR')
                  ->Where('USR.Account = :Acc')->setParameter('Acc',$user->getAccount())
                  ->andWhere('USR.Entiti = :Ent')->setParameter('Ent',$user->getEntiti())
                 ->andwhere('USR != :u')->setParameter('u',$user);
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

        $form = $this->createForm(new \HelloDi\DiDistributorsBundle\Form\User\NewUserType('HelloDiDiDistributorsBundle\Entity\User',2), $user, array('cascade_validation' => true));
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $user->setAccount($Account);
            $user->setEntiti($Entiti);
            if ($form->isValid()) {
                $em->persist($user);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success','this operation done success !');
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
        $form = $this->createForm(new \HelloDi\DiDistributorsBundle\Form\User\NewUserType('HelloDiDiDistributorsBundle\Entity\User',2), $user, array('cascade_validation' => true));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->flush();
                $this->get('session')->getFlashBag()->add('success','this operation done success !');
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
        $em=$this->getDoctrine()->getEntityManager();
        $qb=array();

        $form=$this->createFormBuilder()

            ->add('TypeDate','choice', array(
                'expanded'   => true,
                'choices'    => array(
                    0 => 'Trade Date',
                    1 => 'booking Date',
                )))
            ->add('DateStart','text',array('required'=>false,'label'=>'From:'))
            ->add('DateEnd','text',array('required'=>false,'label'=>'To:'))

            ->add('Type','choice',array('label'=>'Type:',
                'choices'=> array(
                    2=>'All',
                    1=>'Credit',
                    0=>'Debit'
                )))

            ->add('Action','choice',array('label'=>'Action:','data'=>'20',
                'choices'=> array(
                    'All'=>'All',
                    'sale'=>'debit balance when the retailer sell a code',
                    'crnt'=>'issue a credit note for a sold code',
                    'tran'=>'transfer credit from distributor,s account to a retailer,s account',
                    'ogn_pmt'=>'ogone payment on its own account'
                )))->getForm();
$datetype=0;

        if($req->isMethod('POST'))
        {


            $form->handleRequest($req);
            $data=$form->getData();
            $qb=$em->createQueryBuilder();
            $qb->select('Tran')
                ->from('HelloDiDiDistributorsBundle:Transaction','Tran')
                ->where('Tran.Account =:Acc')->setParameter('Acc',$Account);
            if($data['TypeDate']==0)
            {
             if($data['DateStart']!='')
                $qb->where('Tran.tranDate >= :DateStart')->setParameter('DateStart',$data['DateStart']);
             if($data['DateEnd']!='')
                $qb->andwhere('Tran.tranDate <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);

            }

            if($data['TypeDate']==1)
            {$datetype=1;
                if($data['DateStart']!='')
                $qb->where('Tran.tranInsert >= :DateStart')->setParameter('DateStart',$data['DateStart']);
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


    public function DetailsTransactionAction($id)
    {
        $this->check_Transaction($id);

        $em=$this->getDoctrine()->getManager();
        $Tran=$em->getRepository('HelloDiDiDistributorsBundle:Transaction')->find($id);
        return $this->render('HelloDiDiDistributorsBundle:Retailers:DetailsTransaction.html.twig',
            array(
                'tran'=>$Tran,
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
            array('label'=>'Type:',
                'choices'=>array(
                    'All' => 'All',
                     'dmtu'=>'mobile',
                     'clcd'=>'calling card',
                     'empt'=>'e-payment'
                  )))

            ->add('ItemName', 'entity',
                  array(
                      'required'=>false,
                 'label'=>'Item:',
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
                array(
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


  $form=$form->add('DateStart','text',array('required'=>false,'label'=>'From:'))
             ->add('DateEnd','text',array('required'=>false,'label'=>'To:'))->getForm();

        if($req->isMethod('POST'))
        {
            $form->handleRequest($req);
            $data=$form->getData();
            $qb=$em->createQueryBuilder();
            $qb->select(array('Tr'))
                ->from('HelloDiDiDistributorsBundle:Transaction','Tr')
                /*for groupBy*/
                ->innerJoin('Tr.Code','TrCo')
                ->innerJoin('TrCo.Item','TrCoIt')
                /**/
                ->Where($qb->expr()->like('Tr.tranAction',$qb->expr()->literal('sale')));
            if($data['DateStart'])
                $qb->andwhere('Tr.tranDate >= :DateStart')->setParameter('DateStart',$data['DateStart']);
            if($data['DateEnd'])
                $qb->andwhere('Tr.tranDate <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);

            if($data['Staff']!='')
              $qb->andWhere('Tr.User = :usr')->setParameter('usr',$data['Staff']);

//
            if($data['ItemType']!='All')
                $qb->andwhere('TrCoIt.itemType =:ItemType')->setParameter('ItemType',$data['ItemType']);


            if($data['ItemName']!='')
                 $qb->andWhere($qb->expr()->like('TrCoIt.itemName',$qb->expr()->literal($data['ItemName']->getItemName())));

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
            'Entiti' =>$User->getEntiti()));

    }



    public function ticketsAction(Request $req)
    {

        $User=$this->get('security.context')->getToken()->getUser();
        $em=$this->getDoctrine()->getEntityManager();

        $form=$this->createFormBuilder()
            ->add('Type','choice',array('choices'=>array(
                5=>'All',
                0=>'Payment issue',
                1=>'new item request',
                2=>'price change request'
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
            if($data['Type']!=5)
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
            $this->get('request')->query->get('page', 1) /*page number*/,
           10/*limit per page*/
        );

        return $this->render('HelloDiDiDistributorsBundle:Retailers:Tickets.html.twig',array(
            'pagination'=>$pagination,
            'Account'=>$User->getAccount(),
            'User'=>$User,
            'form'=>$form->createView()
        ));

    }


    public  function ticketsnewAction(Request $req)
    {
        $User=$this->get('security.context')->getToken()->getUser();

        $em=$this->getDoctrine()->getEntityManager();
        $Tick=new Ticket();
        $TickNote=new TicketNote();
        $form=$this->createFormBuilder()
            ->add('Contact','choice',array(
                'expanded'=>true,
                'multiple'=>false,
                'choices'=>array(0=>'Distributors',1=>'Support Team')
            ))
            ->add('Subject','text')
            ->add('Type','choice',array(
                'choices'=>array(
                    0=>'Payment',
                    1=>'new item request',
                    2=>'price change request'
                )
            ))
            ->add('Description','textarea',array('required'=>true))
            ->getForm();

        if($req->isMethod('POST'))
        {
            $form->handleRequest($req);
            $data=$form->getData();

            $Tick->setUser($User);
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
        $em=$this->getDoctrine()->getEntityManager();
        $ticket=$em->getRepository('HelloDiDiDistributorsBundle:Ticket')->find($id);



        $form=$this->createFormBuilder()
            ->add('Description','textarea',array('required'=>true,
                'label'=>'Description:'
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



        $noteslist=$em->getRepository('HelloDiDiDistributorsBundle:TicketNote')->findBy(array('Ticket'=>$ticket));


///update vi
        $notesview=$em->createQueryBuilder();
        $notesview->update('HelloDiDiDistributorsBundle:TicketNote Note')
            ->set('Note.view',1)
            ->Where('Note.User != :usr')->setParameter('usr',$User)
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

        return $this->redirect($this->generateUrl('RetailerTickets'));
    }


    public  function  ticketsstatusAction($id)
    {
        $this->check_Ticket($id);
        $em=$this->getDoctrine()->getEntityManager();

        $ticket=$em->getRepository('HelloDiDiDistributorsBundle:Ticket')->find($id);

        $ticket->setStatus(1);
        $ticket->setTicketStart(new \DateTime('now'));
        $ticket->setTicketEnd(null);

        $em->flush();

        return $this->redirect($this->generateUrl('RetailerTicketsNote',array('id'=>$id)));

    }






//--------endkazem--------//

// Start kamal

        public function DmtuAction(){


        $em = $this->getDoctrine()->getEntityManager();
        $Account = $this->get('security.context')->getToken()->getUser()->getAccount();

            $qb = $em->createQueryBuilder();
            $qb->select('O.Logo as oprlogo','OI.itemName as itemname','OI.id as itemid','O.name as oprname','OI.itemFaceValue as itemfv','OI.itemCurrency as itemcur','OIP.id as priceid')
                ->from('HelloDiDiDistributorsBundle:Operator','O')
                ->innerJoin('O.Item','OI')
                ->innerJoin('OI.Prices','OIP')
                ->Where($qb->expr()->like('OI.itemType',$qb->expr()->literal('dmtu')))
                ->andwhere('OIP.Account = :Acc')->setParameter('Acc',$Account)
                ->andwhere('OIP.priceStatus = 1');
            $qb=$qb->getQuery();
            $qb=$qb->getResult();


       return $this->render('HelloDiDiDistributorsBundle:Retailers:ShopDmtu.html.twig',array
       (
            'Operators'=>$qb,
            'Account'=>$Account,
              ));

        }

    public function BuyAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->container->get('security.context')->getToken()->getUser();

        $codeselector = $this->get('hello_di_di_distributors.codeselector');

        $priceChild = $em->getRepository('HelloDiDiDistributorsBundle:Price')->find($request->get('price_id'));
        $Account = $priceChild->getAccount();
        $item = $priceChild->getItem();

        $codes = $codeselector->lookForAvailableCode($Account, $priceChild, $item, $request->get('numberOfsale'));

        $priceParent = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Account'=> $Account->getParent(),'Item' => $item));

        $com = $priceChild->getprice() - $priceParent->getprice();

        if ($codes) {
            $ordercode = new OrderCode();
            $em->persist($ordercode);
            foreach ($codes as $code) {
                $tranretailer = new Transaction();
                $trandist = new Transaction();
                //   for retailer
                $tranretailer->setAccount($Account);
                $tranretailer->setTranAmount(-($priceChild->getPrice()));
                $tranretailer->setTranFees(0);
                $tranretailer->setTranDescription('Code id is: ' . $code->getId());
                $tranretailer->setTranCurrency($Account->getAccCurrency());
                $tranretailer->setTranDate(new \DateTime('now'));
                $tranretailer->setTranInsert(new \DateTime('now'));
                $tranretailer->setCode($code);
                $tranretailer->setTranAction('sale');
                $tranretailer->setTranType(0);
                $tranretailer->setUser($user);
                $tranretailer->setTranBookingValue(null);
                $tranretailer->setTranBalance($Account->getAccBalance());

                $tranretailer->setOrder($ordercode);
                $ordercode->addTransaction($tranretailer);

                // For distributors
                $trandist->setAccount($Account->getParent());
                $trandist->setTranAmount($com);
                $trandist->setTranFees(0);
                $trandist->setTranDescription('Code id is: ' . $code->getId());
                $trandist->setTranCurrency($Account->getParent()->getAccCurrency());
                $trandist->setTranDate(new \DateTime('now'));
                $trandist->setTranInsert(new \DateTime('now'));
                $trandist->setCode($code);
                $trandist->setTranAction('com');
                $trandist->setTranType(1);
                $trandist->setUser($user);
                $trandist->setTranBookingValue(null);
                $trandist->setTranBalance($Account->getParent()->getAccBalance());
                $em->persist($tranretailer);
                $em->persist($trandist);

                $trandist->setOrder($ordercode);
                $ordercode->addTransaction($trandist);
            }
            $em->flush();

            $description = $em->getRepository('HelloDiDiDistributorsBundle:ItemDesc')->findOneBy(array('Item' => $item, 'desclang' => $user->getLanguage()));

            $request->getSession()->set('descriptionid', $description->getId());
            $request->getSession()->set('orderid', $ordercode->getId());
            $request->getSession()->set('firstprintcode', true);


            return $this->redirect($this->generateUrl('Retailer_Shop_print'));
        }

        return $this->redirect($this->getRequest()->headers->get('referer'));

    }

    public function PrintAction(Request $request,$print)
    {
        $em = $this->getDoctrine()->getManager();
        $descriptionid = $request->getSession()->get('descriptionid');
        $orderid = $request->getSession()->get('orderid');

        $duplicate = !$request->getSession()->has('firstprintcode');
        $request->getSession()->remove('firstprintcard');

        $description = $em->getRepository('HelloDiDiDistributorsBundle:ItemDesc')->find($descriptionid);
        $trans = $em->getRepository('HelloDiDiDistributorsBundle:Transaction')->findBy(array('Order'=>$orderid,'tranAction'=>'sale'));

        $html = $this->render('HelloDiDiDistributorsBundle:Retailers:CodePrint.html.twig',array(
            'trans'=>$trans,
            'description'=>$description,
            'duplicate'=>$duplicate,
            'print' => $print
        ));

        if($print == 'web')
            return $html;
        else
            return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
                200,
                array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => 'attachment; filename="Codes.pdf"'
                )
            );
    }

    public function CallingCardAction() {

        $em = $this->getDoctrine()->getEntityManager();
        $Account = $this->get('security.context')->getToken()->getUser()->getAccount();

        $qb = $em->createQueryBuilder();
        $qb->select('O.Logo as oprlogo','OI.itemName as itemname','OI.id as itemid','O.name as oprname','OI.itemFaceValue as itemfv','OI.itemCurrency as itemcur','OIP.id as priceid')
            ->from('HelloDiDiDistributorsBundle:Operator','O')
            ->innerJoin('O.Item','OI')
            ->innerJoin('OI.Prices','OIP')
            ->Where($qb->expr()->like('OI.itemType',$qb->expr()->literal('clcd')))
            ->andwhere('OIP.Account = :Acc')->setParameter('Acc',$Account)
            ->andwhere('OIP.priceStatus = 1');
        $qb=$qb->getQuery();
        $qb=$qb->getResult();


        return $this->render('HelloDiDiDistributorsBundle:Retailers:CallingCard.html.twig',array
        (
            'Operators'=>$qb,
            'Account'=>$Account,
        ));

    }

    public  function FavouritesAction(Request $request ){


        $em = $this->getDoctrine()->getEntityManager();
        $Account = $this->get('security.context')->getToken()->getUser()->getAccount();

        $qb = $em->createQueryBuilder();
        $qb->select('O.Logo as oprlogo','OI.itemName as itemname','OI.id as itemid','O.name as oprname','OI.itemFaceValue as itemfv','OI.itemCurrency as itemcur','OIP.id as priceid')
            ->from('HelloDiDiDistributorsBundle:Operator','O')
            ->innerJoin('O.Item','OI')
            ->innerJoin('OI.Prices','OIP')
            ->where('OIP.isFavourite = 1')
            ->andwhere('OIP.Account = :Acc')->setParameter('Acc',$Account)
            ->andwhere('OIP.priceStatus = 1');
        $qb=$qb->getQuery();
        $qb=$qb->getResult();


        return $this->render('HelloDiDiDistributorsBundle:Retailers:favourite.html.twig',array
        (
            'Operators'=>$qb,
            'Account'=>$Account,
        ));



    }
 // End kamal

//start mostafa
    public function ShowItemsAction()
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();

        $prices = $myaccount->getPrices();

        return $this->render('HelloDiDiDistributorsBundle:Retailers:items.html.twig', array(
                'prices' => $prices,
                'Account' => $myaccount
            ));
    }

    public function SwitchFavoriteItemAction($priceid)
    {
        $em = $this->getDoctrine()->getManager();
        $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->find($priceid);

        $price->setIsFavourite(!$price->getIsFavourite());
        $em->flush();

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
            throw new \Exception("You haven't permission to access this User !");
        }
    }

    private function check_Transaction($tranid)
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
        $em = $this->getDoctrine()->getManager();
        $tran = $em->getRepository('HelloDiDiDistributorsBundle:Transaction')->find($tranid);
        if($tran == null || $tran->getAccount() == null || $tran->getAccount() != $myaccount)
        {
            throw new \Exception("You haven't permission to access this Transaction !");
        }
    }

    private function check_Ticket($ticketid)
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
        $em = $this->getDoctrine()->getManager();
        $ticket = $em->getRepository('HelloDiDiDistributorsBundle:Ticket')->find($ticketid);
        if($ticket == null || $ticket->getAccountretailer() == null || $ticket->getAccountretailer() != $myaccount)
        {
            throw new \Exception("You haven't permission to access this Ticket !");
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

            $value.='<option value="sale">'.'debit balance when the retailer sell a code'.'</option>';


            break;

        case 1:

            $value.='<option value="All">'.'All'.'</option>';
            $value.='<option value="crnt">'.'issue a credit note for a sold code'.'</option>';
            $value.='<option value="tran">'.'transfer credit from distributor,s account to a retailer,s account'.'</option>';
            $value.='<option value="ogn_pmt">'.'ogone payment on its own account'.'</option>';

            break;

        case 2:

            $value.='<option value="All">'.'All'.'</option>';
            $value.='<option value="sale">'.'debit balance when the retailer sell a code'.'</option>';
            $value.='<option value="crnt">'.'issue a credit note for a sold code'.'</option>';
            $value.='<option value="tran">'.'transfer credit from distributor,s account to a retailer,s account'.'</option>';
            $value.='<option value="ogn_pmt">'.'ogone payment on its own account'.'</option>';

            break;
    }
    return new Response($value);
}




}

