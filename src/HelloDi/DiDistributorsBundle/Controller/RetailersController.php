<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Entity\Ticket;
use HelloDi\DiDistributorsBundle\Entity\TicketNote;
use \HelloDi\DiDistributorsBundle\Form\Retailers\NewUserType;
use HelloDi\DiDistributorsBundle\Entity\User;
use HelloDi\DiDistributorsBundle\Form\Distributors\NewUserDistributorsType;
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
        $Account = $this->get('security.context')->getToken()->getUser()->getAccount();
        $users = $Account->getUsers();
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $users,
            $this->get('request')->query->get('page', 1) /*page number*/,
            6/*limit per page*/
        );
        return $this->render('HelloDiDiDistributorsBundle:Retailers:Staff.html.twig', array('Entiti' => $Account->getEntiti(), 'pagination' => $pagination));
    }

    public function RetailerStaffAddAction(Request $request)
    {
        $user = new User();
        $em = $this->getDoctrine()->getManager();
        $Account = $this->get('security.context')->getToken()->getUser()->getAccount();
        $Entiti = $Account->getEntiti();

        $form = $this->createForm(new NewUserType('HelloDiDiDistributorsBundle\Entity\User'), $user, array('cascade_validation' => true));
        $formrole = $this->createFormBuilder()
            ->add('roles', 'choice', array('choices' => array('ROLE_RETAILER' => 'ROLE_RETAILER', 'ROLE_RETAILER_ADMIN' => 'ROLE_RETAILER_ADMIN')))->getForm();

        if ($request->isMethod('POST')) {
            $form->bind($request);
            $formrole->bind($request);
            $data = $formrole->getData();
            $user->addRole(($data['roles']));
            $user->setAccount($Account);
            $user->setEntiti($Entiti);
            $user->setStatus(1);
            if ($form->isValid()) {
                $em->persist($user);
                $em->flush();

                return $this->redirect($this->generateUrl('RetailerStaff', array('id' => $Account->getId())));

            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Retailers:StaffAdd.html.twig', array('Entiti' => $Account->getEntiti(), 'Account' => $Account, 'form' => $form->createView(), 'formrole' => $formrole->createView()));

    }

    public function RetailerStaffEditAction(Request $request, $id)
    {
        $this->check_User($id);

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('HelloDiDiDistributorsBundle:User')->find($id);
        $form = $this->createForm(new NewUserType('HelloDiDiDistributorsBundle\Entity\User'), $user, array('cascade_validation' => true));

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                if ($user->getStatus() == 0)
                    $user->setStatus(0);
                else
                    $user->setStatus(1);
                $em->flush();
                return $this->redirect($this->generateUrl('RetailerStaff', array('id' => $user->getAccount()->getId())));
            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Retailers:StaffEdit.html.twig', array('Account' => $user->getAccount(), 'Entiti' => $user->getEntiti(), 'userid' => $id, 'form' => $form->createView()));

    }

    public function RetailerChangeRoleAction($id)
    {
        $this->check_User($id);

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('HelloDiDiDistributorsBundle:User')->find($id);
        $roles = $user->getRoles();
        $role = $roles[0];
        switch ($role) {

            case 'ROLE_RETAILER':
                $user->removeRole('ROLE_RETAILER');
                $user->addRole('ROLE_RETAILER_ADMIN');
                break;

            case 'ROLE_RETAILER_ADMIN':
                $user->removeRole('ROLE_RETAILER_ADMIN');
                $user->addRole('ROLE_RETAILER');
                break;
        }

        $em->flush();
        return $this->redirect($this->generateUrl('RetailerStaff', array('id' => $user->getAccount()->getId())));

    }

    public function TransactionAction(Request $req)
    {
        $User= $this->get('security.context')->getToken()->getUser();
        $Account=$User->getAccount();
        $em=$this->getDoctrine()->getManager();
        $query=$em->getRepository('HelloDiDiDistributorsBundle:Transaction')->findBy(array('Account'=>$Account,'User'=>$User));

        $form=$this->createFormBuilder()
            ->add('Type','choice',array('choices'=>array('All'=>'All','Sale'=>'Sale','Paym'=>'Payment','Cred'=>'CreditNotes','Tras'=>'Transfer','Add'=>'Add')))
            ->add('DateStart','date',array())
            ->add('DateEnd','date',array())
            ->add('TypeDate','choice', array(
                'expanded'   => true,
                'choices'    => array(
                    0 => 'Trade Date',
                    1 => 'Looking Date',
                )
            ))->getForm();

        if($req->isMethod('POST'))
        {
            $form->bind($req);
            $data=$form->getData();
            $qb=$em->createQueryBuilder();
            $qb->select('Tran')
                ->from('HelloDiDiDistributorsBundle:Transaction','Tran');
            if($data['TypeDate']==0)
            {

                $qb=$qb->where('Tran.tranDate >= :DateStart')->setParameter('DateStart',$data['DateStart']);
                $qb=$qb->andwhere('Tran.tranDate <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);

            }

            if($data['TypeDate']==1)
            {

                $qb=$qb->where('Tran.tranInsert >= :DateStart')->setParameter('DateStart',$data['DateStart']);
                $qb=$qb->andwhere('Tran.tranInsert <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);

            }

            if($data['Type']!='All')
            {
                $qb=$qb->andWhere($qb->expr()->like('Tran.tranAction',$qb->expr()->literal($data['Type'])));

            }

            $query=$qb->getQuery();
        }
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1) /*page number*/,
            5/*limit per page*/
        );

        return $this->render('HelloDiDiDistributorsBundle:Retailers:Transaction.html.twig', array('pagination'=>$pagination,'form'=>$form->createView(),'Account' =>$Account, 'Entiti' =>$User->getEntiti()));

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
         $query=null;
        //load first list search

        $qb=$em->createQueryBuilder();
        $qb->select('Tr')
            ->from('HelloDiDiDistributorsBundle:Transaction','Tr')
            /*for GroupBy*/  ->innerJoin('Tr.Code','TrCo')->innerJoin('TrCo.Item','TrCoIt')->innerJoin('Tr.Account','TrAc')
            ->Where($qb->expr()->like('Tr.tranAction',$qb->expr()->literal('sale')))
            ->andwhere('Tr.User=:ur')->setParameter('ur',$User);
             $query=$qb->getQuery();



        $form=$this->createFormBuilder()

            ->add('ItemType','choice',
            array('choices'=>
            array('3'=>'All','1'=>'Item.TypeChioce.Internet','0' =>'Item.TypeChioce.Mobile','2' =>'Item.TypeChioce.Tel')))

            ->add('ItemName', 'entity',
                  array(
                 'empty_data' => 'All',
                'class' => 'HelloDiDiDistributorsBundle:Item',
                'property' => 'itemName',
            ));

  $roles = $User->getRoles() ;
  if($roles[0]=='ROLE_RETAILER_ADMIN')
  {
      $form=$form->add('Staff', 'entity',
                array(
                'class' => 'HelloDiDiDistributorsBundle:User',
                'property' => 'username',
                 'empty_data'=>$User->getUsername(),
                'query_builder' => function(EntityRepository $er) use ($User) {
                    return $er->createQueryBuilder('u')
                           ->where('u.Account = :ua')
                           ->orderBy('u.username', 'ASC')
                           ->setParameter('ua',$User->getAccount());

                }
                ));

  }


  $form=$form->add('DateStart','date',array())
             ->add('DateEnd','date',array())->getForm();

        if($req->isMethod('POST'))
        {
            $form->bind($req);
            $data=$form->getData();
            $qb=$em->createQueryBuilder();
            $qb->select(array('Tr'))
                ->from('HelloDiDiDistributorsBundle:Transaction','Tr')
                /*for groupBy*/
                ->innerJoin('Tr.Code','TrCo')->innerJoin('TrCo.Item','TrCoIt')->innerJoin('Tr.Account','TrAc')->innerJoin('Tr.User','TrUs')
                /**/
                ->Where($qb->expr()->like('Tr.tranAction',$qb->expr()->literal('sale')))
                ->andwhere('Tr.tranDate >= :DateStart')->setParameter('DateStart',$data['DateStart'])
                ->andwhere('Tr.tranDate <= :DateEnd')->setParameter('DateEnd',$data['DateEnd'])
                ->andWhere($qb->expr()->like('TrUs.username',$qb->expr()->literal($data['Staff'].'%')));

            if($data['ItemType']!=3)
                $qb=$qb->andwhere('TrCoIt.itemType =:ItemType')->setParameter('ItemType',$data['ItemType']);


            if($data['ItemName']!='All')
                 $qb=$qb->andWhere($qb->expr()->like('TrCoIt.itemName',$qb->expr()->literal($data['ItemName'])));


            $query=$qb->getQuery();

        }

        $count = count($query->getResult());
        $query = $query->setHint('knp_paginator.count', $count);
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1) /*page number*/,
            5/*limit per page*/
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

        $tickets=$em->getRepository('HelloDiDiDistributorsBundle:Ticket')->findBy(array('Accountretailer'=>$User->getAccount()));


        if($req->isMethod('POST'))
        {
            $form->submit($req);
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
            $tickets = $tickets->setHint('knp_paginator.count', $count);

        }


        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $tickets,
            $this->get('request')->query->get('page', 1) /*page number*/,
            5/*limit per page*/
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
            ->add('Description','textarea')
            ->getForm();

        if($req->isMethod('POST'))
        {
            $form->submit($req);
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
            ->add('Description','textarea',array('required'=>false,
                'label'=>'New note'
            ))->getForm();

        if($req->isMethod('POST'))
        {
            $form->submit($req);
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

        $em = $this->getDoctrine()->getManager();
        $Account = $this->container->get('security.context')->getToken()->getUser()->getAccount();
        $check = $Account->getId();
        $qb = $em->createQueryBuilder()
            ->select('item.itemName','item.id','operator.name','item.itemFaceValue','item.itemCurrency','price.id as pid')
            ->from('HelloDiDiDistributorsBundle:Account','acc')
            ->innerJoin('acc.Prices','price')
            ->innerJoin('price.Item','item')
            ->innerJoin('item.operator','operator')
            ->where('acc.id =:check')
            ->setParameter('check',$check)
            ->andwhere('item.itemType =:check2')
            ->setParameter('check2',0)
            ->OrderBy('item.itemName')


            ->getQuery();

        $item = $qb->getResult();

        $qb = $em->createQueryBuilder()
            ->select('DISTINCT operator.id','operator.name','operator.Logo')
            ->from('HelloDiDiDistributorsBundle:Account','acc')
            ->innerJoin('acc.Prices','price')
            ->innerJoin('price.Item','item')
            ->innerJoin('item.operator','operator')
            ->where('acc.id =:check')
            ->setParameter('check',$check)
            ->andwhere('item.itemType =:check2')
            ->setParameter('check2',0)


            ->getQuery();
        $operator = $qb->getResult();

       return $this->render('HelloDiDiDistributorsBundle:Retailers:ShopDmtu.html.twig',array
       (
           'itemlist' => $item ,
           'operator'=>$operator ,
           'account'=>$Account
              ));

        }

    public function PrintCodeAction(Request $request){

//        $codeselector = $this->get('hello_di_di_distributors.codeselector');
//        $code = $codeselector->lookForAvailableCode($account, $price, $price->getItem());

        if($request->isMethod('POST')){

                try{
                $em = $this->getDoctrine()->getManager();

                $user = $this->get('security.context')->getToken()->getUser();
                $account = $this->get('security.context')->getToken()->getUser()->getAccount();

                $accountParent = $this->get('security.context')->getToken()->getUser()->getAccount()->getParent();

                $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->find($request->get('price_id'));

                $itemlist = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($request->get('item_id'));

                $codeselector = $this->get('hello_di_di_distributors.codeselector');

                $code = $codeselector->lookForAvailableCode($account,$price,$itemlist,$request->get('numberOfsale'));

                $priceParent = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Account' => $accountParent));

                $tranProfit = $price->getprice() - $priceParent->getprice();

                foreach($code as $value)
                {

                    $transaction = new Transaction();
                    $transaction->setAccount($account);
                    $transaction->setTranAmount(-($price->getPrice()));
                    $transaction->setTranFees(0);
                    $transaction->setTranCurrency($price->getPriceCurrency());
                    $transaction->setTranDate(new \DateTime('now'));
                    $transaction->setCode($value);
                    $transaction->setTranAction('sale');
                    $transaction->setUser($user);
                    $em->persist($transaction);
                    $em->flush();
                    // For Parent
                    $transaction = new Transaction();
                    $transaction->setAccount($accountParent);
                    $transaction->setTranAmount($tranProfit);
                    $transaction->setTranFees(0);
                    $transaction->setTranCurrency($price->getPriceCurrency());
                    $transaction->setTranDate(new \DateTime('now'));
                    $transaction->setCode($value);
                    $transaction->setTranAction('Profit');
                    $transaction->setUser($user);
                    $em->persist($transaction);
                    $em->flush();

                }
                return $this->render('HelloDiDiDistributorsBundle:Retailers:CodePrint.html.twig',array('code'=>$code));

        }

            catch(\Exception $e){
                print  $e->getMessage();
                return $this->render('HelloDiDiDistributorsBundle:Retailers:CodePrint.html.twig',array('code'=>null));
            }
        }

    }

    public function CallingCardAction() {

        $em = $this->getDoctrine()->getManager();

        $Account = $this->container->get('security.context')->getToken()->getUser()->getAccount();
        $check = $Account->getId();
        $qb = $em->createQueryBuilder()
            ->select('item.itemName','item.id','operator.name','item.itemFaceValue','item.itemCurrency','price.id as pid')
            ->from('HelloDiDiDistributorsBundle:Account','acc')
            ->innerJoin('acc.Prices','price')
            ->innerJoin('price.Item','item')
            ->innerJoin('item.operator','operator')
            ->where('acc.id =:check')
            ->setParameter('check',$check)
            ->andwhere('item.itemType =:check2')
            ->setParameter('check2',2)
            ->OrderBy('item.itemName')
            ->getQuery();
            $item = $qb->getResult();

        $qb = $em->createQueryBuilder()
            ->select('DISTINCT operator.id','operator.name')
            ->from('HelloDiDiDistributorsBundle:Account','acc')
            ->innerJoin('acc.Prices','price')
            ->innerJoin('price.Item','item')
            ->innerJoin('item.operator','operator')
            ->where('acc.id =:check')
            ->andwhere('item.itemType =:check2')
            ->setParameter('check2',2)
            ->setParameter('check',$check)
            ->getQuery();

            $operator = $qb->getResult();
        return $this->render('HelloDiDiDistributorsBundle:Retailers:CallingCard.html.twig',array('itemlist' => $item , 'operator'=>$operator,'account'=>$Account));

    }

    public function FavouritesAction(){
        $em = $this->getDoctrine()->getManager();

        $Account = $this->container->get('security.context')->getToken()->getUser()->getAccount();
        $check = $Account->getId();
        $qb = $em->createQueryBuilder()
            ->select('item.itemName','item.id')
            ->from('HelloDiDiDistributorsBundle:Account','acc')
            ->innerJoin('acc.Prices','price')
            ->innerJoin('price.Item','item')
            ->innerJoin('item.operator','operator')
            ->where('acc.id =:check')
            ->setParameter('check',$check)
            ->andwhere('price.isFavourite =:check2')
            ->setParameter('check2',1)
            ->getQuery();

        $itemFavourite = $qb->getResult();
        return $this->render('HelloDiDiDistributorsBundle:Retailers:favourite.html.twig',array('listFavourite'=>$itemFavourite));

    }

    public  function FavouritesCodeAction(Request $request ){

        return $this->render('HelloDiDiDistributorsBundle:Retailers:favouriteCode.html.twig',array('test'=>$request->get('favourite_id')));
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
// end checks
}

