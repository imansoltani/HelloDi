<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Entity\DetailHistory;
use HelloDi\DiDistributorsBundle\Entity\Entiti;
use HelloDi\DiDistributorsBundle\Entity\Price;
use HelloDi\DiDistributorsBundle\Entity\PriceHistory;
use HelloDi\DiDistributorsBundle\Entity\Ticket;
use HelloDi\DiDistributorsBundle\Entity\TicketNote;
use HelloDi\DiDistributorsBundle\Entity\Transaction;
use HelloDi\DiDistributorsBundle\Form\Account\AccountType;
use HelloDi\DiDistributorsBundle\Form\Account\EditRetailerType;
use HelloDi\DiDistributorsBundle\Form\Distributors\NewRetailersType;
use HelloDi\DiDistributorsBundle\Entity\User;
use HelloDi\DiDistributorsBundle\Form\Entiti\EditEntitiRetailerType;
use HelloDi\DiDistributorsBundle\Form\Entiti\EntitiType;
use HelloDi\DiDistributorsBundle\Form\PriceEditType;
use HelloDi\DiDistributorsBundle\Form\Retailers\AccountRetailerSettingType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HelloDi\DiDistributorsBundle\Entity\Account;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

class DistributorsController extends Controller
{

    public function dashboardAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $Account = $user->getAccount();

        return $this->render('HelloDiDiDistributorsBundle:Distributors:dashboard.html.twig', array('Account' => $Account, 'Entiti' => $Account->getEntiti(), 'User' => $user));

    }

    //Retailers

    public function saleAction(Request $req)
    {

        $paginator = $this->get('knp_paginator');
        $User= $this->get('security.context')->getToken()->getUser();
        $Account=$User->getAccount();
        $em=$this->getDoctrine()->getEntityManager();



        $qb=array();


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
                    'label'=>'Item:',
                    'empty_data' => '',
                    'empty_value'=>'All',
                    'required'=>false,
                    'class' => 'HelloDiDiDistributorsBundle:Item',
                    'property' => 'itemName',
                    'query_builder' => function(EntityRepository $er) use ($Account) {
                        return $er->createQueryBuilder('u')
                            ->innerJoin('u.Prices','up');
                        foreach($Account->getChildrens() as $acc )
                        {
                            $er->orwhere('up.Account = :Acc')->setParameter('Acc',$acc);

                        }
                        $er->andWhere('up.priceStatus = 1');
                    }


                ))

            ->add('Account', 'entity',
                array(
                    'class' => 'HelloDiDiDistributorsBundle:Account',
                    'property' => 'accName',
                     'empty_data'=>'',
                    'empty_value'=>'All',
                    'required'=>false,
                    'query_builder' => function(EntityRepository $er) use ($Account) {
                        return $er->createQueryBuilder('a')
                            ->where('a.Parent = :ap')
                            ->orderBy('a.accName', 'ASC')
                            ->setParameter('ap',$Account);
                    }
                          ))
            ->add('DateStart','text',array('required'=>false))
            ->add('DateEnd','text',array('required'=>false))

            ->getForm();

        if($req->isMethod('POST'))
        {
            $form->handleRequest($req);
            $data=$form->getData();

            $qb=$em->createQueryBuilder();

            $qb->select('Tr')
                ->from('HelloDiDiDistributorsBundle:Transaction','Tr')
                /*for groupBy*/
                ->innerJoin('Tr.Code','TrCo')->innerJoin('TrCo.Item','TrCoIt');
                /**/

                $qb->Where($qb->expr()->like('Tr.tranAction',$qb->expr()->literal('sale')));
                if($data['DateStart']!='')
                $qb->andwhere('Tr.tranDate >= :DateStart')->setParameter('DateStart',$data['DateStart']);
                elseif($data['DateEnd']!='')
                 $qb->andwhere('Tr.tranDate <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);

             if($data['Account'])
             {
                 $qb->andwhere('Tr.Account=:ac')->setParameter('ac',$data['Account']);

             }

            else
            {
                foreach($Account->getChildrens() as $acc )
                {
                    $qb->orwhere('Tr.Account = :Acc')->setParameter('Acc',$acc);

                }

            }


            if($data['ItemType']!='All')
                $qb=$qb->andwhere('TrCoIt.itemType =:ItemType')->setParameter('ItemType',$data['ItemType']);

            if($data['ItemName'])
                $qb=$qb->andWhere($qb->expr()->like('TrCoIt.itemName',$qb->expr()->literal($data['ItemName']->getItemName())));


       $qb->orderBy('Tr.tranInsert','desc');

            $qb=$qb->getQuery();
            $count = count($qb->getResult());
           $qb->setHint('knp_paginator.count', $count);

        }


        $pagination = $paginator->paginate(
            $qb,
            $req->get('page',1) /*page number*/,
           10/*limit per page*/
        );

        return $this->render('HelloDiDiDistributorsBundle:Distributors:ReportSale.html.twig',

            array(
                'pagination'=>$pagination,
                'form'=>$form->createView(),
                'User'=>$User,
                'Account' =>$User->getAccount(),
                'Entiti' =>$User->getEntiti()));

    }


    public function  DetailsSaleAction($id)
    {
        $this->check_ChildTransaction($id);

        $em=$this->getDoctrine()->getManager();

        $tran=$em->getRepository('HelloDiDiDistributorsBundle:Transaction')->find($id);

        $BuPrice=$em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array(
         'Account'=>$tran->getAccount()->getParent()
        ,'Item'=>$tran->getCode()->getItem()));

        $SePrice=$em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array(
            'Account'=>$tran->getAccount()
           ,'Item'=>$tran->getCode()->getItem()));

        return $this->render('HelloDiDiDistributorsBundle:Distributors:DetailsReportSale.html.twig',
            array(
                'tran'=>$tran,
                'BuPrice'=>$BuPrice->getPrice(),
                'SePrice'=>$SePrice->getPrice()
            ));

    }

    public function  FundingAction($id)
    {
        $this->check_ChildAccount($id);

        $em=$this->getDoctrine()->getManager();

        $Account=$em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $formapplay=$this->createFormBuilder()
            ->add('Amount',null,array())
            ->add('Communications','textarea',array('required'=>true))
            ->add('Description','textarea',array('required'=>true))
            ->getForm();

        $formupdate=$this->createFormBuilder()
            ->add('Amount','text',array())
            ->add('As','choice',array(
                'preferred_choices'=>array('Credit'),
                'choices'=>
                array(
                    1=>'Increase',
                    0=>'Decrease')
            ))->getForm();

        return $this->render('HelloDiDiDistributorsBundle:Distributors:Funding.html.twig',
            array(
                'Entiti'=>$Account->getEntiti(),
                'Account'=>$Account->getParent(),
                'retailerAccount'=>$Account,
                'formapplay'=>$formapplay->createView(),
                'formupdate'=>$formupdate->createView(),

            ));
    }

    public function  FundingTransferAction(Request $req,$id)
    {

        $this->check_ChildAccount($id);
        $balancechecker=$this->get('hello_di_di_distributors.balancechecker');
        $User= $this->get('security.context')->getToken()->getUser();
        $em=$this->getDoctrine()->getManager();
        $Account=$em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $formtransfer=$this->createFormBuilder()
            ->add('Amount')
            ->add('Communications','textarea',array('required'=>false))
            ->add('Description','textarea',array('required'=>false))
            ->getForm();

        if($req->isMethod('post'))
        {

            $trandist=new Transaction();
            $tranretailer=new Transaction();

            $formtransfer->handleRequest($req);

            $data=$formtransfer->getData();

           #transaction for dist#

            $trandist->setTranDate(new \DateTime('now'));
            $trandist->setTranCurrency($Account->getParent()->getAccCurrency());
            $trandist->setTranInsert(new \DateTime('now'));
            $trandist->setAccount($Account->getParent());
            $trandist->setUser($User);
            $trandist->setTranFees(0);
            $trandist->setTranAction('tran');
            $trandist->setTranType(0);
            $trandist->setTranDescription($data['Description']);




            #transaction for retailer#

            $tranretailer->setTranDate(new \DateTime('now'));
            $tranretailer->setTranCurrency($Account->getAccCurrency());
            $tranretailer->setTranInsert(new \DateTime('now'));
            $tranretailer->setAccount($Account);
            $tranretailer->setUser($User);
            $tranretailer->setTranFees(0);
            $tranretailer->setTranAction('tran');
            $trandist->setTranType(1);
            $tranretailer->setTranDescription($data['Communications']);

            if($data['Amount']>0)
            {
                if($balancechecker->isBalanceEnoughForMoney($Account->getParent(),$data['Amount']))
                {
                    $tranretailer->setTranAmount(+$data['Amount']);
                    $trandist->setTranAmount(-$data['Amount']);
                    $em->persist($trandist);
                    $em->persist($tranretailer);
                    $em->flush();
                    $this->get('session')->getFlashBag()->add('success','this operation done success !');
                }

            }
else
    $this->get('session')->getFlashBag()->add('error','zero isn,t accept!');

        }

        return $this->redirect($this->generateUrl('DistRetailerFunding',array('id'=>$id)));

    }

    public function  FundingUpdateAction(Request $req,$id)
    {
        $this->check_ChildAccount($id);
        $balancechecker=$this->get('hello_di_di_distributors.balancechecker');
        $User= $this->get('security.context')->getToken()->getUser();

        $em=$this->getDoctrine()->getManager();

        $Account=$em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $formupdate=$this->createFormBuilder()
            ->add('Amount','text')
            ->add('As','choice',array('preferred_choices'=>array('Credit'),
                'choices'=>array(
                    1=>'Increase',
                    0=>'Decrease')
            ))->getForm();

        if($req->isMethod('POST'))
        {
            $formupdate->handleRequest($req);
            $data=$formupdate->getData();

            $trandist=new Transaction();

            $trandist->setTranDate(new \DateTime('now'));
            $trandist->setTranCurrency($Account->getParent()->getAccCurrency());

            $trandist->setTranInsert(new \DateTime('now'));

            $trandist->setUser($User);
            $trandist->setTranFees(0);
            $trandist->setTranAction('crtl');
            $trandist->setTranType(0);
            $trandist->setAccount($Account->getParent());
if($data['Amount']>0)
{

    if($data['As']==1)
    {
        if($balancechecker->isBalanceEnoughForMoney($Account->getParent(),$data['Amount']))
        {
            $trandist->setTranAmount(-$data['Amount']);
            $Account->setAccCreditLimit($Account->getAccCreditLimit()+$data['Amount']);
            $em->persist($trandist);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success','this operation done success !');
        }
    }

    elseif($data['As']==0)
    {

        if($balancechecker->isAccCreditLimitPlus($Account,$data['Amount']))
        {
            $Account->setAccCreditLimit($Account->getAccCreditLimit()- $data['Amount']);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success','this operation done success !');
        }
    }

}
else
    $this->get('session')->getFlashBag()->add('error','zero isn,t accept!');

}
        return $this->redirect($this->generateUrl('DistRetailerFunding',array('id'=>$id)));
    }


    public function DistProfileAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $Account = $user->getAccount();
        return $this->render('HelloDiDiDistributorsBundle:Distributors:Profile.html.twig', array('Account' => $Account, 'Entiti' => $Account->getEntiti(), 'User' => $user));
    }

    public function DistStaffAction()
    {
        $em=$this->getDoctrine()->getEntityManager();
        $user= $this->get('security.context')->getToken()->getUser();

         $Account=$user->getAccount();

        $users =$em->createQueryBuilder()
            ->select('USR')
            ->from('HelloDiDiDistributorsBundle:User','USR')
            ->where('USR.Account = :Acc')->setParameter('Acc',$Account)
            ->andWhere('USR != :US ')->setParameter('US',$user);

        $users=$users->getQuery()->getResult();


        return $this->render('HelloDiDiDistributorsBundle:Distributors:Staff.html.twig',
            array(
                'Entiti' => $Account->getEntiti(),
                'Users' => $users));
    }

    public function DistStaffAddAction(Request $request)
    {
        $user = new User();
        $em = $this->getDoctrine()->getManager();
        $Account = $this->get('security.context')->getToken()->getUser()->getAccount();
        $Entiti = $Account->getEntiti();

        $form = $this->createForm(new \HelloDi\DiDistributorsBundle\Form\User\NewUserType('HelloDiDiDistributorsBundle\Entity\User'), $user);
        $formrole = $this->createFormBuilder()
            ->add('roles', 'choice', array('choices' => array('ROLE_DISTRIBUTOR' => 'ROLE_DISTRIBUTOR', 'ROLE_DISTRIBUTOR_ADMIN' => 'ROLE_DISTRIBUTOR_ADMIN')))->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $formrole->handleRequest($request);
            $data = $formrole->getData();
            $user->addRole(($data['roles']));
            $user->setAccount($Account);
            $user->setEntiti($Entiti);
            $user->setStatus(1);
            if ($form->isValid()) {
                $em->persist($user);
                $em->flush();

                return $this->redirect($this->generateUrl('DistStaff', array('id' => $Account->getId())));

            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Distributors:StaffAdd.html.twig',
            array(
                'Entiti' => $Account->getEntiti(),
                'Account' => $Account,
                'form' => $form->createView(),
                'formrole' => $formrole->createView()));

    }

    public function DistStaffEditAction(Request $request, $id)
    {
        $this->check_User($id);


        $user = new User();
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('HelloDiDiDistributorsBundle:User')->find($id);
        $form = $this->createForm(new \HelloDi\DiDistributorsBundle\Form\User\NewUserType('HelloDiDiDistributorsBundle\Entity\User',0), $user, array('cascade_validation' => true));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->flush();
                return $this->redirect($this->generateUrl('DistStaff', array('id' => $user->getAccount()->getId())));
            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Distributors:StaffEdit.html.twig', array('Account' => $user->getAccount(), 'Entiti' => $user->getEntiti(), 'userid' => $id, 'form' => $form->createView()));

    }



    public function DistRetailerUserAction(Request $req,$id)
    {
        $this->check_ChildAccount($id);

        $em = $this->getDoctrine()->getManager();
        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $users = $Account->getUsers();



        return $this->render('HelloDiDiDistributorsBundle:Distributors:RetailerUser.html.twig', array(
                'Entiti' => $Account->getEntiti(),
            'Account' => $Account->getParent(),
            'retailerAccount' => $Account,
                'Users' => $users
            ));

    }

    public function DistRetailerUserEditAction(Request $request, $userid)
    {
        $this->check_ChildUser($userid);
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('HelloDiDiDistributorsBundle:User')->find($userid);
        $form = $this->createForm(new \HelloDi\DiDistributorsBundle\Form\User\NewUserType('HelloDiDiDistributorsBundle\Entity\User',2), $user, array('cascade_validation' => true));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->flush();
                $this->get('session')->getFlashBag()->add('success','this operation done success !');
            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Distributors:RetailerUserEdit.html.twig', array(
                'retailerAccount' => $user->getAccount(),
                'Account' => $myaccount,
                'Entiti' => $user->getEntiti(),
                'userid' => $userid,
                'form' => $form->createView()
            ));

    }

    public function DistRetailerUserAddAction(Request $request, $id)
    {
        $this->check_ChildAccount($id);
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
        $user = new User();
        $em = $this->getDoctrine()->getManager();
        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $Entiti = $Account->getEntiti();

        $form = $this->createForm(new \HelloDi\DiDistributorsBundle\Form\User\NewUserType('HelloDiDiDistributorsBundle\Entity\User',2),$user);


        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $user->setAccount($Account);
            $user->setEntiti($Entiti);
            if ($form->isValid())
            {
                $em->persist($user);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success','this operation done success !');
                return $this->redirect($this->generateUrl('DistRetailerUser', array('id' => $Account->getId())));
            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Distributors:RetailerUserAdd.html.twig', array(
                'Entiti' => $Account->getEntiti(),
                'retailerAccount' =>$Account,
                'Account' => $myaccount,
                'form' => $form->createView(),
            ));

    }


    public function DistNewRetailerAction(Request $request)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $Account = $user->getAccount();

        $em = $this->getDoctrine()->getManager();
        $user= $this->get('security.context')->getToken()->getUser();
        $currency=$user->getAccount()->getAccCurrency();
        $user = new User();
        $AdrsDetai = new DetailHistory();
        $Entiti = new Entiti();
        $Account = new Account();

        $Account->setAccCreditLimit(0);
        $Account->setAccCreationDate(new \DateTime('now'));
        $Account->setAccTimeZone(null);
        $Account->setAccType(2);
        $Account->setAccBalance(0);


        $Account->setAccCurrency($currency);
        $Account->setParent($user->getAccount());


        $Account->setEntiti($Entiti);
        $Entiti->addAccount($Account);

        $user->setEntiti($Entiti);
        $Entiti->addUser($user);



        $user->setAccount($Account);
        $user->setStatus(1);


        $form = $this->createForm(new NewRetailersType(), $Entiti, array('cascade_validation' => true));

        if ($request->isMethod('POST')) {

            $form->handleRequest($request);

            //if ($form->isValid()) {

            $em->persist($Entiti);
            $AdrsDetai->setCountry($Entiti->getCountry());
            $em->persist($Account);
            $em->persist($user);
            $AdrsDetai->setAdrsDate(new \DateTime('now'));
            $AdrsDetai->setEntiti($Entiti);
            $AdrsDetai->setAdrs1($Entiti->getEntAdrs1());
            $AdrsDetai->setAdrs2($Entiti->getEntAdrs2());
            $AdrsDetai->setAdrs3($Entiti->getEntAdrs3());
            $AdrsDetai->setAdrsCity($Entiti->getEntCity());
            $AdrsDetai->setAdrsNp($Entiti->getEntNp());
            $AdrsDetai->setEntiti($Entiti);
            $em->persist($AdrsDetai);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success','this operation done success !');

            return $this->redirect($this->generateUrl('retailer_show',array('id',$user->getAccount()->getId())));



        }

        return $this->render('HelloDiDiDistributorsBundle:Distributors:NewRetailer.html.twig', array(
            'form_Relaited_New' => $form->createView(),
            'Account' => $Account
        ));

    }

    public function ShowRetaierAccountAction(Request $request)
    {
        $paginator = $this->get('knp_paginator');
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $Account = $user->getAccount();
        $qb = $em->createQueryBuilder()
            ->select('retailer')
            ->from('HelloDiDiDistributorsBundle:Account', 'retailer')
            ->innerJoin('retailer.Entiti', 'Ent')
            ->where('retailer.Parent = :prn')
            ->setParameter('prn',$Account);

        $form_search= $this->createFormBuilder()
                       ->add('CityName','entity',
                              array(
                                  'required'=>false,
                                  'class' => 'HelloDiDiDistributorsBundle:Entiti',
                                  'property'=>'entCity',
                                  'empty_value'=>'All',
                                  'query_builder' => function(EntityRepository $er) use ($Account) {
                                      return $er->createQueryBuilder('E')
                                          ->innerJoin('E.Accounts','EA')
                                          ->andWhere('EA.Parent = :parent')->setParameter('parent',$Account);
                                  })

                                                    )
                       ->add('Balance','choice',
                               array(
                                   'choices'=>(array(
                                       0=>'<',
                                       1=>'>',
                                       2=>'=' )
                                                     )))
                       ->add('BalanceValue','text',
                                array(
                                    'required'=>false)
                                                      )->getForm();


        if ($request->isMethod('POST')) {

            $form_search->handleRequest($request);
            $data= $form_search->getData();
            if($data['CityName'])
                $qb->andwhere($qb->expr()->like('Ent.entCity', $qb->expr()->literal($data['CityName']->getEntCity())));
            switch($data['Balance'])
            {
                case 1:
                if ($data['BalanceValue'] != '')
                    $qb->andwhere($qb->expr()->gt('retailer.accBalance', $data['BalanceValue']));
                break;
                case 0:
                if ($data['BalanceValue']!= '')
                    $qb->andwhere($qb->expr()->lt('retailer.accBalance', $data['BalanceValue']));
                 break;
           case 2:
                if ($data['BalanceValue']!= '')
                    $qb->andwhere($qb->expr()->eq('retailer.accBalance', $data['BalanceValue']));
                 break;
            }


        }


        $qb=$qb->getQuery();
        $count = count($qb->getResult());
        $qb->setHint('knp_paginator.count', $count);
        $pagination = $paginator->paginate(
            $qb,
            $request->get('page',1)/*page number*/,
            10/*limit per page*/
        );
        return $this->render('HelloDiDiDistributorsBundle:Distributors:ShowRetailers.html.twig', array (
            'Retailers' => $pagination,
            'form_search' => $form_search->createView(),
            'Account' => $Account
        ));

    }

/////---jaadidkazem--

    public function RetailersTransactionAction(Request $req,$id)
    {
        $em=$this->getDoctrine()->getManager();

        $paginator = $this->get('knp_paginator');

        $this->check_ChildAccount($id);

        $Account=$em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $typedate=0;
       $qb=array();

        $form=$this->createFormBuilder()
            ->add('TypeDate','choice', array(
                'expanded'   => true,
                'choices'    => array(
                    0 => 'Trade Date',
                    1 => 'Looking Date',
                )

            ))
            ->add('DateStart','text',array('required'=>false,'label'=>'From:'))
            ->add('DateEnd','text',array('required'=>false,'label'=>'To:'))

            ->add('Type','choice',
                array('choices'=>
                array(
                    2=>'All',
                    0=>'Debit',
                    1=>'Credit',
                )))

            ->add('Action', 'choice', array('label'=>'Action:',
                'choices' =>
                array(
                    'All'=>'All',
                    'sale'=>'debit balance when the retailer sell a code',
                    'crnt'=>'issue a credit note for a sold code',
                    'tran'=>'transfer credit from distributor,s account to a retailer,s account',
                    'pmt'=>'ogone payment on its own account'
                )))

            ->getForm();


        if($req->isMethod('POST'))
        {
            $form->handleRequest($req);
            $data=$form->getData();
            $qb=$em->createQueryBuilder();
            $qb->select('Tran')
                ->from('HelloDiDiDistributorsBundle:Transaction','Tran')
                ->where('Tran.Account = :Acc')->setParameter('Acc',$Account);
            if($data['TypeDate']==0)
            {
                if($data['DateStart']!='')
                $qb->andwhere('Tran.tranDate >= :DateStart')->setParameter('DateStart',$data['DateStart']);
                if($data['DateEnd']!='')
                $qb->andwhere('Tran.tranDate <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);

            }

            if($data['TypeDate']==1)
            {$typedate=1;
                if($data['DateStart']!='')
               $qb->where('Tran.tranInsert >= :DateStart')->setParameter('DateStart',$data['DateStart']);
                if($data['DateEnd']!='')
               $qb->andwhere('Tran.tranInsert <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);

            }

            if ($data['Type'] != 2)
                $qb->andWhere($qb->expr()->eq('Tran.tranType',$data['Type']));

            if($data['Action']!='All')
                $qb->andWhere($qb->expr()->like('Tran.tranAction',$qb->expr()->literal($data['Action'])));

            $qb->addOrderBy('Tran.tranInsert','desc')->addOrderBy('Tran.id','desc');;

            $qb=$qb->getQuery();
            $count = count($qb->getResult());
            $qb->setHint('knp_paginator.count', $count);
        }

        $pagination = $paginator->paginate(
            $qb,
            $req->get('page',1) /*page number*/,
            10/*limit per page*/
        );

        return $this->render('HelloDiDiDistributorsBundle:Distributors:RetailersTransaction.html.twig',
        array(
            'pagination'=>$pagination,
            'form'=>$form->createView(),
            'Account' => $Account->getParent(),
            'retailerAccount' => $Account,
            'Entiti' =>$Account->getEntiti(),
            'typedate'=>$typedate
        ));


    }

    public function DetailsRetailerTransactionAction($tranid)
    {
        $this->check_ChildTransaction($tranid);

        $em=$this->getDoctrine()->getManager();


        $Tran=$em->getRepository('HelloDiDiDistributorsBundle:Transaction')->find($tranid);
        $Account = $Tran->getAccount();
        return $this->render('HelloDiDiDistributorsBundle:Distributors:RetailerDetailsTransaction.html.twig',
            array(
                'tran'=>$Tran,
                'retailerAccount'=>$Account,
            ));

    }


    public function DistTransactionAction(Request $req)
{
    $paginator = $this->get('knp_paginator');
    $em=$this->getDoctrine()->getManager();

    $Account=$this->get('security.context')->getToken()->getUser()->getAccount();

    $qb=array();

    $form=$this->createFormBuilder()

        ->add('Type','choice',
            array('choices'=>
            array(
                  2=>'All',
                  0=>'Debit',
                  1=>'Credit',
            )))

        ->add('Action', 'choice', array('label'=>'Action:',
            'choices' =>
              array(

                  'All' => 'All',
                  'pmt' => 'credit distributor,s account',
                  'amdt' => 'debit distributor,s account',
                  'crnt'=>'issue a credit note for a sold code',
                  'com_pmt' => 'debit distributor,s account for the commisson payments',
                  'pmt' => 'ogone payment on its own account',
                  'tran'=>'transfer credit from provider,s account to a distributor,s account',
                  'tran'=>'transfer credit from distributors account to a retailer,s account',
                  'crtl'=>'increase retailer,s credit limit',
                  'com'=>'credit commissons when a retailer sells a code'

              )))

        ->add('DateStart','text',array('required'=>false))
        ->add('DateEnd','text',array('required'=>false))
        ->add('TypeDate','choice', array(
            'empty_value'=>'Trade Date',
            'expanded'   => true,
            'choices'    => array(
                0 => 'Trade Date',
                1 => 'Looking Date',
            )
        ))->getForm();

    $typedate=0;
    if($req->isMethod('POST'))
    {
        $form->handleRequest($req);
        $data=$form->getData();

        $qb=$em->createQueryBuilder();
        $qb->select('Tran')
            ->from('HelloDiDiDistributorsBundle:Transaction','Tran')
            ->where('Tran.Account = :Acc')->setParameter('Acc',$Account);

        if($data['TypeDate']==0)
        {
            $typedate=0;
          if($data['DateStart']!='')
            $qb->andwhere('Tran.tranDate >= :DateStart')->setParameter('DateStart',$data['DateStart']);
            if($data['DateEnd']!='')
            $qb->andwhere('Tran.tranDate <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);

        }

        if($data['TypeDate']==1)
        {$typedate=1;
            if($data['DateStart']!='')
           $qb->andwhere('Tran.tranInsert >= :DateStart')->setParameter('DateStart',$data['DateStart']);
            if($data['DateEnd']!='')
            $qb->andwhere('Tran.tranInsert <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);

        }
        if ($data['Type'] != 2)
            $qb->andWhere($qb->expr()->eq('Tran.tranType',$data['Type']));

        if($data['Action']!='All')
            $qb->andWhere($qb->expr()->like('Tran.tranAction',$qb->expr()->literal($data['Action'])));

        $qb->addOrderBy('Tran.tranInsert','desc')->addOrderBy('Tran.id','desc');;

        $qb=$qb->getQuery();
        $count = count($qb->getResult());
        $qb->setHint('knp_paginator.count', $count);

    }

    $pagination = $paginator->paginate(
        $qb,
        $req->get('page',1) /*page number*/,
        10/*limit per page*/
    );

    return $this->render('HelloDiDiDistributorsBundle:Distributors:Transaction.html.twig',
        array(
            'pagination'=>$pagination,
            'form'=>$form->createView(),
            'Account' =>$Account,
            'Entiti' =>$Account->getEntiti(),
            'typedate'=>$typedate
        ));

}


    public function DetailsTransactionAction($id)
    {
        $this->check_Transaction($id);

        $em=$this->getDoctrine()->getManager();
        $Tran=$em->getRepository('HelloDiDiDistributorsBundle:Transaction')->find($id);


        return $this->render('HelloDiDiDistributorsBundle:Distributors:DetailsTransaction.html.twig',
            array(
                'tran'=>$Tran,

            ));

    }




    public function DistRetailerSettingAction(Request $req, $id)
    {
        $this->check_ChildAccount($id);

        $em = $this->getDoctrine()->getManager();

        $Account= $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $form = $this->createForm(new EditRetailerType(),$Account);
        if ($req->isMethod('POST')) {
            $form->handleRequest($req);
            if ($form->isValid()) {
                $em->flush();
                $this->get('session')->getFlashBag()->add('success','this operation done success !');
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Distributors:RetailerSetting.html.twig', array(
            'Entiti' => $Account->getEntiti(),
            'Account' => $Account->getParent(),
            'retailerAccount' => $Account,
            'form' => $form->createView()
        ));
    }

    public function DetailsAction(Request $req,$id)
    {
        $this->check_ChildAccount($id);
        $em = $this->getDoctrine()->getManager();

        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $entity = $Account->getEntiti();

        $editForm = $this->createForm(new EditEntitiRetailerType(),$entity);

        if($req->isMethod('post'))
        {
         $editForm->handleRequest($req);
         if($editForm->isValid())
         {
             $em->flush();
             $this->get('session')->getFlashBag()->add('success','this operation done success !');
         }
        }

        return $this->render('HelloDiDiDistributorsBundle:Distributors:RetailersDetails.html.twig', array(

            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'Account' => $Account->getParent(),
            'retailerAccount' => $Account,
         ));
    }

    //items
    public function ShowItemsAction()
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();

        $prices = $myaccount->getPrices();

        return $this->render('HelloDiDiDistributorsBundle:Distributors:items.html.twig', array(
            'prices' => $prices,
            'Account' => $myaccount
        ));
    }

    public function ItemPerRetailerAction(Request $request, $itemid)
    {
        $this->check_Item($itemid);
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($itemid);

        $account = $this->get('security.context')->getToken()->getUser()->getAccount();

        $form = $this->createFormBuilder()
            ->add('checks', 'entity', array(
                    'class' => 'HelloDiDiDistributorsBundle:Account',
                    'expanded' => 'true',
                    'multiple' => 'true',
                    'query_builder' => function(EntityRepository $er) use ($account,$item) {
                        return $er->createQueryBuilder('u')
                            ->leftJoin('u.Prices','prices','WITH','prices.Item = :item')
                            ->andWhere('u.Parent = :parent')
                            ->setParameter('item',$item)
                            ->setParameter('parent',$account)
                            ;
                    }
            ))
            ->add('NewPrice','text')
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $newprice = $data['NewPrice'];
                foreach ($data['checks'] as $accountretailer)
                {
                    if(count($accountretailer->getPrices())!=0)
                    {
                        $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Item'=>$item,'Account'=>$accountretailer));
                        $price->setPrice($newprice);

                        $pricehistory = new PriceHistory();
                        $pricehistory->setPrice($newprice);
                        $pricehistory->setDate(new \DateTime('now'));
                        $pricehistory->setPrices($price);
                        $em->persist($pricehistory);
                    }
                    else
                    {
                        $price = new Price();
                        $price->setPrice($newprice);
                        $price->setPriceCurrency($accountretailer->getAccCurrency());
                        $price->setPriceStatus(true);
                        $price->setIsFavourite(true);
                        $price->setItem($item);
                        $price->setAccount($accountretailer);
                        $em->persist($price);

                        $pricehistory = new PriceHistory();
                        $pricehistory->setPrice($newprice);
                        $pricehistory->setDate(new \DateTime('now'));
                        $pricehistory->setPrices($price);
                        $em->persist($pricehistory);
                    }
                }
                $em->flush();
                return $this->forward('HelloDiDiDistributorsBundle:Distributors:ShowItems');
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Distributors:ItemsPerRetailer.html.twig', array(
            'form' => $form->createView(),
            'Account' => $account,
            'itemid' => $itemid
        ));
    }

    public function RetailerItemsAction($id)
    {
        $this->check_ChildAccount($id);
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();

        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $prices = $account->getPrices();

        return $this->render('HelloDiDiDistributorsBundle:Distributors:RetailerItems.html.twig', array(
            'Account' => $myaccount,
            'retailerAccount' => $account,
            'prices' => $prices
        ));
    }

    public function RetailerItemsAddAction($id, Request $request)
    {
        $this->check_ChildAccount($id);
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();

        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $price = new Price();
        $price->setPriceCurrency($account->getAccCurrency());
        $price->setPriceStatus(1);
        $price->setAccount($account);
        $price->setIsFavourite(false);

        $form = $this->createFormBuilder($price)
            ->add('Item', 'entity', array(
                    'class' => 'HelloDiDiDistributorsBundle:Item',
                    'property' => 'itemName',
                    'query_builder' => function(EntityRepository $er) use ($account,$myaccount) {
                        return $er->createQueryBuilder('u')
                            ->where ('u.id NOT IN (
                            SELECT ii.id
                            FROM HelloDiDiDistributorsBundle:Item ii
                            JOIN ii.Prices pp
                            JOIN pp.Account aa
                            WHERE aa = :aaid
                        )')
                            ->andWhere('u.id IN (
                            SELECT iii.id
                            FROM HelloDiDiDistributorsBundle:Item iii
                            JOIN iii.Prices ppp
                            JOIN ppp.Account aaa
                            WHERE aaa = :aamyid
                        )')
                            ->setParameter('aaid',$account)
                            ->setParameter('aamyid',$myaccount)
                            ;
                    }
                ))
            ->add('price')
            ->getForm();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($price);

                $pricehistory = new PriceHistory();
                $pricehistory->setDate(new \DateTime('now'));
                $pricehistory->setPrice($price->getPrice());
                $pricehistory->setPrices($price);
                $em->persist($pricehistory);

                $em->flush();
                return $this->forward('HelloDiDiDistributorsBundle:Distributors:RetailerItems', array(
                        'id' => $account->getId()
                    ));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Distributors:RetailerItemsAdd.html.twig', array(
                'Account' => $myaccount,
                'retailerAccount' => $account,
                'form' => $form->createView()
            ));
    }

    public function RetailerItemsEditAction($id,$priceid, Request $request)
    {
        $this->check_ChildAccount($id);
        $this->check_ChildPrice($priceid);
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();

        $em = $this->getDoctrine()->getManager();
        $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->find($priceid);
        $oldprice = $price->getPrice();

        $form = $this->createForm(new PriceEditType(), $price);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                if ($price->getPrice() != $oldprice) {
                    $pricehistory = new PriceHistory();
                    $pricehistory->setDate(new \DateTime('now'));
                    $pricehistory->setPrice($price->getPrice());
                    $pricehistory->setPrices($price);
                    $em->persist($pricehistory);
                }
                $em->flush();

                return $this->forward('HelloDiDiDistributorsBundle:Distributors:RetailerItems', array(
                        'id' => $price->getAccount()->getId()
                    ));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Distributors:RetailerItemsEdit.html.twig', array(
                'Account' => $myaccount,
                'retailerAccount' => $price->getAccount(),
                'price' => $price,
                'form' => $form->createView()
            ));
    }

    // check functions
    private function check_Item($itemid)
    {
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($itemid);
        $account = $this->getUser()->getAccount();
        $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Account'=>$account,'Item'=>$item));

        if($price == null || $price->getPrice()==0)
        {
            throw new \Exception("You haven't permission to access this item !");
        }
    }

    private function check_ChildAccount($accountid)
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($accountid);
        if($account == null || $account->getParent() == null || $account->getParent() != $myaccount)
        {
            throw new \Exception("You haven't permission to access this Account !");
        }
    }

    private function check_ChildPrice($priceid)
    {
        $em = $this->getDoctrine()->getManager();
        $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->find($priceid);
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
        if($price == null || $price->getAccount() == null || $price->getAccount()->getParent() == null || $price->getAccount()->getParent() != $myaccount)
        {
            throw new \Exception("You haven't permission to access this Price !");
        }
    }

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

    private function check_ChildUser($userid)
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('HelloDiDiDistributorsBundle:User')->find($userid);
        if($user == null || $user->getAccount() == null || $user->getAccount()->getParent() == null || $user->getAccount()->getParent()!= $myaccount)
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

    private function check_ChildTransaction($tranid)
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
        $em = $this->getDoctrine()->getManager();
        $tran = $em->getRepository('HelloDiDiDistributorsBundle:Transaction')->find($tranid);
        if($tran == null || $tran->getAccount() == null || $tran->getAccount()->getParent() == null || $tran->getAccount()->getParent() != $myaccount)
        {
            throw new \Exception("You haven't permission to access this Transaction !");
        }
    }

    private function check_ChildEntity($entityid) //---has problem----
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
        $em = $this->getDoctrine()->getManager();
        $entiti = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($entityid);

        $qb = $em->createQueryBuilder()
            ->select('count(ent.id)')
            ->from('HelloDiDiDistributorsBundle:Entiti','ent')
            ->innerJoin('ent.Accounts','accs')
            ->innerJoin('accs.Parent','p')
            ->where('ent = :enti')
            ->andWhere('p = :par')
            ->setParameter('enti',$entiti)
            ->setParameter('par',$myaccount)
            ->getQuery();
        $count = $qb->getSingleScalarResult();

//        if($entiti == null || $entiti->getAccount() == null || $tran->getAccount()->getParent() == null || $tran->getAccount()->getParent() != $myaccount)
//        {
//            throw new \Exception("You haven't permission to access this Transaction !");
//        }
    }

    private function check_Ticket($ticketid)
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
        $em = $this->getDoctrine()->getManager();
        $ticket = $em->getRepository('HelloDiDiDistributorsBundle:Ticket')->find($ticketid);
        if($ticket == null || $ticket->getAccountdist() == null || $ticket->getAccountdist() != $myaccount)
        {
            throw new \Exception("You haven't permission to access this Ticket !");
        }
    }
    /////tickets


    public  function  ticketsAction(Request $req)
    {
        $paginator = $this->get('knp_paginator');

        $em=$this->getDoctrine()->getEntityManager();

        $User=$this->get('security.context')->getToken()->getUser();

        $Account=$User->getAccount();

        $form=$this->createFormBuilder()
            ->add('Type','choice',array('label'=>'Type:',
                'choices'=>array(
                    5=>'All',
                    0=>'Payment issue',
                    1=>'new item request',
                    2=>'price change request')
            ))

            ->add('Status','choice',array('label'=>'Status:',
                    'expanded'=>true,
                    'choices'=>array(
                        0=>'Close',
                        1=>'Open'
                    ))
            )->getForm();

        $tickets=$em->getRepository('HelloDiDiDistributorsBundle:Ticket')->findBy(array('Accountdist'=>$Account));

        if($req->isMethod('POST'))
        {
            $form->handleRequest($req);
            $data=$form->getData();

            $tickets=$em->createQueryBuilder();
            $tickets->select('Tic')
                ->from('HelloDiDiDistributorsBundle:Ticket','Tic')
                ->Where('Tic.Status = :status')->setParameter('status',$data['Status'])
                ->andWhere('Tic.Accountdist = :Acc')->setParameter('Acc',$Account);
            if($data['Type']!=5)
                $tickets->andWhere('Tic.type = :type')->setParameter('type',$data['Type']);
            $tickets=$tickets->getQuery();
        }

        $pagination = $paginator->paginate(
            $tickets,
            $req->get('page',1) /*page number*/,
            10/*limit per page*/
        );

        return $this->render('HelloDiDiDistributorsBundle:Distributors:Tickets.html.twig',array(
            'Account'=>$Account,
            'form'=>$form->createView(),
            'pagination'=>$pagination
        ));

    }



    public  function  tickestnewAction(Request $req)
    {

        $em=$this->getDoctrine()->getEntityManager();

        $User=$this->get('security.context')->getToken()->getUser();
        $Account=$User->getAccount();

        $form=$this->createFormBuilder()
            ->add('Subject','text',array('label'=>'Subject:'))
            ->add('Type','choice',array('label'=>'Type:',
                'choices'=>array(
                    5=>'All',
                    0=>'Payment issue',
                    1=>'new item request',
                    2=>'price change request'
                )

            ))
            ->add('Description','textarea',array('required'=>true,'label'=>'Description:'))->getForm();

        if($req->isMethod('POST'))
        {
            $tickets=new Ticket();
            $note=new TicketNote();
            $form->handleRequest($req);
            $data=$form->getData();

            $tickets->setAccountdist($Account);
            $tickets->setStatus(1);
            $tickets->setType($data['Type']);
            $tickets->setUser($User);
            $tickets->setSubject($data['Subject']);
            $tickets->setTicketEnd(null);
            $tickets->setTicketStart(new \DateTime('now'));
            $tickets->setLastUser($User);

            $note->setUser($User);
            $note->setDate(new \DateTime('now'));
            $note->setDescription($data['Description']);
            $note->setTicket($tickets);
            $note->setView(0);
            $em->persist($tickets);
            $em->persist($note);
            $em->flush();

        }

        return $this->render('HelloDiDiDistributorsBundle:Distributors:TicketNew.html.twig',array(
            'form'=>$form->createView() ,
            'Account'=>$Account
        ));

    }

    public  function ticketsnoteAction(Request $req,$id)
    {
        $this->check_Ticket($id);
        $note=new TicketNote();
        $em=$this->getDoctrine()->getEntityManager();
        $User=$this->get('security.context')->getToken()->getUser();


        $form=$this->createFormBuilder()
            ->add('Description','textarea',array('required'=>true))->getForm();

        $ticket=$em->getRepository('HelloDiDiDistributorsBundle:Ticket')->find($id);

        if($req->isMethod('POST'))
        {
            $form->submit($req);
            $data=$form->getData();
            $note->setTicket($ticket);
            $note->setView(0);
            $note->setUser($User);
            $note->setDate(new \DateTime('now'));
            $note->setDescription($data['Description']);
            $ticket->setLastUser($User);
            $em->persist($note);
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


        return $this->render('HelloDiDiDistributorsBundle:Distributors:TicketNote.html.twig',array(
            'Ticket'=>$ticket,
            'Notes'=> array_reverse($notes),
            'Account'=>$User->getAccount(),
            'form'=>$form->createView()
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

        return $this->redirect($this->generateUrl('DistTickets'));
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

        return $this->redirect($this->generateUrl('DistTicketsNote',array('id'=>$id)));
    }


    public  function  countnoteAction()
    {
        $User = $this->get('security.context')->getToken()->getUser();
        $em=$this->getDoctrine()->getEntityManager();
        $Countnote=$em->createQueryBuilder();
        $Countnote->select('Note')
            ->from('HelloDiDiDistributorsBundle:TicketNote','Note')
            ->innerJoin('Note.Ticket','NoteTic')
            ->Where('NoteTic.Accountdist = :Acc')->setParameter('Acc',$User->getAccount())
            ->andWhere('Note.User != :usr')->setParameter('usr',$User)
            ->andWhere('Note.view = 0');

        return new Response(count($Countnote->getQuery()->getResult()));
    }


    public function add($a, $b)
    {
        return $a + $b;
    }


 public function DistLoadActionOwnAction(Request $req)
 {
     $id=$req->get('id',0);
     $value='';
     $value.='<option value="All">'.'All'.'</option>';

     switch($id)
     {
         case 0:

             $value.='<option value="amdt">'.'debit distributor,s account'.'</option>';
             $value.='<option value="tran">'.'transfer credit from distributor,s account to a retailer,s account'.'</option>';
             $value.='<option value="crnt">'.'issue a credit note for a sold code'.'</option>';
             $value.='<option value="com_pmt">'.'debit distributor,s account for the commisson payments'.'</option>';

             break;

         case 1:

             $value.='<option value="crlt">'.'inscrease retailer,s credit limit'.'</option>';
             $value.='<option value="pmt">'.'credit distributor,s account'.'</option>';
             $value.='<option value="tran">'.'transfer credit from provider,s account to a distributor,s account'.'</option>';
             $value.='<option value="pmt">'.'ogone payment on its own account'.'</option>';
             $value.='<option value="com">'.'credit commissons when a retailer sells a code'.'</option>';


             break;

         case 2:
             $value.='<option value="amdt">'.'debit distributor,s account'.'</option>';
             $value.='<option value="tran">'.'transfer credit from distributor,s account to a retailer,s account'.'</option>';
             $value.='<option value="crnt">'.'issue a credit note for a sold code'.'</option>';
             $value.='<option value="com_pmt">'.'debit distributor,s account for the commisson payments'.'</option>';

             $value.='<option value="crlt">'.'inscrease retailer,s credit limit'.'</option>';
             $value.='<option value="pmt">'.'credit distributor,s account'.'</option>';
             $value.='<option value="tran">'.'transfer credit from provider,s account to a distributor,s account'.'</option>';
             $value.='<option value="pmt">'.'ogone payment on its own account'.'</option>';
             $value.='<option value="com">'.'credit commissons when a retailer sells a code'.'</option>';
             break;
     }
     return new Response($value);
 }

public function DistLoadActionRetailerAction(Request $req)
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
            $value.='<option value="pmt">'.'ogone payment on its own account'.'</option>';


            break;

        case 2:
            $value.='<option value="All">'.'All'.'</option>';
            $value.='<option value="sale">'.'debit balance when the retailer sell a code'.'</option>';
            $value.='<option value="crnt">'.'issue a credit note for a sold code'.'</option>';
            $value.='<option value="tran">'.'transfer credit from distributor,s account to a retailer,s account'.'</option>';
            $value.='<option value="pmt">'.'ogone payment on its own account'.'</option>';
            break;
    }
    return new Response($value);
}
}

