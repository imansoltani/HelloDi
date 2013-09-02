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
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HelloDi\DiDistributorsBundle\Entity\Account;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

class DistributorsController extends Controller
{

    public function dashboardAction()
    {
        $em=$this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $Account = $user->getAccount();
        $Notifications=$em->getRepository('HelloDiDiDistributorsBundle:Notification')->findBy(array('Account'=>$this->getUser()->getAccount()));
        return $this->render('HelloDiDiDistributorsBundle:Distributors:dashboard.html.twig', array(
            'Account' => $Account,
            'Entiti' => $Account->getEntiti(),
            'User' => $user,
            'Notifications'=>$Notifications
        ));

    }


    #notification#

    public function CountNotificationAction()
    {
        return $this->forward('hello_di_di_notification:CountAction',array('id'=>$this->getUser()->getAccount()->getId()));
    }


    public function ShowLastNotificationAction(){

        $em=$this->getDoctrine()->getManager();
        $Notifications=$em->getRepository('HelloDiDiDistributorsBundle:Notification')->findBy(array('Account'=>$this->getUser()->getAccount()));
        $i=0;
        $str='';
        foreach($Notifications as $Notif)
        {
            $str.='<li id="Notif'.$Notif->getId().'" ><a href="'.$this->generateUrl('DistShowNotification').'"  >';
            if($Notif->getType()==21)
                $str.= $this->get('translator')->trans('Added_user_with_username_%value%',array('value'=>$Notif->getValue()),'notification');
            elseif($Notif->getType()==22)
                $str.= $this->get('translator')->trans('Balance_increased_%value%',array('value'=>$Notif->getValue()),'notification');
            elseif($Notif->getType()==23)
                $str.=   $this->get('translator')->trans('Balance_decreased_%value%',array('value'=>$Notif->getValue()),'notification');
            elseif($Notif->getType()==24)
                $str.=  $this->get('translator')->trans('CreditLimit_increased_%value%',array('value'=>$Notif->getValue()),'notification');
            elseif($Notif->getType()==25)
                $str.=  $this->get('translator')->trans('CreditLimit_decreased_%value%',array('value'=>$Notif->getValue()),'notification');
            elseif($Notif->getType()==26)
                $str.=  $this->get('translator')->trans('Edited_account',array(),'notification');
            elseif($Notif->getType()==27)
                $str.=  $this->get('translator')->trans('Edited_entity',array(),'notification');
            elseif($Notif->getType()==121)
                $str.=  $this->get('translator')->trans('Distributor_account_balance_is_lower_than_equal_%value%',array('value'=>$Notif->getValue()),'notification');
            $str.='</a></li>';

            if(++$i==3)break;
        }

        $str.= '<li><a href="'.$this->generateUrl("DistShowNotification").'">'.$this->get('translator')->trans('Notifications',array(),'notification').'</a></li>';
        return new Response($str);

    }


    public function ShowNotificationAction()
    {
        $em=$this->getDoctrine()->getManager();
        $Notifications=$em->getRepository('HelloDiDiDistributorsBundle:Notification')->findBy(array('Account'=>$this->getUser()->getAccount()));

        return $this->render('HelloDiDiDistributorsBundle:Distributors:Notifications.html.twig',
            array(
                'Account' => $this->getUser()->getAccount(),
                'Entity' => $this->getUser()->getAccount()->getEntiti(),
                'Notifications'=>$Notifications
            ));

    }

    public function ReadNotificationAction(Request $req)
    {
   return $this->forward('hello_di_di_notification:ReadAction',array('id'=>$req->get('id')));
    }

    //Retailers

    public function saleAction(Request $req)
    {

        $paginator = $this->get('knp_paginator');
        $User= $this->get('security.context')->getToken()->getUser();
        $Account=$User->getAccount();
        $em=$this->getDoctrine()->getManager();



        $qb=array();


        $form=$this->createFormBuilder()
            ->add('ItemType','choice',
                array('label'=>'ItemType','translation_domain'=>'item',
                    'choices'=>array(
                        'All' => 'All',
                        'dmtu'=>'Mobile',
                        'clcd'=>'Calling_Card',
                        'epmt'=>'E-payment'
                    )))

            ->add('ItemName', 'entity',
                array(
                    'label'=>'Item','translation_domain'=>'item',
                    'empty_data' => '',
                    'empty_value'=>'All',
                    'required'=>false,
                    'class' => 'HelloDiDiDistributorsBundle:Item',
                    'property' => 'itemName',
                    'query_builder' => function(EntityRepository $er) use ($Account) {
                        return $er->createQueryBuilder('u')
                            ->innerJoin('u.Prices','up')
                            ->andwhere('up.Account = :Acc')->setParameter('Acc',$Account)
                            ->andWhere('up.priceStatus = 1');
                    }
                ))


            ->add('Account', 'entity',
                array('label'=>'Retailer(s)','translation_domain'=>'accounts',
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
            ->add('DateStart','date',
                array(
                     'widget'=>'single_text',
                      'format'=>'yyyy/MM/dd',
                    'data'=>(new \DateTime('now'))->sub(new \DateInterval('P7D')),
                    'label'=>'From',
                    'translation_domain'=>'transaction',
                    'required'=>false
                ))
            ->add('DateEnd','date',array(
                'widget'=>'single_text',
                'data'=>new \DateTime('now'),
                'format'=>'yyyy/MM/dd',
                'label'=>'To',
                'translation_domain'=>'transaction',
                'required'=>false
            ))

            ->getForm();

        if($req->isMethod('POST'))
        {
          try{
            $form->handleRequest($req);
            $data=$form->getData();

            $qb=$em->createQueryBuilder();

            $qb->select('Tr')
                ->from('HelloDiDiDistributorsBundle:Transaction','Tr')
                /*for groupBy*/
                ->innerJoin('Tr.Code','TrCo')->innerJoin('TrCo.Item','TrCoIt');
                /**/
            if($data['Account'])
                $qb->where('Tr.Account=:ac')->setParameter('ac',$data['Account']);

            else
                $qb->where('Tr.Account IN (:Acc)')->setParameter('Acc',(count($Account->getChildrens()->toArray())==0)?-1:$Account->getChildrens()->toArray());

                $qb->andWhere($qb->expr()->like('Tr.tranAction',$qb->expr()->literal('sale')));
                if($data['DateStart']!='')
                $qb->andwhere('Tr.tranDate >= :DateStart')->setParameter('DateStart',$data['DateStart']);
                if($data['DateEnd']!='')
                 $qb->andwhere('Tr.tranDate <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);


            if($data['ItemType']!='All')
               $qb->andwhere($qb->expr()->like('TrCoIt.itemType ', $qb->expr()->literal($data['ItemType'])));

            if($data['ItemName'])
                $qb->andWhere('TrCoIt = :item')->setParameter('item',$data['ItemName']);

            $qb->orderBy('Tr.tranInsert','desc');

            $qb=$qb->getQuery();
            $count = count($qb->getResult());
           $qb->setHint('knp_paginator.count', $count);

          }
          catch(\Exception $e){
              $this->get('session')->getFlashBag()->add('error',
                  $this->get('translator')->trans('You_entered_an_invalid',array(),'message'));
          }

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
                'Entiti' =>$User->getEntiti(),


    ));

    }


    public function GetComAction($id)
    {

        $em=$this->getDoctrine()->getManager();

        $tran=$em->getRepository('HelloDiDiDistributorsBundle:Transaction')->find($id);

        $com=$em->getRepository('HelloDiDiDistributorsBundle:Transaction')->findOneBy(array(
         'Account'=>$tran->getAccount()->getParent(),
         'Code'=>$tran->getCode(),
         'User'=>$tran->getUser(),
          'tranAction'=>'com',
         'tranDate'=>$tran->getTranDate(),
         'tranCurrency'=>$tran->getTranCurrency(),
          'Order'=>$tran->getOrder()
        ));

        return new Response($com->getTranAmount());

    }


    public function  FundingAction($id)
    {
        $this->check_ChildAccount($id);

        $em=$this->getDoctrine()->getManager();

        $Account=$em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $formapplay=$this->createFormBuilder()
            ->add('Amount','money',array(
                'currency'=>$Account->getAccCurrency(),
                'invalid_message'=>'You_entered_an_invalid',
                'label'=>'Amount',
                'translation_domain'=>'transaction'
            ))
            ->add('Communications','textarea',array('label'=>'Communications','translation_domain'=>'transaction','required'=>true))
            ->add('Description','textarea',array('label'=>'Description','translation_domain'=>'transaction','required'=>true))
            ->getForm();

        $formupdate=$this->createFormBuilder()
            ->add('Amount','money',array(
                'currency'=>$Account->getAccCurrency(),
                'invalid_message'=>'You_entered_an_invalid',
                'label'=>'Amount',
                'translation_domain'=>'transaction'))
            ->add('As','choice',array('label'=>'As','translation_domain'=>'transaction',
                'choices'=>
                array(
                    ''=>'select_a_action',
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
            ->add('Amount',null,array('label'=>'Amount','translation_domain'=>'transaction'))
            ->add('Communications','textarea',array('label'=>'Communications','translation_domain'=>'transaction','required'=>false))
            ->add('Description','textarea',array('label'=>'Description','translation_domain'=>'transaction','required'=>false))
            ->getForm();

        if($req->isMethod('post'))
        {
       try{
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
            $trandist->setTranBalance($Account->getParent()->getAccBalance());
            $trandist->setTranDescription($data['Description']);


            #transaction for retailer#

            $tranretailer->setTranDate(new \DateTime('now'));
            $tranretailer->setTranCurrency($Account->getAccCurrency());
            $tranretailer->setTranInsert(new \DateTime('now'));
            $tranretailer->setAccount($Account);
            $tranretailer->setUser($User);
            $tranretailer->setTranFees(0);
            $tranretailer->setTranAction('tran');
            $tranretailer->setTranType(1);
            $tranretailer->setTranBalance($Account->getAccBalance());
            $tranretailer->setTranDescription($data['Communications']);

            $alredyretailer=$Account->getAccBalance();
            $alredydist=$Account->getParent()->getAccBalance();
            if($data['Amount']>0)
            {
                if($balancechecker->isBalanceEnoughForMoney($Account->getParent(),$data['Amount']))
                {
                    $tranretailer->setTranAmount(+$data['Amount']);
                    $trandist->setTranAmount(-$data['Amount']);
                    $em->persist($trandist);
                    $em->persist($tranretailer);


                    $this->forward('hello_di_di_notification:NewAction',array('id'=>$Account->getId(),'type'=>32,'value'=>$data['Amount'].' '.$Account->getAccCurrency()));

                    if($Account->getAccBalance()+$Account->getAccCreditLimit()<=15000)
                        $this->forward('hello_di_di_notification:NewAction',array('id'=>$Account->getId(),'type'=>31,'value'=>'15000 ' .$Account->getAccCurrency()));


                    if($Account->getParent()->getAccBalance()+$Account->getParent()->getAccCreditLimit()<=15000)
                    {
                        $this->forward('hello_di_di_notification:NewAction',array('id'=>$Account->getParent()->getId(),'type'=>121,'value'=>'15000 ' .$Account->getParent()->getAccCurrency()));
                        $this->forward('hello_di_di_notification:NewAction',array('id'=>null,'type'=>121,'value'=>'15000 ' .$Account->getParent()->getAccCurrency().'   ('.$Account->getParent()->getAccName().')'));
                    }

                    $em->flush();


                    $this->get('session')->getFlashBag()->add('success',
                        $this->get('translator')->trans('Retailer_account_was_changed_from_%alredyretailer%_to_%currentretailer%',
                            array('alredyretailer'=>$alredyretailer,'currentretailer'=>$Account->getAccBalance()),
                            'message')
                    );

                    $this->get('session')->getFlashBag()->add('success',
                        $this->get('translator')->trans('Distributor_account_was_changed_from_%alredydist%_to_%currentdist%',
                            array('alredydist'=>$alredydist,'currentdist'=>$Account->getParent()->getAccBalance()),
                            'message')
                    );
                }

            }
else
    $this->get('session')->getFlashBag()->add('error',
        $this->get('translator')->trans('Please_input_a_number_greater_than_zero',array(),'message'));

        }

  catch(\Exception $e)
       {
           $this->get('session')->getFlashBag()->add('error',
               $this->get('translator')->trans('You_entered_an_invalid',array(),'message'));

       }

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
            ->add('Amount','text',array('label'=>'Amount','translation_domain'=>'transaction'))
            ->add('As','choice',array('label'=>'As','translation_domain'=>'transaction',
                'choices'=>array(
                    1=>'Increase',
                    0=>'Decrease')
            ))->getForm();

        if($req->isMethod('POST'))
        {
          try{
            $formupdate->handleRequest($req);
            $data=$formupdate->getData();

            $trandist=new Transaction();

            $trandist->setTranDate(new \DateTime('now'));
            $trandist->setTranCurrency($Account->getParent()->getAccCurrency());

            $trandist->setTranInsert(new \DateTime('now'));

            $trandist->setUser($User);
            $trandist->setTranFees(0);
            $trandist->setTranAction('crlt');
            $trandist->setTranType(0);
            $trandist->setAccount($Account->getParent());
            $trandist->setTranDescription('increase retailer,s credit limit ');

            $alredyretailer=$Account->getAccCreditLimit();
            $alredydist=$Account->getParent()->getAccBalance();
if($data['Amount']>0)
{

    if($data['As']==1)
    {
        if($balancechecker->isBalanceEnoughForMoney($Account->getParent(),$data['Amount']))
        {
            $trandist->setTranBalance($Account->getParent()->getAccBalance());
            $trandist->setTranAmount(-$data['Amount']);
            $Account->setAccCreditLimit($Account->getAccCreditLimit()+$data['Amount']);
            $em->persist($trandist);
            $em->flush();

         $this->forward('hello_di_di_notification:NewAction',array('id'=>$Account->getId(),'type'=>33,'value'=>$data['Amount'].' '.$Account->getAccCurrency()));


         if($Account->getAccBalance()+$Account->getAccCreditLimit()<=15000)
                $this->forward('hello_di_di_notification:NewAction',array('id'=>$Account->getId(),'type'=>31,'value'=>'15000 ' .$Account->getAccCurrency()));

         if($Account->getParent()->getAccBalance()+$Account->getParent()->getAccCreditLimit()<=15000)
            {
                $this->forward('hello_di_di_notification:NewAction',array('id'=>$Account->getParent()->getId(),'type'=>121,'value'=>'15000 ' .$Account->getParent()->getAccCurrency()));
                $this->forward('hello_di_di_notification:NewAction',array('id'=>null,'type'=>121,'value'=>'15000 ' .$Account->getParent()->getAccCurrency().'   ('.$Account->getParent()->getAccName().')'));
            }





            $this->get('session')->getFlashBag()->add('success',
                $this->get('translator')->trans('Retailer_creditlimit_was_changed_from_%alredyretailer%_to_%currentretailer%',
                    array('alredyretailer'=>$alredyretailer,'currentretailer'=>$Account->getAccCreditLimit()),
                    'message')
            );

            $this->get('session')->getFlashBag()->add('success',
                $this->get('translator')->trans('Distributor_account_was_changed_from_%alredydist%_to_%currentdist%',
                    array('alredydist'=>$alredydist,'currentdist'=>$Account->getParent()->getAccBalance()),
                    'message')
            );
        }
    }

    elseif($data['As']==0)
    {

        if($balancechecker->isAccCreditLimitPlus($Account,$data['Amount']))
        {
            $Account->setAccCreditLimit($Account->getAccCreditLimit()- $data['Amount']);
            $em->flush();

            $this->forward('hello_di_di_notification:NewAction',array('id'=>$Account->getId(),'type'=>34,'value'=>$data['Amount'].' '.$Account->getAccCurrency()));

            $this->get('session')->getFlashBag()->add('success',
                $this->get('translator')->trans('Retailer_creditlimit_was_changed_from_%alredyretailer%_to_%currentretailer%',
                    array('alredyretailer'=>$alredyretailer,'currentretailer'=>$Account->getAccCreditLimit()),
                    'message')
            );


        }
    }

}
else
    $this->get('session')->getFlashBag()->add('error',
        $this->get('translator')->trans('Please_input_a_number_greater_than_zero',array(),'message'));

}
catch(\Exception $e)
{
    $this->get('session')->getFlashBag()->add('error',
        $this->get('translator')->trans('You_entered_an_invalid',array(),'message'));

}
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
        $em=$this->getDoctrine()->getManager();
        $user= $this->getUser();
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
                'Users' => $users
            ));
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

                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));

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
                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
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
                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
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
                $this->forward('hello_di_di_notification:NewAction',array('id'=>$Account->getId(),'type'=>37,'value'=>$user->getUsername()));
                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
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
        $userdist = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $user = new User();

        $AdrsDetai = new DetailHistory();

        $Entiti = new Entiti();

        $Account = new Account();

        $Account->setAccCreditLimit(0);
        $Account->setAccCreationDate(new \DateTime('now'));
        $Account->setAccTimeZone(null);
        $Account->setAccType(2);
        $Account->setAccBalance(0);
        $Account->setAccCurrency($userdist->getAccount()->getAccCurrency());
        $Account->setParent($userdist->getAccount());


        $Account->setEntiti($Entiti);
        $Entiti->addAccount($Account);


        $user->setEntiti($Entiti);
        $Entiti->addUser($user);


        $user->setAccount($Account);
        $Account->addUser($user);



        $form = $this->createForm(new NewRetailersType(), $Entiti, array('cascade_validation' => true));

        if ($request->isMethod('POST')) {

            $form->handleRequest($request);

            if ($form->isValid()) {

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
                $this->forward('hello_di_di_notification:NewAction',array('id'=>null,'type'=>13,'value'=>'   ('.$Account->getAccName().')'));
            $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
           return $this->redirect($this->generateUrl('retailer_show',array('id',$user->getAccount()->getId())));
            }




        }

        return $this->render('HelloDiDiDistributorsBundle:Distributors:NewRetailer.html.twig', array(
            'form_Relaited_New' => $form->createView(),
            'Account' => $Account
        ));

    }

    public function ShowRetaierAccountAction(Request $request)
    {
        $paginator = $this->get('knp_paginator');
        $em = $this->getDoctrine()->getManager();
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
                                  'label'=>'City',
                                  'translation_domain'=>'entity',
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
                                   'label'=>'Balance',
                                   'translation_domain'=>'accounts',
                                   'choices'=>(array(
                                       0=>'<',
                                       1=>'>',
                                       2=>'=' )
                                                     )))
                       ->add('BalanceValue','money',
                                array(
                                    'currency'=>$Account->getAccCurrency(),
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

        return $this->render('HelloDiDiDistributorsBundle:Distributors:ShowRetailers.html.twig', array (
            'Retailers' => $qb->getResult(),
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
            ->add('TypeDate','choice', array('translation_domain'=>'transaction',
                'expanded'   => true,
                'choices'    => array(
                    0 => 'TradeDate',
                    1 => 'BookingDate',
                )

            ))
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

            ->add('Type','choice',
                array('label'=>'Type','translation_domain'=>'transaction','choices'=>
                array(
                    2=>'All',
                    0=>'Debit',
                    1=>'Credit',
                )))

            ->add('Action', 'choice', array('label'=>'Action','translation_domain'=>'transaction',
                'choices' =>
                array(
                    'All'=>'All',
                    'sale'=>'debit_balance_when_the_retailer_sell_a_code',
                    'crnt'=>'issue_a_credit_note_for_a_sold_code',
                    'tran'=>'transfer_credit_from_distributor,s_account_to_a_retailer,s_account',
                    'ogn_pmt'=>'ogone_payment_on_its_own_account'
                )))

            ->getForm();


        if($req->isMethod('POST'))
        {
try{
            $form->handleRequest($req);
            $data=$form->getData();
            $qb=$em->createQueryBuilder();
            $qb->select('Tran')
                ->from('HelloDiDiDistributorsBundle:Transaction','Tran')
                ->where('Tran.Account = :Acc')->setParameter('Acc',$Account);

            if($data['TypeDate']==1)$typedate=1; else $typedate=0;

            if($data['DateStart']!='')
               $qb->andwhere('Tran.tranInsert >= :DateStart')->setParameter('DateStart',$data['DateStart']);
            if($data['DateEnd']!='')
               $qb->andwhere('Tran.tranInsert <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);

            if ($data['Type'] != 2)
                $qb->andWhere($qb->expr()->eq('Tran.tranType',$data['Type']));

            if($data['Action']!='All')
                $qb->andWhere($qb->expr()->like('Tran.tranAction',$qb->expr()->literal($data['Action'])));

            $qb->addOrderBy('Tran.tranInsert','desc')->addOrderBy('Tran.id','desc');;

            $qb=$qb->getQuery();
            $count = count($qb->getResult());
            $qb->setHint('knp_paginator.count', $count);
        }
catch(\Exception $e){
    $this->get('session')->getFlashBag()->add('error',
        $this->get('translator')->trans('You_entered_an_invalid',array(),'message'));
}
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


    public function DistTransactionAction(Request $req)
{



    $paginator = $this->get('knp_paginator');
    $em=$this->getDoctrine()->getManager();

    $Account=$this->get('security.context')->getToken()->getUser()->getAccount();

    $qb=array();

    $form=$this->createFormBuilder()

        ->add('Type','choice',
            array('label'=>'Type','translation_domain'=>'transaction','choices'=>
            array(
                  2=>'All',
                  0=>'Debit',
                  1=>'Credit',
            )))

        ->add('Action', 'choice', array('label'=>'Action','translation_domain'=>'transaction',
            'choices' =>
              array(
                  'All' =>'All',
                  'pmt' =>'credit_distributor,s_account',
                  'amdt' =>'debit_distributor,s_account',
                  'crnt'=>'issue_a_credit_note_for_a_sold_code',
                  'com_pmt' =>'debit_distributor,s_account_for_the_commisson_payments',
                  'ogn_pmt' =>'ogone_payment_on_its_own_account',
                  'tran'=>'transfer_credit_from_provider,s_account_to_a_distributor,s_account',
                  'tran'=>'transfer_credit_from_distributors_account_to_a_retailer,s_account',
                  'crlt'=>'increase_retailer,s_credit_limit',
                  'com'=>'credit_commissons_when_a_retailer_sells_a_code'

              )))

        ->add('DateStart','date',
            array(
                'widget'=>'single_text',
                'format'=>'yyyy/MM/dd',
                'label'=>'From',
                'translation_domain'=>'transaction',
                'required'=>false
            ))
        ->add('DateEnd','date',
            array
            (   'label'=>'To',
                'widget'=>'single_text',
                'format'=>'yyyy/MM/dd',
                'translation_domain'=>'transaction',
                'required'=>false)
        )
        ->add('TypeDate','choice', array('translation_domain'=>'transaction',
            'empty_value'=>'TradeDate',
            'expanded'   => true,
            'choices'    => array(
                0 => 'TradeDate',
                1 => 'BookingDate',
            )
        ))->getForm();

    $typedate=0;
    if($req->isMethod('POST'))
    {
        try{
        $form->handleRequest($req);
        $data=$form->getData();

        $qb=$em->createQueryBuilder();
        $qb->select('Tran')
            ->from('HelloDiDiDistributorsBundle:Transaction','Tran')
            ->where('Tran.Account = :Acc')->setParameter('Acc',$Account);

        if($data['TypeDate']==1) $typedate=1; else $typedate=0;
        if($data['DateStart']!='')
           $qb->andwhere('Tran.tranInsert >= :DateStart')->setParameter('DateStart',$data['DateStart']);
        if($data['DateEnd']!='')
            $qb->andwhere('Tran.tranInsert <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);

        if ($data['Type'] != 2)
            $qb->andWhere($qb->expr()->eq('Tran.tranType',$data['Type']));

        if($data['Action']!='All')
            $qb->andWhere($qb->expr()->like('Tran.tranAction',$qb->expr()->literal($data['Action'])));

        $qb->addOrderBy('Tran.tranInsert','desc')->addOrderBy('Tran.id','desc');;

        $qb=$qb->getQuery();
        $count = count($qb->getResult());
        $qb->setHint('knp_paginator.count', $count);

    }
        catch(\Exception $e){
            $this->get('session')->getFlashBag()->add('error',
                $this->get('translator')->trans('You_entered_an_invalid',array(),'message'));
        }
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
                $this->forward('hello_di_di_notification:NewAction',array('id'=>$Account->getId(),'type'=>35));
                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
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
        $detahis=new DetailHistory();
        $editForm = $this->createForm(new EditEntitiRetailerType(),$entity);

        if($req->isMethod('post'))
        {
         $editForm->handleRequest($req);
         if($editForm->isValid())
         {

             $detahis->setAdrs1($entity->getEntAdrs1());
             $detahis->setAdrs2($entity->getEntAdrs2());
             $detahis->setAdrs3($entity->getEntAdrs3());
             $detahis->setAdrsCity($entity->getEntCity());
             $detahis->setAdrsNp($entity->getEntNp());
             $detahis->setCountry($entity->getCountry());
             $detahis->setAdrsDate(new \DateTime('now'));
             $detahis->setEntiti($entity);

             $em->persist($detahis);
             $em->flush();

             $this->forward('hello_di_di_notification:NewAction',array('id'=>$Account->getId(),'type'=>36));


             $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
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
            ->add('NewPrice','text',array('required'=>true,'label' => 'NewPrice','translation_domain' => 'price'))
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $data = $form->getData();
            $newprice = $data['NewPrice'];
            $distprice = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Item'=>$item,'Account'=>$account))->getPrice();
            if($newprice < $distprice)
                $form->get('NewPrice')->addError(new FormError($this->get('translator')->trans('New_price_can_not_less_than_price_on_this_item_in_your_distributor',array(),'message')));
            if ($form->isValid()) {
                $actiontype = $request->get("actiontype");
                foreach ($data['checks'] as $accountretailer)
                {
                    $retprice = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Item'=>$item,'Account'=>$accountretailer));
                    if($actiontype == "1")
                    {
                        if($retprice != null) $retprice->setPriceStatus(0);
                    }
                    else
                    {
                        if($retprice != null)
                        {
                            if($retprice->getPrice() != $newprice)
                            {
                                $retprice->setPrice($newprice);

                                $pricehistory = new PriceHistory();
                                $pricehistory->setPrice($newprice);
                                $pricehistory->setDate(new \DateTime('now'));
                                $pricehistory->setPrices($retprice);
                                $em->persist($pricehistory);
                            }
                            $retprice->setPriceStatus(1);
                        }
                        else
                        {
                            $retprice = new Price();
                            $retprice->setPrice($newprice);
                            $retprice->setPriceCurrency($accountretailer->getAccCurrency());
                            $retprice->setPriceStatus(true);
                            $retprice->setIsFavourite(true);
                            $retprice->setItem($item);
                            $retprice->setAccount($accountretailer);
                            $em->persist($retprice);

                            $pricehistory = new PriceHistory();
                            $pricehistory->setPrice($newprice);
                            $pricehistory->setDate(new \DateTime('now'));
                            $pricehistory->setPrices($retprice);
                            $em->persist($pricehistory);
                        }
                    }
                }
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl('items_item_per_retailers', array('itemid' => $itemid)));
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
                    },
                    'label' => 'Item','translation_domain' => 'item'
                ))
            ->add('price','integer',array('label' => 'Price','translation_domain' => 'price'))
            ->getForm();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $distprice = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Item'=>$price->getItem(),'Account'=>$myaccount))->getPrice();
            if( $price->getPrice()<$distprice)
                $form->get('price')->addError(new FormError($this->get('translator')->trans('New_price_can_not_less_than_price_on_this_item_in_your_distributor',array(),'message')));
            if ($form->isValid()) {
                $em->persist($price);

                $pricehistory = new PriceHistory();
                $pricehistory->setDate(new \DateTime('now'));
                $pricehistory->setPrice($price->getPrice());
                $pricehistory->setPrices($price);
                $em->persist($pricehistory);

                $em->flush();
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
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
            $distprice = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Item'=>$price->getItem(),'Account'=>$myaccount))->getPrice();
            if( $price->getPrice()<$distprice)
                $form->get('price')->addError(new FormError($this->get('translator')->trans('New_price_can_not_less_than_price_on_this_item_in_your_distributor',array(),'message')));
            if ($form->isValid()) {
                if ($price->getPrice() != $oldprice) {
                    $pricehistory = new PriceHistory();
                    $pricehistory->setDate(new \DateTime('now'));
                    $pricehistory->setPrice($price->getPrice());
                    $pricehistory->setPrices($price);
                    $em->persist($pricehistory);
                }
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
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
            throw new \Exception($this->get('translator')->trans('have_not_permission_%object%',array('object'=>$this->get('translator')->trans('Item',array(),'item')),'message'));
        }
    }

    private function check_ChildAccount($accountid)
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($accountid);
        if($account == null || $account->getParent() == null || $account->getParent() != $myaccount)
        {
            throw new \Exception($this->get('translator')->trans('have_not_permission_%object%',array('object'=>$this->get('translator')->trans('Account',array(),'accounts')),'message'));
        }
    }

    private function check_ChildPrice($priceid)
    {
        $em = $this->getDoctrine()->getManager();
        $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->find($priceid);
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
        if($price == null || $price->getAccount() == null || $price->getAccount()->getParent() == null || $price->getAccount()->getParent() != $myaccount)
        {
            throw new \Exception($this->get('translator')->trans('have_not_permission_%object%',array('object'=>$this->get('translator')->trans('Price',array(),'price')),'message'));
        }
    }

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

    private function check_ChildUser($userid)
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('HelloDiDiDistributorsBundle:User')->find($userid);
        if($user == null || $user->getAccount() == null || $user->getAccount()->getParent() == null || $user->getAccount()->getParent()!= $myaccount)
        {
            throw new \Exception($this->get('translator')->trans('have_not_permission_%object%',array('object'=>$this->get('translator')->trans('User',array(),'user')),'message'));
        }
    }

    private function check_Transaction($tranid)
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
        $em = $this->getDoctrine()->getManager();
        $tran = $em->getRepository('HelloDiDiDistributorsBundle:Transaction')->find($tranid);
        if($tran == null || $tran->getAccount() == null || $tran->getAccount() != $myaccount)
        {
            throw new \Exception($this->get('translator')->trans('have_not_permission_%object%',array('object'=>$this->get('translator')->trans('Transaction',array(),'transaction')),'message'));
        }
    }

    private function check_ChildTransaction($tranid)
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
        $em = $this->getDoctrine()->getManager();
        $tran = $em->getRepository('HelloDiDiDistributorsBundle:Transaction')->find($tranid);
        if($tran == null || $tran->getAccount() == null || $tran->getAccount()->getParent() == null || $tran->getAccount()->getParent() != $myaccount)
        {
            throw new \Exception($this->get('translator')->trans('have_not_permission_%object%',array('object'=>$this->get('translator')->trans('Transaction',array(),'transaction')),'message'));
        }
    }

    private function check_Ticket($ticketid)
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
        $em = $this->getDoctrine()->getManager();
        $ticket = $em->getRepository('HelloDiDiDistributorsBundle:Ticket')->find($ticketid);
        if($ticket == null || $ticket->getAccountdist() == null || $ticket->getAccountdist() != $myaccount)
        {
            throw new \Exception($this->get('translator')->trans('have_not_permission_%object%',array('object'=>$this->get('translator')->trans('Ticket',array(),'ticket')),'message'));
        }
    }


    /////tickets


    public  function  ticketsAction(Request $req)
    {
        $paginator = $this->get('knp_paginator');

        $em=$this->getDoctrine()->getManager();

        $User=$this->get('security.context')->getToken()->getUser();

        $Account=$User->getAccount();

        $form=$this->createFormBuilder()
            ->add('Type','choice',array('label'=>'Type','translation_domain'=>'ticket',
                'choices'=>array(
                   -1=>'All',
                    0=>'Payment_issue',
                    1=>'new_item_request',
                    2=>'price_change_request',
                    3=>'address_change',
                    4=>'account_change_requests',
                    5=>'bug_reporting',
                    6=>'support'

                )
            ))

            ->add('Status','choice',array('label'=>'Status','translation_domain'=>'ticket',
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
            if($data['Type']!=-1)
                $tickets->andWhere('Tic.type = :type')->setParameter('type',$data['Type']);
            $tickets=$tickets->getQuery();
            $count = count($tickets->getResult());
            $tickets->setHint('knp_paginator.count', $count);

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



    public  function  tickestnewAction(Request $req,$data)
    {

        $em=$this->getDoctrine()->getManager();

        $User=$this->get('security.context')->getToken()->getUser();
        $Account=$User->getAccount();

        $form=$this->createFormBuilder(array('Type'=>$data))
            ->add('Subject','text',array('label'=>'Subject','translation_domain'=>'ticket'))
            ->add('Type','choice',array('label'=>'Type','translation_domain'=>'ticket',
                'choices'=>array(
                    0=>'Payment_issue',
                    1=>'new_item_request',
                    2=>'price_change_request',
                    3=>'address_change',
                    4=>'account_change_requests',
                    5=>'bug_reporting',
                    6=>'support'
                )

            ))
            ->add('Description','textarea',array('required'=>true,'label'=>'Description','translation_domain'=>'ticket'))->getForm();

        if($req->isMethod('POST'))
        {
            $tickets=new Ticket();
            $note=new TicketNote();
            $form->handleRequest($req);
            $data=$form->getData();

            $tickets->setAccountdist($Account);
            $tickets->setStatus(1);
            $tickets->setType($data['Type']);
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

            $this->get('session')->getFlashBag()->add('success',
                                $this->get('translator')->trans('ticket_create_successfully',array(),'message'));

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
        $em=$this->getDoctrine()->getManager();
        $User=$this->get('security.context')->getToken()->getUser();


        $form=$this->createFormBuilder()
            ->add('Description','textarea',array('required'=>true,'label'=>'Description','translation_domain'=>'ticket'))->getForm();

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
            ->Where('Note.User not in (:usr)')->setParameter('usr',$User->getAccount()->getUsers()->ToArray())
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

        return $this->redirect($this->generateUrl('DistTickets'));
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

        return $this->redirect($this->generateUrl('DistTicketsNote',array('id'=>$id)));
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
            ->Where('NoteTic.Accountdist = :Acc')->setParameter('Acc',$User->getAccount())
            ->andWhere('Note.view = 0')
            ->andWhere('Note.User NOT IN(:usr)')->setParameter('usr',$users->toArray());
        return new Response(count($Countnote->getQuery()->getResult()));
    }

 public function DistLoadActionOwnAction(Request $req)
 {
     $id=$req->get('id',0);
     $value='';
     $value.='<option value="All">'.
         $this->get('translator')->trans('All',[],'transaction')
         .'</option>';

     switch($id)
     {
         case 0:

             $value.='<option value="crlt">'.
                 $this->get('translator')->trans('increase_retailer,s_credit_limit',[],'transaction')
                 .'</option>';

             $value.='<option value="amdt">'.
                 $this->get('translator')->trans('debit_distributor,s_account',[],'transaction')
                 .'</option>';

             $value.='<option value="tran">'.
                 $this->get('translator')->trans('transfer_credit_from_distributor,s_account_to_a_retailer,s_account',[],'transaction').
                 '</option>';

             $value.='<option value="crnt">'.
                 $this->get('translator')->trans('issue_a_credit_note_for_a_sold_code',[],'transaction')
                 .'</option>';

             $value.='<option value="com_pmt">'.
                 $this->get('translator')->trans('debit_distributor,s_account_for_the_commisson_payments',[],'transaction')
                 .'</option>';

             break;

         case 1:



             $value.='<option value="pmt">'.
                 $this->get('translator')->trans('credit_distributor,s_account',[],'transaction')
                 .'</option>';

             $value.='<option value="tran">'.
                 $this->get('translator')->trans('transfer_credit_from_provider,s_account_to_a_distributor,s_account',[],'transaction')
                 .'</option>';

             $value.='<option value="ogn_pmt">'.
                 $this->get('translator')->trans('ogone_payment_on_its_own_account',[],'transaction')
                 .'</option>';

             $value.='<option value="com">'.
                  $this->get('translator')->trans('credit_commissons_when_a_retailer_sells_a_code',[],'transaction')
                 .'</option>';


             break;

         case 2:

             $value.='<option value="amdt">'.
                 $this->get('translator')->trans('debit_distributor,s_account',[],'transaction')
                 .'</option>';

             $value.='<option value="tran">'.
                 $this->get('translator')->trans('transfer_credit_from_distributor,s_account_to_a_retailer,s_account',[],'transaction').
                 '</option>';

             $value.='<option value="crnt">'.
                 $this->get('translator')->trans('issue_a_credit_note_for_a_sold_code',[],'transaction')
                 .'</option>';

             $value.='<option value="com_pmt">'.
                 $this->get('translator')->trans('debit_distributor,s_account_for_the_commisson_payments',[],'transaction')
                 .'</option>';

             $value.='<option value="crlt">'.
                 $this->get('translator')->trans('increase_retailer,s_credit_limit',[],'transaction')
                 .'</option>';

             $value.='<option value="pmt">'.
                 $this->get('translator')->trans('credit_distributor,s_account',[],'transaction')
                 .'</option>';

             $value.='<option value="tran">'.
                 $this->get('translator')->trans('transfer_credit_from_provider,s_account_to_a_distributor,s_account',[],'transaction')
                 .'</option>';

             $value.='<option value="ogn_pmt">'.
                 $this->get('translator')->trans('ogone_payment_on_its_own_account',[],'transaction')
                 .'</option>';

             $value.='<option value="com">'.
                 $this->get('translator')->trans('credit_commissons_when_a_retailer_sells_a_code',[],'transaction')
                 .'</option>';

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
}

