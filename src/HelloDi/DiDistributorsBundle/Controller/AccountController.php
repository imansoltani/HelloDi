<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Entity\Code;
use HelloDi\DiDistributorsBundle\Entity\Entiti;
use HelloDi\DiDistributorsBundle\Entity\Input;
use HelloDi\DiDistributorsBundle\Entity\Price;
use HelloDi\DiDistributorsBundle\Entity\PriceHistory;
use HelloDi\AccountingBundle\Entity\Transaction;
use HelloDi\DiDistributorsBundle\Entity\User;
use HelloDi\DiDistributorsBundle\Form\Account\EditDistType;
use HelloDi\DiDistributorsBundle\Form\Account\EditProvType;
use HelloDi\DiDistributorsBundle\Form\Account\EditRetailerType;
use HelloDi\DiDistributorsBundle\Form\Account\MakeAccountIn2StepType;
use HelloDi\DiDistributorsBundle\Form\Entiti\EditEntitiRetailerType;
use HelloDi\DiDistributorsBundle\Form\PriceEditType;
use HelloDi\DiDistributorsBundle\Form\User\NewUserType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HelloDi\AccountingBundle\Entity\Account;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Collection;


class AccountController extends Controller
{

    #notification#



    public function CountNotificationAction()

    {
        return ($this->forward('hello_di_di_notification:CountAction',array('id'=>null)));
    }


    public function ShowLastNotificationAction(){

        $em=$this->getDoctrine()->getManager();
        $Notifications=$em->getRepository('HelloDiDiDistributorsBundle:Notification')->findBy(array('Account'=>null));
     $i=0;
     $str='';
           foreach($Notifications as $Notif)
           {
               $str.='<li id="Notif'.$Notif->getId().'" ><a href="'.$this->generateUrl('MasterShowNotification').'"  >';

                   if($Notif->getType()==11)
                     $str.= $this->get('translator')->trans('Codes_for_this_%value%_is_very_low',array('value'=>$Notif->getValue()),'notification');
                       elseif($Notif->getType()==12)
                           $str.= $this->get('translator')->trans('Provider_account_balance_is_lower_than_equal_%value%',array('value'=>$Notif->getValue()),'notification');
                           elseif($Notif->getType()==13)
                               $str.=   $this->get('translator')->trans('Retailer_created_an_account',array('value'=>$Notif->getValue()),'notification');
                               elseif($Notif->getType()==121)
                                   $str.=  $this->get('translator')->trans('Distributor_account_balance_is_lower_than_equal_%value%',array('value'=>$Notif->getValue()),'notification');

               $str.='</a></li>';

                if(++$i==3)break;
           }
        $str.= '<li><a href="'.$this->generateUrl("MasterShowNotification").'">'.$this->get('translator')->trans('Notifications',array(),'notification').'</a></li>';
       return new Response($str);
    }

    public function ShowNotificationAction()
    {
        $em=$this->getDoctrine()->getManager();
        $Notifications=$em->getRepository('HelloDiDiDistributorsBundle:Notification')->findBy(array('Account'=>null));

        return $this->render('HelloDiDiDistributorsBundle:Notifications:Notifications.html.twig',
            array(
                'Notifications'=>$Notifications
            ));

    }



    public function ReadNotificationAction(Request $req)
    {

    return  $this->forward('hello_di_di_notification:ReadAction',array('id'=>$req->get('id')));

    }


    public function ShowMyAccountProvAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $query = $em->getRepository('HelloDiAccountingBundle:Account')->findBy(array('accType' => 1));

        return $this->render('HelloDiDiDistributorsBundle:Account:ShowMyAccountProv.html.twig', array
        ('pagination' => $query));

    }


    public function AddAccountProveMaster2StepAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $Entiti = new Entiti();
        $Account = new Account();
        $Account->setAccCreationDate(new \DateTime('now'));
        $Account->setAccTimeZone(null);
        $Account->setAccType(1);
        $Account->setAccBalance(0);
        $Account->setAccCreditLimit(0);
        $Account->setEntiti($Entiti);
        $Entiti->addAccount($Account);

        $form2step = $this->createForm(new MakeAccountIn2StepType($this->container->getParameter('Currencies.Account')), $Entiti, array('cascade_validation' => true));

        if ($request->isMethod('POST')) {
            $form2step->handleRequest($request);

            if ($form2step->isValid()) {
                $em->persist($Entiti);
                $em->persist($Account);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'this operation done success !');
                return $this->redirect($this->generateUrl('ShowMyAccountProv'));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Account:AddAccountProvMaster2Step.html.twig', array(
            'form2step' => $form2step->createView(),
        ));
    }


    public function AddAccountDistMaster2StepAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $Entiti = new Entiti();
        $Account = new Account();
        $User = new User();

        $Account->setAccCreationDate(new \DateTime('now'));
        $Account->setAccTimeZone(null);
        $Account->setAccType(0);
        $Account->setAccBalance(0);
        $Account->setAccCreditLimit(0);

        $Account->setEntiti($Entiti);
        $Entiti->addAccount($Account);

        $User->setEntiti($Entiti);
        $Entiti->addUser($User);

        $User->setAccount($Account);
        $Account->addUser($User);

        $form2step = $this->createForm(new MakeAccountIn2StepType($this->container->getParameter('Currencies.Account')), $Entiti,
            array(
                'cascade_validation' => true
            ));

        if ($request->isMethod('POST')) {
            $form2step->handleRequest($request);
            if ($form2step->isValid()) {
                $em->persist($Entiti);
                $em->persist($Account);
                $em->persist($User);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'this operation done success !');
                return $this->redirect($this->generateUrl('ShowMyAccountDist'));

            }

        }


        return $this->render('HelloDiDiDistributorsBundle:Account:AddAccountDistMaster2Step.html.twig', array(
            'form2step' => $form2step->createView(),

        ));
    }


    public function ManageProvAction($id)
    {
        return $this->render('HelloDiDiDistributorsBundle:Account:ManageProv.html.twig', array('id' => $id));
    }

    public function EditAccountProvAction(Request $request, $id)
    {

        $account = new Account();
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);
        $edit_form = $this->createForm(new EditProvType(), $account);


        if ($request->isMethod('post')) {

            $edit_form->handleRequest($request);
            if ($edit_form->isValid()) {

                $em->flush($account);
                $this->get('session')->getFlashBag()->add('success', 'this operation done success !');

            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Account:EditAccountProv.html.twig', array(
                'form_edit' => $edit_form->createView(),
                'Account' => $account,
                'id' => $id)
        );
    }


    public function  DistTransactionAction(Request $req, $id)
    {

        $paginator = $this->get('knp_paginator');

        $em = $this->getDoctrine()->getManager();

        $Account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);
        $datetype = 0;


        $qb = array();
        $form = $this->createFormBuilder()

            ->add('DateStart', 'date',
                array(
                    'widget'=>'single_text',
                    'format'=>'yyyy/MM/dd',
                    'required' => false, 'label' => 'From','translation_domain' => 'transaction',))
            ->add('DateEnd', 'date',
                array(
                    'widget'=>'single_text',
                    'format'=>'yyyy/MM/dd',
                    'required' => false, 'label' => 'To','translation_domain' => 'transaction',))
            ->add('TypeDate', 'choice', array('translation_domain' => 'transaction',
                'expanded' => true,
                'choices' => array(
                    0 => 'TradeDate',
                    1 => 'BookingDate',
                )
            ))
            ->add('Type', 'choice', array('translation_domain' => 'transaction',
                'choices' => array(
                    2 => 'All',
                    1 => 'Credit',
                    0 => 'Debit'
                )))
            ->add('Action', 'choice', array('label'=>'Action','translation_domain' => 'transaction',
                'choices' =>
                array(
                    'All' => 'All',
                    'pmt' => 'credit_distributor,s_account',
                    'amdt' => 'debit_distributor,s_account',
                    'crnt' => 'issue_a_credit_note_for_a_sold_code',
                    'com_pmt' => 'debit_distributor,s_account_for_the_commisson_payments',
                    'ogn_pmt' => 'ogone_payment_on_its_own_account',
                    'tran' => 'transfer_credit_from_provider,s_account_to_a_distributor,s_account',
                    'tran' => 'transfer_credit_from_distributors_account_to_a_retailer,s_account',
                    'crlt' => 'increase_retailer,s_credit_limit',
                    'com' => 'credit_commissons_when_a_retailer_sells_a_code'
                )))
            ->getForm();


        if ($req->isMethod('POST')) {
            try{
            $form->handleRequest($req);
            $data = $form->getData();
            $qb = $em->createQueryBuilder();
            $qb->select('Tran')
                ->from('HelloDiAccountingBundle:Transaction', 'Tran')
                ->where('Tran.Account = :Acc')->setParameter('Acc', $Account);
            if ($data['TypeDate'] == 0) {
                if ($data['DateStart'] != '')
                    $qb->andwhere('Tran.tranDate >= :DateStart')->setParameter('DateStart', $data['DateStart']);
                if ($data['DateEnd'] != '')
                    $qb->andwhere('Tran.tranDate <= :DateEnd')->setParameter('DateEnd', $data['DateEnd']);
            }

            if ($data['TypeDate'] == 1) {
                $datetype = 1;
                if ($data['DateStart'] != '')
                    $qb->andwhere('Tran.tranInsert >= :DateStart')->setParameter('DateStart', $data['DateStart']);
                if ($data['DateEnd'] != '')
                    $qb->andwhere('Tran.tranInsert <= :DateEnd')->setParameter('DateEnd', $data['DateEnd']);

            }

            if ($data['Type'] != 2)
                $qb->andWhere($qb->expr()->eq('Tran.tranType', $data['Type']));

            if ($data['Action'] != 'All')
                $qb->andWhere($qb->expr()->like('Tran.tranAction', $qb->expr()->literal($data['Action'])));


            $qb->addOrderBy('Tran.tranInsert', 'desc')->addOrderBy('Tran.id','desc');

            $qb = $qb->getQuery();
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
            $req->get('page', 1) /*page number*/,
            10/*limit per page*/
        );


        return $this->render('HelloDiDiDistributorsBundle:Account:DistTransaction.html.twig',
            array(
                'pagination' => $pagination,
                'form' => $form->createView(),
                'Account' => $Account,
                'Entiti' => $Account->getEntiti(),
                'datetype' => $datetype
            ));

    }



    public function  FundingAction($id)
    {

        $em = $this->getDoctrine()->getManager();

        $Account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);

        $formapplay = $this->createFormBuilder()
            ->add('Amount', 'money',
                array(
                'currency'=>$Account->getAccCurrency(),
                'invalid_message'=>'You_entered_an_invalid',
                'label' => 'Amount',
                'translation_domain'=>'transaction'
                ))
            ->add('As', 'choice', array('label' => 'As','translation_domain'=>'transaction',
                'empty_value' => 'select_a_action',
                'preferred_choices' => array('Credit'),
                'choices' => array(
                    0 => 'Debit',
                    1 => 'Credit',
                    2 => 'Debit(commission)'
                )
            ))
            ->add('Description', 'textarea',
                array('label' => 'Description','translation_domain'=>'transaction',
                    'required' => true
                ))
            ->getForm();

        $formupdate = $this->createFormBuilder()
            ->add('Amount', 'money', array(
                'currency'=>$Account->getAccCurrency(),
                'invalid_message'=>'You_entered_an_invalid',
                'label' => 'Amount','translation_domain'=>'transaction',))
            ->add('As', 'choice', array('label' => 'As','translation_domain'=>'transaction',
                'empty_value' => 'select_a_action',
                'choices' =>
                array(
                    1 => 'Increase',
                    0 => 'Decrease'
                )
            ))->getForm();

        return $this->render('HelloDiDiDistributorsBundle:Account:Funding.html.twig',
            array(
                'Entiti' => $Account->getEntiti(),
                'Account' => $Account,
                'formapplay' => $formapplay->createView(),
                'formupdate' => $formupdate->createView(),

            ));
    }

    public function  FundingUpdateBalanceAction(Request $req, $id)
    {
        $balancechecker = $this->get('hello_di_di_distributors.balancechecker');

        $User = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $Account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);

        $formapplay = $this->createFormBuilder()
            ->add('Amount',null,array('label'=>'Amount','translation_domain'=>'transaction'))
            ->add('As', 'choice', array('label'=>'As','translation_domain'=>'transaction',

                'choices'
                => array(
                    0 => 'Debit',
                    1 => 'Credit',
                    2 => 'Debit(commission)'
                )))
            ->add('Description', 'textarea', array('required' => true,'label'=>'Description','translation_domain'=>'transaction'))
            ->getForm();

        if ($req->isMethod('post')) {
            try
            {
            $trandist = new Transaction();
            $formapplay->handleRequest($req);
            $data = $formapplay->getData();

            //objeavt transaction//

            $trandist->setTranDate(new \DateTime('now'));
            $trandist->setTranCurrency($Account->getAccCurrency());
            $trandist->setTranInsert(new \DateTime('now'));
            $trandist->setAccount($Account);
            $trandist->setUser($User);
            $trandist->setTranFees(0);
            $trandist->setTranDescription($data['Description']);
            $trandist->setTranBalance($Account->getAccBalance());


            if (($data['Amount'] > 0)) {

                switch ($data['As']) {
                    case 0:
                        if ($balancechecker->isMoreThanCreditLimit($Account, $data['Amount']))
                        {
                            $alredy=$Account->getAccBalance();
                            $trandist->setTranType(0);
                            $trandist->setTranAmount(-$data['Amount']);
                            $trandist->setTranAction('amdt');
                            $em->persist($trandist);
                            $em->flush();

                          #check#
                if($Account->getAccBalance()+$Account->getAccCreditLimit()<=15000)
                {
                    $this->forward('hello_di_di_notification:NewAction',array('id'=>$Account->getId(),'type'=>121,'value'=>'15000 '.$Account->getAccCurrency()));
                    $this->forward('hello_di_di_notification:NewAction',array('id'=>null,'type'=>121,'value'=>'15000 '.$Account->getAccCurrency().'   ('.$Account->getAccName().')'));
                }


                $this->forward('hello_di_di_notification:NewAction',array('id'=>$Account->getId(),'type'=>23,'value'=>$data['Amount'].$Account->getAccCurrency()));

                            $this->get('session')->getFlashBag()->add('success',
                                $this->get('translator')->trans('Distributor_account_was_changed_from_%alredydist%_to_%currentdist%',
                                    array('alredydist'=>$alredy,'currentdist'=>$Account->getAccBalance()),
                                    'message')
                            );
                        }


                        break;

                    case 1:

                        $trandist->setTranType(1);
                        $trandist->setTranAmount(+$data['Amount']);
                        $trandist->setTranAction('pmt');
                        $alredy=$Account->getAccBalance();
                        $em->persist($trandist);
                        $em->flush();

                        if($Account->getAccBalance()+$Account->getAccCreditLimit()<=15000)
                        {
                            $this->forward('hello_di_di_notification:NewAction',array('id'=>$Account->getId(),'type'=>121,'value'=>'15000 '.$Account->getAccCurrency()));
                            $this->forward('hello_di_di_notification:NewAction',array('id'=>null,'type'=>121,'value'=>'15000 '.$Account->getAccCurrency().'   ('.$Account->getAccName().')'));
                        }


                        $this->forward('hello_di_di_notification:NewAction',array('id'=>$Account->getId(),'type'=>22,'value'=>$data['Amount'].$Account->getAccCurrency()));

                        $this->get('session')->getFlashBag()->add('success',
                            $this->get('translator')->trans('Distributor_account_was_changed_from_%alredydist%_to_%currentdist%',
                                array('alredydist'=>$alredy,'currentdist'=>$Account->getAccBalance()),
                                'message')
                        );


                        break;

                    case 2:

                        if ($balancechecker->isMoreThanCreditLimit($Account, $data['Amount']))
                        {
                            $alredy=$Account->getAccBalance();
                            $trandist->setTranType(0);
                            $trandist->setTranAmount(-$data['Amount']);
                            $trandist->setTranAction('com_pmt');
                            $em->persist($trandist);
                            $em->flush();
                            if($Account->getAccBalance()+$Account->getAccCreditLimit()<=15000)
                            {
                                $this->forward('hello_di_di_notification:NewAction',array('id'=>$Account->getId(),'type'=>121,'value'=>'15000 '.$Account->getAccCurrency()));
                                $this->forward('hello_di_di_notification:NewAction',array('id'=>null,'type'=>121,'value'=>'15000 '.$Account->getAccCurrency().'   ('.$Account->getAccName().')'));
                            }
                            $this->forward('hello_di_di_notification:NewAction',array('id'=>$Account->getId(),'type'=>23,'value'=>$data['Amount'].' '.$Account->getAccCurrency()));


                            $this->get('session')->getFlashBag()->add('success',
                                $this->get('translator')->trans('Distributor_account_was_changed_from_%alredydist%_to_%currentdist%',
                                    array('alredydist'=>$alredy,'currentdist'=>$Account->getAccBalance()),
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

            return $this->redirect($this->generateUrl('MasterDistFunding', array('id' => $id)));


          }
              }

    public function  FundingUpdateCredilimitAction(Request $req, $id)
    {

        $em = $this->getDoctrine()->getManager();
        $balancechecker = $this->get('hello_di_di_distributors.balancechecker');
        $Account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);
        $formupdate = $this->createFormBuilder()
            ->add('Amount', 'text')
            ->add('As', 'choice', array('preferred_choices' => array('Credit'),
                'choices' => array(
                    1 => 'Increase',
                    0 => 'Decrease')
            ))->getForm();

        if ($req->isMethod('POST')) {
    try{
            $formupdate->handleRequest($req);
            $data = $formupdate->getData();
            $alredy=$Account->getAccCreditLimit();

if($data['Amount']>0 )
{
          switch($data['As'] )
          {
              case 0:
                  if ($balancechecker->isAccCreditLimitPlus($Account, $data['Amount'])) {

                      $Account->setAccCreditLimit($Account->getAccCreditLimit() - $data['Amount']);

                      $this->forward('hello_di_di_notification:NewAction',array('id'=>$Account->getId(),'type'=>25,'value'=>$data['Amount'].' '.$Account->getAccCurrency()));

                      $this->get('session')->getFlashBag()->add('success',
                          $this->get('translator')->trans('Distributor_creditlimit_was_changed_from_%alredydist%_to_%currentdist%',
                              array('alredydist'=>$alredy,'currentdist'=>$Account->getAccCreditLimit()),
                              'message')
                      );
                  }
                  break;
              case 1:
                  $Account->setAccCreditLimit($Account->getAccCreditLimit() + $data['Amount']);

                  $this->forward('hello_di_di_notification:NewAction',array('id'=>$Account->getId(),'type'=>24,'value'=>$data['Amount'].' '.$Account->getAccCurrency()));

                  $this->get('session')->getFlashBag()->add('success',
                      $this->get('translator')->trans('Distributor_creditlimit_was_changed_from_%alredydist%_to_%currentdist%',
                          array('alredydist'=>$alredy,'currentdist'=>$Account->getAccCreditLimit()),
                          'message')
                  );
                  break;

          }

            $em->flush();

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
        return $this->redirect($this->generateUrl('MasterDistFunding', array('id' => $id)));
    }

   }

    public function  SaleAction(Request $req, $id)
    {
        $printtype= $req->get('print', null);


        $group = 0;
        $em = $this->getDoctrine()->getManager();
        $qb = array();
        //load first list search
        $Account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);

        $form = $this->createFormBuilder()

            ->add('ItemType', 'choice',
                array('label' => 'ItemType','translation_domain'=>'item' ,'choices' =>
                array(
                    'All' => 'All',
                    'dmtu' => 'Mobile',
                    'clcd' => 'Calling_Card',
                    'epmt' => 'E-payment',
                    'imtu' => 'IMTU',
                )))

            ->add('ItemName', 'entity',
                array('label' => 'Item','translation_domain'=>'item',
                    'empty_data' => '',
                    'empty_value' => 'All',
                    'required' => false,
                    'class' => 'HelloDiDiDistributorsBundle:Item',
                    'property' => 'itemName',
                    'query_builder' => function (EntityRepository $er) use ($Account) {
                        return $er->createQueryBuilder('i')
                            ->innerJoin('i.Prices', 'ip')
                            ->where('ip.priceStatus = 1')
                            ->andwhere('ip.Account = :Acc')->setParameter('Acc',$Account);
                        }
                ))


            ->add('Account', 'entity',
                array('label' => 'Retailer(s)','translation_domain'=>'accounts',
                    'class' => 'HelloDiAccountingBundle:Account',
                    'property' => 'accName',
                    'required' => false,
                    'empty_value' => 'All',
                    'empty_data' => '',
                    'query_builder' => function (EntityRepository $er) use ($Account) {
                        return $er->createQueryBuilder('a')
                            ->where('a.Parent = :ap')
                            ->orderBy('a.accName', 'ASC')
                            ->setParameter('ap', $Account);
                    }
                ))


            ->add('DateStart', 'date', array(
                'format'=>"yyyy/MM/dd",
                'widget'=>'single_text',
                'disabled' => false, 'label' => 'From','translation_domain'=>'transaction','data'=>(new \DateTime('now'))->sub(new \DateInterval('P7D'))))
            ->add('DateEnd', 'date',
                array(
                    'format'=>"yyyy/MM/dd",
                    'widget'=>'single_text',
                    'disabled' => false, 'label' => 'To','translation_domain'=>'transaction','data'=>(new \DateTime('now'))))

            ->add('GroupBy', 'choice', array('translation_domain'=>'transaction',
                'expanded' => true,
                 'multiple'=>true,
                'choices' => array(
                    1 => 'daily_sales_grouped_by_item_and_retailer',
                )))

            ->getForm();


        if ($req->isMethod('POST')) {
try{
            $form->handleRequest($req);
            $data = $form->getData();

            $qb = $em->createQueryBuilder();

            if ($data['GroupBy']) {
                $qb->select('Tr as TR','count(Tr.id) as Quantity');
                $group = 1;
            } else $qb->select('Tr as TR');


            $qb->from('HelloDiAccountingBundle:Transaction', 'Tr')
//                /*for groupBy*/
                ->innerJoin('Tr.Code', 'TrCo')
                ->innerJoin('Tr.Account', 'TrAc')
                ->innerJoin('TrAc.Entiti', 'TrAcEn')
                ->innerJoin('TrCo.Item', 'TrCoIt')
                ->innerJoin('Tr.TaxHistory','TrTh');
                /**/

            if ($data['Account'])
                $qb->where('Tr.Account =:account')->setParameter('account', $data['Account']);
            else
                $qb->where('Tr.Account In (:Acc)')->setParameter('Acc',(count($Account->getChildrens()->toArray())==0)?-1:$Account->getChildrens()->toArray());


                $qb->andWhere($qb->expr()->like('Tr.tranAction', $qb->expr()->literal('sale')));


            if ($data['DateStart'] != '')
                $qb->andwhere('Tr.tranDate >= :DateStart')->setParameter('DateStart', $data['DateStart']);
            if ($data['DateEnd'] != '')
                $qb->andwhere('Tr.tranDate <= :DateEnd')->setParameter('DateEnd', $data['DateEnd']);

            if ($data['ItemName'])
               $qb->andwhere('TrCoIt= :item')->setParameter('item', $data['ItemName']);

            if ($data['ItemType'] != 'All')
                $qb->andwhere($qb->expr()->like('TrCoIt.itemType ', $qb->expr()->literal($data['ItemType'])));

            if ($data['GroupBy'])
                $qb->GroupBy('Tr.tranDate','TrCo.Item','TrAc','Tr.tranAmount','TrTh');
            else
                $qb->addOrderBy('Tr.tranDate', 'desc')->addOrderBy('Tr.id', 'desc');


            $qb = $qb->getQuery();

            $count = count($qb->getResult());

            $qb->setHint('knp_paginator.count', $count);

        }
catch(\Exception $e)
{
    $this->get('session')->getFlashBag()->add('error',
        $this->get('translator')->trans('You_entered_an_invalid',array(),'message'));
}
        }

        if($group==1)
        {
            $pagination = $qb->getResult();
        }
        else
        {
            $paginator = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $qb,
                $req->get('page', 1) /*page number*/,
                10/*limit per page*/
            );
        }

        if($printtype==null)
        {
            return $this->render('HelloDiDiDistributorsBundle:Account:ReportSales.html.twig',array(
                    'pagination' => $pagination,
                    'form' => $form->createView(),
                    'Account' => $Account,
                    'group'=>$group,
                    'Entiti' => $Account->getEntiti()
            ));
        }
        else
        {
            $header= "
                <div style='font-size:14px;float:left;border:1px solid #999;width:7cm;padding:3px'>
                    <b>Distributor Details</b><br/>
                    Account Name: ".$Account->getAccName()."<br/>
                    Account Balance: ".$Account->getAccBalance()."<br/>
                    Account Currency: ".$Account->getAccCurrency()."<br/>
                </div>
                <div style='font-size:14px;float:right;width:8cm;text-align:right'>
                    <b>List of Retailer Revenues</b><hr/>
                    Period: ".$data['DateStart']->format('Y/m/d')." to ".$data['DateEnd']->format('Y/m/d')."
                </div>
                ";

            if($printtype == 0)
            {
                $html = $this->render('HelloDiDiDistributorsBundle:Print:SaleRevenuesPrint.html.twig',array(
                    'pagination' => $qb->getResult()
                ));
            }
            else
            {
                $retailers = $Account->getChildrens();
                $html = $this->render('HelloDiDiDistributorsBundle:Print:SaleStatementPrint.html.twig',array(
                    'pagination' => $qb->getResult(),
                    'retailers' => $retailers
                ));
            }

            return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($html->getContent(),array(
                    'footer-right'=>'Page: [page]/[toPage]',
                    'footer-left'=>'Date: [date]',
                    'header-html'=>$header,
                    'margin-top'=>35,
                    'header-spacing'=>25,
                    'margin-bottom'=>15,
                    'footer-spacing'=>5
                )),
                200,
                array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => 'attachment; filename="Print.pdf"'
                )
            );
        }
    }


    public function GetComAction($id)
    {

        $em=$this->getDoctrine()->getManager();

        $tran=$em->getRepository('HelloDiAccountingBundle:Transaction')->find($id);

        $com=$em->getRepository('HelloDiAccountingBundle:Transaction')->findOneBy(array(
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

    public function ProvTransferAction($id, Request $req)
    {

        $AccountBalance = $this->get('hello_di_di_distributors.balancechecker');

        $em = $this->getDoctrine()->getManager();

        $User = $this->get('security.context')->getToken()->getUser();
        $Account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);

        $countisprov = 0;

        $isprove = $em->createQueryBuilder();

        $isprove->select('Acc')
            ->from('HelloDiAccountingBundle:Account', 'Acc')
            ->Where('Acc.Entiti = :Ent')->setParameter('Ent', $Account->getEntiti())
            ->andWhere('Acc.accType =0')
            ->andWhere('Acc.accCurrency=:Cur')->setParameter('Cur', $Account->getAccCurrency());

        $countisprov = count($isprove->getQuery()->getResult());

        $form = $this->createFormBuilder()
            ->add('Amount', 'money',
                array(
                 'invalid_message'=> 'You-entered_an_invali',
                 'currency'=>$Account->getAccCurrency()
                ,'label'=>'Amount'
                ,'translation_domain'=>'transaction'
                ))
            ->add('Accounts', 'entity', array('label'=>'Account',
                'translation_domain'=>'accounts',
                'empty_value' => 'select_a_account',
                'empty_data' => '',
                'class' => 'HelloDiAccountingBundle:Account',
                'property' => 'NamewithCurrency',
                'required' => true,
                'query_builder' => function (EntityRepository $er) use ($Account) {
                    return $er->createQueryBuilder('Acc')
                        ->Where('Acc.Entiti = :Ent')->setParameter('Ent', $Account->getEntiti())
                        ->andWhere('Acc.accType =0')
                        ->andWhere('Acc.accCurrency=:Cur')->setParameter('Cur', $Account->getAccCurrency());
                }
            ))
            ->add('Description', 'textarea', array('required' => true,'label'=>'Description','translation_domain'=>'transaction'))
            ->add('Communications', 'textarea', array('required' => true,'label'=>'Communications','translation_domain'=>'transaction'))
            ->getForm();

        $tranprov = new Transaction();
        $trandist = new Transaction();

        $tranprov->setTranBookingValue(null);
        $tranprov->setTranDate(new \DateTime('now'));
        $tranprov->setTranInsert(new \DateTime('now'));

        $trandist->setTranBookingValue(null);
        $trandist->setTranDate(new \DateTime('now'));
        $trandist->setTranInsert(new \DateTime('now'));

        $trandist->setTranAction('tran');
        $tranprov->setTranAction('tran');

        if ($req->isMethod('POST')) {

      try
         {
            $form->handleRequest($req);
            $data = $form->getData();

            #transaction for prov#

            $tranprov->setTranAmount(-$data['Amount']);
            $tranprov->setAccount($Account);
            $tranprov->setUser($User);
            $tranprov->setTranDescription($data['Description']);
            $tranprov->setTranFees(0);
            $tranprov->setTranCurrency($Account->getAccCurrency());
            $tranprov->setTranType(0);
            $tranprov->setTranBalance($Account->getAccBalance());
            #0 for debit

            #transaction for dist#
            $trandist->setTranAmount(+$data['Amount']);
            $trandist->setTranType(1); #1 for credit
            $trandist->setAccount($data['Accounts']);
            $trandist->setUser($User);
            $trandist->setTranDescription($data['Communications']);
            $trandist->setTranFees(0);
            $trandist->setTranCurrency($data['Accounts']->getAccCurrency());
            $trandist->setTranBalance($data['Accounts']->getAccBalance());

            $alredydist=$data['Accounts']->getAccBalance();
            $alredyprov=$Account->getAccBalance();
            if ($data['Amount'] > 0) {

                $em->persist($trandist);
                $em->persist($tranprov);
                $em->flush();


          $this->forward('hello_di_di_notification:NewAction',array('id'=>$data['Accounts']->getId(),'type'=>22,'value'=>$data['Amount'].' '.$data['Accounts']->getAccCurrency()));

          if($data['Accounts']->getAccBalance()+$data['Accounts']->getAccCreditLimit()<=15000)
          {
              $this->forward('hello_di_di_notification:NewAction',array('id'=>$data['Accounts']->getId(),'type'=>121,'value'=>'15000 '.$data['Accounts']->getAccCurrency()));
              $this->forward('hello_di_di_notification:NewAction',array('id'=>null,'type'=>121,'value'=>'15000 '.$data['Accounts']->getAccCurrency().'   ('.$data['Accounts']->getAccName().')'));
          }


          if($Account->getAccBalance()<=15000)
                $this->forward('hello_di_di_notification:NewAction',array('id'=>null,'type'=>12,'value'=>'15000 '.$Account->getAccCurrency().'   ('.$Account->getAccName().')'));

                $this->get('session')->getFlashBag()->add('success',
                    $this->get('translator')->trans('Distributor_account_was_changed_from_%alredydist%_to_%currentdist%',
                        array('alredydist'=>$alredydist,'currentdist'=>$data['Accounts']->getAccBalance()),
                        'message')
                );


                $this->get('session')->getFlashBag()->add('success',
                    $this->get('translator')->trans('Provider_account_was_changed_from_%alredyprov%_to_%currentprov%',
                        array('alredyprov'=>$alredyprov,'currentprov'=>$Account->getAccBalance()),
                        'message')
                );

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
        return $this->render('HelloDiDiDistributorsBundle:Account:ProvTranTransfer.html.twig', array(
            'Account' => $Account,
            'User' => $User,
            'Entity' => $Account->getEntiti(),
            'form' => $form->createView(),
            'CountProv' => $countisprov
        ));


    }


    public function  ProvRegisterAction($id, Request $Req)
    {
        $AccountBalance = $this->get('hello_di_di_distributors.balancechecker');
        $em = $this->getDoctrine()->getManager();

        $User = $this->get('security.context')->getToken()->getUser();
        $Account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);

        $tran = new Transaction();

        $form = $this->createFormBuilder()
            ->add('CreditDebit', 'choice', array('translation_domain'=>'transaction',
                'expanded' => true,
                'choices' => array(
                    0 => 'Debit',
                    1 => 'Credit',

                )

            ))
            ->add('Amount', 'money',
                array(
                'currency'=>$Account->getAccCurrency(),
                'invalid_message'=> 'You_entered_an_invali',
                'label'=>'Amount','translation_domain'=>'transaction',
                'required' => true,
                     ))
            ->add('TradeDate', 'date', array(
                 'widget'=>'single_text',
                 'format'=>"yyyy/MM/dd",
                'label'=>'TradeDate',
                'translation_domain'=>'transaction'
                   ))
            ->add('Description', 'textarea', array('required' => true,'label'=>'Description','translation_domain'=>'transaction',))
            ->add('Fees', 'text', array('required' => false,'label'=>'Fees','translation_domain'=>'transaction'))->getForm();

        if ($Req->isMethod('POST')) {


         try
         {
            $form->handleRequest($Req);
            $data = $form->getData();

            $tran->setTranCurrency($Account->getAccCurrency());
            $tran->setUser($User);
            $tran->setAccount($Account);

            $tran->setTranDate($data['TradeDate']);
            $tran->setTranInsert($data['TradeDate']);
            $tran->setTranBalance($Account->getAccBalance());
            $tran->setTranDescription($data['Description']);

            if ($data['Fees'] != '')
                $tran->setTranFees($data['Fees']);
            else
                $tran->setTranFees(0);
                $alredy=$Account->getAccBalance();
            if ($data['Amount'] > 0) {
                switch ($data['CreditDebit']) {
                    case 1:
                        $tran->setTranAction('pmt');
                        $tran->setTranType(1);
                        $tran->setTranAmount(+$data['Amount']);
                        $em->persist($tran);
                        $em->flush();

                        if($Account->getAccBalance()<=15000)
                            $this->forward('hello_di_di_notification:NewAction',array('id'=>null,'type'=>12,'value'=>'15000 '.$Account->getAccCurrency().'   ('.$Account->getAccName().')'));

                        $this->get('session')->getFlashBag()->add('success',
                            $this->get('translator')->trans('Provider_account_was_changed_from_%alredyprov%_to_%currentprov%',
                                array('alredyprov'=>$alredy,'currentprov'=>$Account->getAccBalance()),
                                'message')
                        );



                        break;


                    case 0:
                        $tran->setTranAction('amdt');
                        $tran->setTranType(0);
                        $tran->setTranAmount(-$data['Amount']);
                        $em->persist($tran);
                        $em->flush();

                  if($Account->getAccBalance()<=15000)
                        $this->forward('hello_di_di_notification:NewAction',array('id'=>null,'type'=>12,'value'=>'15000 '.$Account->getAccCurrency().'   ('.$Account->getAccName().')'));

                        $this->get('session')->getFlashBag()->add('success',
                            $this->get('translator')->trans('Provider_account_was_changed_from_%alredyprov%_to_%currentprov%',
                                array('alredyprov'=>$alredy,'currentprov'=>$Account->getAccBalance()),
                                'message')
                        );

                        break;
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
        return $this->render('HelloDiDiDistributorsBundle:Account:ProvTranRegister.html.twig',
            array(
                'form' => $form->createView(),
                'Account' => $Account,
                'User' => $User,
                'Entity' => $Account->getEntiti(),
            ));

    }

    public function PurchasesAction($id, Request $req)
    {
        $em = $this->getDoctrine()->getManager();

        $User = $this->getUser();

        $Account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);

        $qb = array();

        $form = $this->createFormBuilder()
            ->add('DateStart', 'date',
                array(
                    'format'=>'yyyy/MM/dd',
                    'widget'=>'single_text',
                    'data'=>((new \DateTime('now'))->sub(new \DateInterval('P7D'))),
                    'disabled' => false,
                    'required' => false,
                    'label' => 'From',
                     'translation_domain'=>'transaction'
                     ))
            ->add('DateEnd', 'date',
                array(
                    'format'=>'yyyy/MM/dd',
                    'data'=>(new \DateTime('now')),
                    'widget'=>'single_text',
                    'disabled' => false, 'required' => false, 'label' => 'To','translation_domain'=>'transaction'))
            ->add('ItemType', 'choice',
                array('label' => 'ItemType','translation_domain'=>'item','choices' =>
                array(
                    'All' => 'All',
                    'dmtu' => 'Mobile',
                    'clcd' => 'Calling_Card',
                    'epmt' => 'E-payment',
                    'imtu' => 'IMTU',
                )))

            ->add('ItemName', 'entity',
                array('label' => 'Item','translation_domain'=>'item',
                    'empty_data' => '',
                    'empty_value' => 'All',
                    'required' => false,
                    'class' => 'HelloDiDiDistributorsBundle:Item',
                    'property' => 'itemName',
                    'query_builder' => function (EntityRepository $er) use ($Account) {
                        return $er->createQueryBuilder('i')
                            ->innerJoin('i.Prices', 'ip')
                            ->where('ip.priceStatus = 1')
                            ->andwhere('ip.Account = :Acc')->setParameter('Acc', $Account);

                    }
                ))->getForm();

        if ($req->isMethod('POST')) {
            try
            {
            $form->handleRequest($req);
            $data = $form->getData();

            $qb = $em->createQueryBuilder();
        $qb->select('Tr as TR, count(Tr.Code) as Quantity')
                ->from('HelloDiAccountingBundle:Transaction','Tr')
                ->innerJoin('Tr.Account', 'TrAcc')
                ->innerJoin('Tr.Code', 'TrCo')
                ->innerJoin('TrCo.Item', 'TrCoIt')
                ->innerJoin('Tr.TaxHistory', 'TrTh')

                ->where('Tr.Account = :Acc')->setParameter('Acc',$Account)
                ->andwhere($qb->expr()->like('Tr.tranAction', $qb->expr()->literal('com')));
            if ($data['DateStart'] != '')
                $qb->andWhere('Tr.tranDate >= :DateStart')->setParameter('DateStart', $data['DateStart']);
            if ($data['DateEnd'] != '')
                $qb->andWhere('Tr.tranDate <= :DateEnd')->setParameter('DateEnd', $data['DateEnd']);
            if ($data['ItemName'])
                $qb->andWhere('TrCoIt = :Item')->setParameter('Item',$data['ItemName']);
            if ($data['ItemType'] != 'All')
                $qb->andWhere($qb->expr()->like('TrCoIt.itemType', $qb->expr()->literal($data['ItemType'])));


            $qb->groupBy('TrCoIt')->addGroupBy('Tr.BuyingPrice')->addGroupBy('TrTh');

            $qb->addOrderBy('Tr.tranInsert', 'desc');

            $qb = $qb->getQuery();
            $qb = $qb->getResult();

            }
            catch(\Exception $e){
                $this->get('session')->getFlashBag()->add('error',
                    $this->get('translator')->trans('You_entered_an_invalid',array(),'message'));
            }
        }


        $print= $req->get('print', 0);

        if($print == 0)
        {
            return $this->render('HelloDiDiDistributorsBundle:Account:Purchases.html.twig', array(
                'pagination' => $qb,
                'Account' => $Account,
                'User' => $User,
                'Entity' => $Account->getEntiti(),
                'form' => $form->createView()
            ));
        }
        else
        {
            $html = $this->render('HelloDiDiDistributorsBundle:Print:PurchasesPrint.html.twig', array(
                'pagination' => $qb
            ));
            return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($html->getContent(),array(
                    'header-html'=>'
                    <div style="font-size:14px;float:left;border:1px solid #999;width:7cm;padding:3px">
                        <b>Distributor Details</b><br/>
                        Account Name: '.$Account->getAccName().'<br/>
                        Account Balance: '.$Account->getAccBalance().'<br/>
                        Account Currency: '.$Account->getAccCurrency().'<br/>
                    </div>
                    <div style="float: right;font-weight: bold;width: 8cm;border-bottom: 2px solid black;text-align:right">
                        Purchases Print
                    </div>
                    ',
                    'margin-top'=>35,
                    'header-spacing'=>25,
                    'footer-right'=>'Page: [page]/[toPage]',
                    'footer-left'=>'Date: [date]'
                )),
                200,
                array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => 'attachment; filename="Purchases.pdf"'
                )
            );
        }
    }

    public function ShowMyAccountDistAction(Request $request)
    {


        $em = $this->getDoctrine()->getManager();
        $query = $em->getRepository('HelloDiAccountingBundle:Account')->findBy(array('accType' => 0));
        return $this->render('HelloDiDiDistributorsBundle:Account:ShowMyAccountDist.html.twig', array
        ('pagination' => $query));


    }

////////////////////

    public function ManageDistChildrenAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();

        $Account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);
        $query = $Account->getChildrens();


        return $this->render('HelloDiDiDistributorsBundle:Account:ManageDistChildren.html.twig',
            array(
                'pagination' => $query,
                'Account' => $Account));

    }


    public function ManageDistUserAction(Request $request, $id)
    {


        $em = $this->getDoctrine()->getManager();


        $Account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);

        $result = $Account->getUsers();

        return $this->render('HelloDiDiDistributorsBundle:Account:ManageDistUser.html.twig',

            array(
                'pagination' =>$result,
                'Account' => $Account
            ));

    }

    public function ManageDistSettingsAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();
        $Account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);
        $form_edit = $this->createForm(new EditDistType(), $Account);


        if ($request->isMethod('POST')) {
            $form_edit->handleRequest($request);
            if ($form_edit->isValid()) {
                $em->flush();

                $this->forward('hello_di_di_notification:NewAction',array('id'=>$Account->getId(),'type'=>26));

                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
            }

        }


        return $this->render('HelloDiDiDistributorsBundle:Account:ManageDistSettings.html.twig',
            array('form_edit' => $form_edit->createView(),
                'Account' => $Account
            ));

    }

//
//    public function ManageDistInfoEditAction(Request $request)
//    {
//        $id = $request->get('id');
//        $em = $this->getDoctrine()->getManager();
//        $Account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);
//        $edit_form = $this->createForm(new AccountDistMasterType(), $Account);
//
//        if ($request->isMethod('POST')) {
//
//            $edit_form->handleRequest($request);
//            if ($edit_form->isValid()) {
//
//                $em->flush();
//
//                return $this->forward("HelloDiDiDistributorsBundle:Account:ManageDistInfo");
//
//
//            }
//        }
//        return $this->render('HelloDiDiDistributorsBundle:Account:ManageDistInfo.html.twig', array('edit_form' => $edit_form->createView(), 'Account' => $Account));
//    }

    //items prov
    public function ManageItemsProvAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);
        $prices = $account->getPrices();

        return $this->render('HelloDiDiDistributorsBundle:Account:ManageItemsProv.html.twig', array(
            'Account' => $account,
            'prices' => $prices
        ));
    }

    public function AddItemProvAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);

        if (!$account) {
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>$this->get('translator')->trans('Account',array(),'accounts')),'message'));
        }

        $price = new Price();
        $price->setPriceCurrency($account->getAccCurrency());
        $price->setPriceStatus(1);
        $price->setIsFavourite(false);
        $price->setAccount($account);

        $form = $this->createFormBuilder($price)
            ->add('Item', 'entity', array(
                'class' => 'HelloDiDiDistributorsBundle:Item',
                'property' => 'itemName',
                'query_builder' => function (EntityRepository $er) use ($account) {
                    return $er->createQueryBuilder('u')
                        ->where('u.id NOT IN (
                            SELECT DISTINCT ii.id
                            FROM HelloDiDiDistributorsBundle:Item ii
                            JOIN ii.Prices pp
                            JOIN pp.Account aa
                            WHERE aa = :aaid
                        )')
                        ->setParameter('aaid', $account);
                },
                'label' => 'Item','translation_domain' => 'item'
            ))
            ->add('price','number',array('label' => 'Price','translation_domain' => 'price'))
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
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->forward('HelloDiDiDistributorsBundle:Account:ManageItemsProv', array(
                    'id' => $price->getAccount()->getId()
                ));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Account:AddItemProv.html.twig', array(
            'Account' => $account,
            'form' => $form->createView()
        ));
    }

    public function EditItemProvAction(Request $request, $itemid)
    {
        $em = $this->getDoctrine()->getManager();
        $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->find($itemid);

        if (!$price) {
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>$this->get('translator')->trans('Item',array(),'item')),'message'));
        }

        $oldprice = $price->getPrice();

        $form = $this->createForm(new PriceEditType(null), $price);

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
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->forward('HelloDiDiDistributorsBundle:Account:ManageItemsProv', array(
                    'id' => $price->getAccount()->getId()
                ));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Account:EditItemProv.html.twig', array(
            'Account' => $price->getAccount(),
            'price' => $price,
            'form' => $form->createView()
        ));
    }

    //items dist
    public function ManageItemsDistAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);
        $prices = $account->getPrices();

        return $this->render('HelloDiDiDistributorsBundle:Account:ManageItemsDist.html.twig', array(
            'Account' => $account,
            'prices' => $prices
        ));
    }

    public function AddItemDistAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);
        $country = $account->getEntiti()->getCountry();
        if (!$account) {
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>$this->get('translator')->trans('Account',array(),'accounts')),'message'));
        }

        $price = new Price();
        $price->setPriceCurrency($account->getAccCurrency());
        $price->setPriceStatus(1);
        $price->setIsFavourite(false);
        $price->setAccount($account);

        $form = $this->createFormBuilder($price)
            ->add('Item', 'entity', array(
                'class' => 'HelloDiDiDistributorsBundle:Item',
                'property' => 'itemName',
                'query_builder' => function (EntityRepository $er) use ($account) {
                    return $er->createQueryBuilder('u')
                        ->where('u.id NOT IN (
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
                            WHERE aaa.accType = 1
                        )')
                        ->setParameter('aaid', $account);
                },
                'label' => 'Item','translation_domain' => 'item'
            ))
            ->add('price','number',array('label' => 'Price','translation_domain' => 'price'))
            ->add('tax', 'entity', array(
                'class' => 'HelloDiDiDistributorsBundle:Tax',
                'property' => 'tax',
                'query_builder' => function(EntityRepository $er) use ($country) {
                    return $er->createQueryBuilder('u')
                        ->where('u.id = 1 or u.Country = :country')->setParameter('country',$country)
                        ->orderBy('u.id', 'DESC');
                },
                'label' => 'Tax','translation_domain' => 'vat'
            ))
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            $denomination = $em->getRepository("HelloDiDiDistributorsBundle:Denomination")->findOneBy(array(
                    "Item" => $price->getItem(),
                    "currency" => $price->getAccount()->getAccCurrency()
                ));

            if($price->getItem()->getItemCurrency() != $price->getAccount()->getAccCurrency() && $denomination == null)
                $form->get('Item')->addError(new FormError("Add denomination to this item for your account currency."));

            if ($form->isValid()) {
                $em->persist($price);

                $pricehistory = new PriceHistory();
                $pricehistory->setDate(new \DateTime('now'));
                $pricehistory->setPrice($price->getPrice());
                $pricehistory->setPrices($price);
                $em->persist($pricehistory);

                $em->flush();
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->forward('HelloDiDiDistributorsBundle:Account:ManageItemsDist', array(
                    'id' => $price->getAccount()->getId()
                ));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Account:AddItemDist.html.twig', array(
            'Account' => $account,
            'form' => $form->createView()
        ));
    }

    public function EditItemDistAction(Request $request, $id, $itemid)
    {
        $em = $this->getDoctrine()->getManager();
        $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->find($itemid);

        if (!$price) {
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>$this->get('translator')->trans('Item',array(),'item')),'message'));
        }

        $oldprice = $price->getPrice();

        $form = $this->createForm(new PriceEditType($price->getAccount()->getEntiti()->getCountry()), $price);

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
                if ($price->getPriceStatus() == 0) {
                    $RetAccs = $em->getRepository('HelloDiAccountingBundle:Account')->find($id)->getChildrens()->toArray();
                    if(count($RetAccs)>0)
                    {
                        $em->createQueryBuilder()
                            ->update('HelloDiDiDistributorsBundle:Price', 'pr')
                            ->where('pr.Account IN (:retaccs)')->setParameter('retaccs', $RetAccs)
                            ->andWhere('pr.Item = :item')->setParameter('item', $price->getItem())
                            ->set("pr.priceStatus", 0)
                            ->getQuery()
                            ->execute();
                    }
                }
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->forward('HelloDiDiDistributorsBundle:Account:ManageItemsDist', array(
                    'id' => $price->getAccount()->getId()
                ));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Account:EditItemDist.html.twig', array(
            'Account' => $price->getAccount(),
            'price' => $price,
            'form' => $form->createView()
        ));
    }

    //Inputs prov
    public function ManageInputsProvAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);

        $qb = $em->createQueryBuilder()
            ->select('input')
            ->from('HelloDiDiDistributorsBundle:Input', 'input')
            ->innerJoin('input.Account', 'a')
            ->where('a = :aaid')
            ->setParameter('aaid', $account);

        $form = $this->createFormBuilder()
            ->add('From', 'date', array('required' => false, 'widget' => 'single_text', 'format' => 'yyyy/MM/dd',
                'label' => 'FromInsertDate','translation_domain' => 'code'))
            ->add('To', 'date', array('required' => false, 'widget' => 'single_text', 'format' => 'yyyy/MM/dd',
                'label' => 'ToInsertDate','translation_domain' => 'code'))
            ->add('item', 'entity', array(
                'required' => false,
                'empty_value' => 'All',
                'class' => 'HelloDiDiDistributorsBundle:Item',
                'property' => 'itemName',
                'query_builder' => function (EntityRepository $er) use ($account) {
                    return $er->createQueryBuilder('i')
                        ->innerJoin('i.Prices', 'p')
                        ->innerJoin('p.Account', 'a')
                        ->where('a = :aaid')
                        ->setParameter('aaid', $account);
                },
                'label' => 'Item','translation_domain' => 'item'
            ))
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $data = $form->getData();

            $qb->join('input.Item', 'item');

            if ($data['item'] != null)
                $qb = $qb->andWhere($qb->expr()->eq('item', intval($data['item']->getId())));

            if ($data['From'] != "")
                $qb = $qb->andWhere("input.dateInsert >= :fromdate")->setParameter('fromdate', $data['From']);

            if ($data['To'] != "")
                $qb = $qb->andWhere("input.dateInsert <= :todate")->setParameter('todate', $data['To']);

        }

        $inputs = $qb->getQuery()->getResult();

        return $this->render('HelloDiDiDistributorsBundle:Account:ManageInputsProv.html.twig', array(
            'form' => $form->createView(),
            'Account' => $account,
            'inputs' => $inputs
        ));
    }

    public function CalcPriceAction($account, $item, $count)
    {
        $em = $this->getDoctrine()->getManager();
        $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Account' => $account, 'Item' => $item));
        if ($price)
            return new Response($price->getPrice() * $count);
        else
            return new Response('--');
    }

    public function UploadInputProvAction($id, $itemid)
    {
        $em = $this->getDoctrine()->getManager();
        $Account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);
        $Item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($itemid);

        $form = $this->createFormBuilder()
            ->add('File', 'file',array('label' => 'File','translation_domain' => 'code'))
            ->add('Batch', 'text', array('required' => false,'label' => 'Batch','translation_domain' => 'code'))
            ->add('ProductionDate', 'date', array('widget' => 'single_text', 'format' => 'yyyy/MM/dd', 'data' => new \DateTime('now'),
                'label' => 'DateProduction','translation_domain' => 'code'))
            ->add('ExpireDate', 'date', array('widget' => 'single_text', 'format' => 'yyyy/MM/dd', 'data' => new \DateTime('now'),
                'label' => 'DateExpiry','translation_domain' => 'code'))
            ->add('delimiter', 'choice', array('choices' => array(';' => ';', ',' => ',', ' ' => 'Space', '-' => '-'),
                'label' => 'Delimiter','translation_domain' => 'code'))
            ->add('SerialNumber', 'text', array('data' => '1','label' => 'ColumnNumSN','translation_domain' => 'code'))
            ->add('PinCode', 'text', array('data' => '4','label' => 'ColumnNumPIN','translation_domain' => 'code'))
            ->getForm();

        return $this->render('HelloDiDiDistributorsBundle:Account:UploadInputProv.html.twig', array(
            'item' => $Item,
            'Account' => $Account,
            'form' => $form->createView()
        ));
    }

    public function UploadInputProvSubmitAction(Request $request,$id, $itemid)
    {
        $em = $this->getDoctrine()->getManager();

        $Account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);
        $Item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($itemid);

        $form = $this->createFormBuilder()
            ->add('File', 'file',array('label' => 'File','translation_domain' => 'code'))
            ->add('Batch', 'text', array('required' => false,'label' => 'Batch','translation_domain' => 'code'))
            ->add('ProductionDate', 'date', array('widget' => 'single_text', 'format' => 'yyyy/MM/dd', 'data' => new \DateTime('now'),
                'label' => 'DateProduction','translation_domain' => 'code'))
            ->add('ExpireDate', 'date', array('widget' => 'single_text', 'format' => 'yyyy/MM/dd', 'data' => new \DateTime('now'),
                'label' => 'DateExpiry','translation_domain' => 'code'))
            ->add('delimiter', 'choice', array('choices' => array(';' => ';', ',' => ',', ' ' => 'Space', '-' => '-'),
                'label' => 'Delimiter','translation_domain' => 'code'))
            ->add('SerialNumber', 'text', array('data' => '1','label' => 'ColumnNumSN','translation_domain' => 'code'))
            ->add('PinCode', 'text', array('data' => '4','label' => 'ColumnNumPIN','translation_domain' => 'code'))
            ->getForm();

        $form->handleRequest($request);
        $data = $form->getData();

        $haserror = false;
        if (!is_numeric($data['Batch']))
        {
            $haserror = true;
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('BatchNotValid',array(),'message'));
        }
        if (!is_numeric($data['SerialNumber']))
        {
            $haserror = true;
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('SNNotValid',array(),'message'));
        }
        if (!is_numeric($data['PinCode']))
        {
            $haserror = true;
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('PINNotValid',array(),'message'));
        }

        if (!$haserror) {
            $em = $this->getDoctrine()->getManager();

            $input = new Input();
            $input->setFile($data['File']);
            $input->upload();
            $input->setBatch($data['Batch']);
            $input->setItem($Item);
            $input->setDateProduction($data['ProductionDate']);
            $input->setDateExpiry($data['ExpireDate']);

            $fileName = $input->getFileName();
            $inputfind = $em->getRepository('HelloDiDiDistributorsBundle:Input')->findOneBy(array('fileName' => $fileName));
//          $f= fopen("d:\\a.txt","w+");
            if (!$inputfind) {
                try {
                    $file = fopen($input->getAbsolutePath(), 'r+');

                    if ($line = fgets($file)) {
                        $ok = true;
                        $count = 1;
                        while ($line = fgets($file)) {
                            $count++;
                            $lineArray = explode($data['delimiter'], $line);
//                        fwrite($f,$count.','.$lineArray[$data['SerialNumber'] - 1].'\n');
                            $codefind = $em->getRepository('HelloDiDiDistributorsBundle:Code')->findOneBy(
                                array('serialNumber' => trim($lineArray[$data['SerialNumber'] - 1]))
                            );
                            if ($codefind) {
                                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('CodesAlreadyExist',array(),'message'));
                                $ok = false;
                                break;
                            }
                        }
                        if ($ok) {
                            $request->getSession()->set('upload_Name', $input->getFileName());
                            $request->getSession()->set('upload_Itemid', $input->getItem()->getId());
                            $request->getSession()->set('upload_Batch', $data['Batch']);
                            $request->getSession()->set('upload_Production', $data['ProductionDate']);
                            $request->getSession()->set('upload_Expiry', $data['ExpireDate']);
                            $request->getSession()->set('upload_delimiter', $data['delimiter']);
                            $request->getSession()->set('upload_SerialNumber', $data['SerialNumber']);
                            $request->getSession()->set('upload_PinCode', $data['PinCode']);
                            $request->getSession()->set('upload_accountid', $id);

                            return $this->render(
                                'HelloDiDiDistributorsBundle:Account:UploadInputProvSubmit.html.twig',
                                array(
                                    'Account' => $Account,
                                    'count' => $count,
                                    'input' => $input
                                )
                            );
                        }
                    } else {
                        $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('FileEmpty',array(),'message'));
                    }
                } catch (\Exception $ex) {
                    $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('ErrorReadingFile',array(),'message'));
                }
            } else {
                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('FileAlreadyExist',array(),'message'));
            }
        }
        return $this->forward('HelloDiDiDistributorsBundle:Account:UploadInputProv', array(
            'id' => $id,
            'itemid' => $itemid
        ));
    }

    public function UploadInputProvSubmitAcceptedAction(Request $request)
    {
        $filename = $request->getSession()->get('upload_Name');
        $itemid = $request->getSession()->get('upload_Itemid');
        $batch = $request->getSession()->get('upload_Batch');
        $production = $request->getSession()->get('upload_Production');
        $expiry = $request->getSession()->get('upload_Expiry');
        $delimiter = $request->getSession()->get('upload_delimiter');
        $SerialNumber = $request->getSession()->get('upload_SerialNumber');
        $PinCode = $request->getSession()->get('upload_PinCode');
        $accountid = $request->getSession()->get('upload_accountid');

        $em = $this->getDoctrine()->getManager();
        $Account = $em->getRepository('HelloDiAccountingBundle:Account')->find($accountid);
        $Item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($itemid);

        $user = $this->get('security.context')->getToken()->getUser();

        try {
            $input = new Input();
            $input->setFileName($filename);
            $input->setItem($Item);
            $input->setBatch($batch);
            $input->setDateProduction($production);
            $input->setDateExpiry($expiry);
            $input->setDateInsert(new \DateTime('now'));
            $input->setAccount($Account);
            $input->setUser($user);
            $em->persist($input);
    //        $f= fopen("d:\\b.txt","w+");
            $file = fopen($input->getAbsolutePath(), 'r+');
    //        $count = 0;
            while ($line = fgets($file)) {
    //            $count++;
                $lineArray = explode($delimiter, $line);
    //            fwrite($f,$count.','.$lineArray[$SerialNumber - 1].'\n');
                $code = new Code();
                $code->setSerialNumber(trim($lineArray[$SerialNumber - 1]));
                $code->setPin(trim($lineArray[$PinCode - 1]));
                $code->setStatus(1);
                $code->setItem($input->getItem());
                $code->setInput($input);
                $em->persist($code);

                $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Item'=>$Item,'Account'=>$Account))->getPrice();

                $transaction = new Transaction();
                $transaction->setCode($code);
                $transaction->setAccount($Account);
                $transaction->setTranAmount($price);
                $transaction->setUser($user);
                $transaction->setTranDate(new \DateTime('now'));
                $transaction->setTranInsert(new \DateTime('now'));
                $transaction->setTranAction('add');
                $transaction->setTranCurrency($Account->getAccCurrency());
                $transaction->setTranFees(0);
                $transaction->setTranType(1);
                $transaction->setTranBookingValue(null);
                $transaction->setTranBalance($Account->getAccBalance());
                $transaction->setTranDescription('add new code with serial '.$code->getSerialNumber().' to system.');
                $em->persist($transaction);

            }

            $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
            $em->flush();
        }catch (\Exception $e)
        {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('ErrorReadingFile',array(),'message'));
        }

        return $this->forward('HelloDiDiDistributorsBundle:Account:UploadInputProvSubmitCanceled');
    }

    public function UploadInputProvSubmitCanceledAction(Request $request)
    {
        $accountid = $request->getSession()->get('upload_accountid');
        $itemid = $request->getSession()->get('upload_Itemid');

        $request->getSession()->remove('upload_Name');
        $request->getSession()->remove('upload_Itemid');
        $request->getSession()->remove('upload_Batch');
        $request->getSession()->remove('upload_Production');
        $request->getSession()->remove('upload_Expiry');
        $request->getSession()->remove('upload_delimiter');
        $request->getSession()->remove('upload_SerialNumber');
        $request->getSession()->remove('upload_PinCode');
        $request->getSession()->remove('upload_accountid');

        return $this->redirect($this->generateUrl('ManageInputsProv', array(
            'id' => $accountid,
            'itemid' => $itemid
        )));
    }

    // kamal Prov Start


    public function MasterProvTransactionAction(Request $request, $id)
    {

        $datetype = 0;
        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('knp_paginator');
        $form = $this->createFormBuilder()
            ->add('TypeDate', 'choice', array('translation_domain'=>'transaction',
                'expanded' => true,
                'choices' => array(
                    0 => 'TradeDate',
                    1 => 'BookingDate',
                )))
            ->add('FromDate', 'date', array(
                'widget'=>'single_text',
                'format'=>"yyyy/MM/dd",
                'disabled' => false, 'required' => false,'label'=>'From','translation_domain'=>'transaction'))
            ->add('ToDate', 'date', array(
                'widget'=>'single_text',
                'format'=>"yyyy/MM/dd",
                'disabled' => false, 'required' => false,'label'=>'To','translation_domain'=>'transaction'))

            ->add('type', 'choice', array('label' => 'Type','translation_domain'=>'transaction',
                'choices' => array(
                    2 => 'All',
                    1 => 'Credit',
                    0 => 'Debit'
                )))

            ->add('Action', 'choice', array('label' => 'Action','translation_domain'=>'transaction',
                'choices' => array(
                    'All' => 'All',
                    'add' => 'add_new_codes_to_system',
                    'pmt' => 'credit_provider,s_account',
                    'amdt' => 'an_amount_is_credited_to_correct_the_price_of_a_code',
                    'amdt' => 'an_amount_is_debited_to_correct_the_price_of_a_code',
                    'rmv' => 'remove_codes_from_to_system',
                    'amdt' => 'debit_provider,s_account',
                    'tran' => 'transfer_credit_from_provider,s_account_to_a_distributor,s_account',

                )))->getForm();

        $Account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);

        $qb = array();

        if ($request->isMethod('post')) {
try{
            $form->handleRequest($request);
            $data = $form->getData();
            $qb = $em->createQueryBuilder();

            $qb->select('Tr')
                ->from('HelloDiAccountingBundle:Transaction', 'Tr')
                ->where('Tr.Account = :Acc')->setParameter('Acc', $Account);
            if ($data['TypeDate'] == 0) {
                if ($data['FromDate'] != '')
                    $qb->andwhere('Tr.tranDate >= :transdateFrom')->setParameter('transdateFrom', $data['FromDate']);
                if ($data['ToDate'] != '')
                    $qb->andwhere('Tr.tranDate <= :transdateTo')->setParameter('transdateTo', $data['ToDate']);
            } elseif ($data['TypeDate'] == 1) {
                $datetype = 1;
                if ($data['FromDate'] != '')
                    $qb->andwhere('Tr.tranInsert >= :transdateFrom')->setParameter('transdateFrom', $data['FromDate']);
                if ($data['ToDate'] != '')
                    $qb->andwhere('Tr.tranInsert <= :transdateTo')->setParameter('transdateTo', $data['ToDate']);
            }

            if ($data['type'] != 2)
                $qb->andWhere($qb->expr()->eq('Tr.tranType', $data['type']));


            if ($data['Action'] != 'All')
                $qb->andWhere($qb->expr()->like('Tr.tranAction', $qb->expr()->literal($data['Action'])));

            $qb->addOrderBy('Tr.tranInsert', 'desc')->addOrderBy('Tr.id','desc');

            $qb = $qb->getQuery();

            $count = count($qb->getResult());
            $qb->setHint('knp_paginator.count', $count);
}
catch(\Exception $e)
{
    $this->get('session')->getFlashBag()->add('error',
        $this->get('translator')->trans('You_entered_an_invalid',array(),'message'));
}
        }



        $pagination = $paginator->paginate(
            $qb,
            $request->get('page', 1), /*page number*/
            10/*limit per page*/
        );


        return $this->render('HelloDiDiDistributorsBundle:Account:ProvTransactionMaster.html.twig',
            array(
                'Trans' => $pagination,
                'Account' => $Account,
                'form' => $form->createView(),
                'datetype' => $datetype
            ));

    }

#kazem alan



    public function MasterProvTransactionDeleteAction($tranid)
    {
        $em = $this->getDoctrine()->getManager();


        $tran = $em->getRepository('HelloDiAccountingBundle:Transaction')->find($tranid);

        $em->remove($tran);
        $em->flush();
        return $this->redirect($this->getRequest()->headers->get('referer'));


    }

#end alan


    public function MasterProvRemovedAction(Request $request, $id)
    {


        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);
        $searchForm = $this->createFormBuilder()
            ->add('FromDate', 'date', array('required' => false, 'format' => 'yyyy/MM/dd', 'widget' => 'single_text',
                'label' => 'From','translation_domain' => 'transaction'))
            ->add('ToDate', 'date', array('required' => false, 'format' => 'yyyy/MM/dd', 'widget' => 'single_text',
                'label' => 'To','translation_domain' => 'transaction'))
            ->add('item', 'entity', array(
                'required' => false,
                'empty_value' => 'All',
                'class' => 'HelloDiDiDistributorsBundle:Item',
                'property' => 'itemName',
                'query_builder' => function (EntityRepository $er) use ($account) {
                    return $er->createQueryBuilder('i')
                        ->innerJoin('i.Prices', 'p')
                        ->innerJoin('p.Account', 'a')
                        ->where('a = :aaid')
                        ->setParameter('aaid', $account);
                },
                'label' => 'Item','translation_domain' => 'item'
            ))
            ->getForm();

        $qb = $em->createQueryBuilder()
            ->select('trans')
            ->from('HelloDiAccountingBundle:Transaction', 'trans')
            ->innerJoin('trans.Code', 'code')
            ->innerJoin('code.Input', 'input')
            ->innerJoin('input.Account', 'acc')
            ->where('trans.tranAction =:check')
            ->setParameter('check', 'rmv')
            ->andwhere('acc = :check2')
            ->setParameter('check2', $account);


        if ($request->isMethod('POST')) {

            $searchForm->handleRequest($request);
            $data = $searchForm->getData();

            if ($data['FromDate'] != "")
                $qb = $qb->andWhere("trans.tranDate >= :transdateFrom")->setParameter('transdateFrom', $data['FromDate']);

            if ($data['ToDate'] != "")
                $qb = $qb->andWhere("trans.tranDate <= :transdateTo")->setParameter('transdateTo', $data['ToDate']);

            if ($data['item'] != "")
                $qb = $qb->andWhere('code.Item = :item')->setParameter('item', $data['item']);

        }
        $qb = $qb->getQuery();
        $accProv = $qb->getResult();
        $paginator = $this->get('knp_paginator');

        $accProv = $paginator->paginate(
            $accProv,
            $this->get('request')->query->get('page', 1) /*page number*/,
            20/*limit per page*/
        );
        return $this->render('HelloDiDiDistributorsBundle:Account:MasterProvRemoved.html.twig', array('id' => $id, 'Account' => $account, 'accProv' => $accProv, 'form' => $searchForm->createView()));
    }

    // kamal Prov End


    #start kazem


    public function DistUserAddAction(Request $req, $id)
    {


        $user = new User();
        $em = $this->getDoctrine()->getManager();

        $Account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);

        $form = $this->createForm(new NewUserType('HelloDiDiDistributorsBundle\Entity\User', 0), $user, array('cascade_validation' => true));


        if ($req->isMethod('POST')) {
            $form->handleRequest($req);
            if ($form->isValid()) {
                $user->setEntiti($Account->getEntiti());
                $user->setAccount($Account);
                $user->setEnabled(1);
                $em->persist($user);
                $em->flush();

                $this->forward('hello_di_di_notification:NewAction',array('id'=>$Account->getId(),'type'=>21,'value'=>$user->getUsername()));

                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl('ManageDistUser', array('id' => $Account->getId())));

            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Account:DistUserAdd.html.twig', array(
                'Entiti' => $Account->getEntiti(),
                'Account' => $Account,
                'form' => $form->createView(),
            )
        );
    }


    public function DistUsereditAction(Request $req, $userid)
    {

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('HelloDiDiDistributorsBundle:User')->find($userid);

        $form = $this->createForm(new NewUserType('HelloDiDiDistributorsBundle\Entity\User', 0), $user, array('cascade_validation' => true));

        if ($req->isMethod('POST')) {
            $form->handleRequest($req);
            if ($form->isValid()) {
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));

            }

     }

         return $this->render('HelloDiDiDistributorsBundle:Account:DistUserEdit.html.twig', array(
            'Account' => $user->getAccount(),
            'Entiti' => $user->getEntiti(),
            'User' => $user,
            'form' => $form->createView()));
    }


    public function  MasterProvEntitiAction($id)
    {

        $em = $this->getDoctrine()->getManager();

        $Account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);

        $Entiti = $Account->getEntiti();

        return $this->render('HelloDiDiDistributorsBundle:Account:ManageProvEntiti.html.twig', array(
            'Account' => $Account,
            'entiti' => $Entiti
        ));

    }

    public function  MasterDistEntitiAction($id)
    {

        $em = $this->getDoctrine()->getManager();

        $Account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);

        $Entiti = $Account->getEntiti();

        return $this->render('HelloDiDiDistributorsBundle:Account:ManageDistEntiti.html.twig', array(
            'Account' => $Account,
            'entiti' => $Entiti
        ));

    }


    public function  LoadActionProvAction(Request $req)
    {
        $id = $req->get('id', 0);
        $value = '';
        $value .= '<option value="All">' .
            $this->get('translator')->trans('All',[],'transaction')
            . '</option>';

        switch ($id) {
            case 0:

                $value .= '<option value="rmv">' .
                 $this->get('translator')->trans('remove_codes_from_the_system',[],'transaction')
                    .'</option>';

                $value .= '<option value="amdt">' .
                 $this->get('translator')->trans('debit_provider,s_account',[],'transaction')
                    . '</option>';

                $value .= '<option value="amdt">' .
                     $this->get('translator')->trans('an_amount_is_debited_to_correct_the_price_of_a_code',[],'transaction')
                    . '</option>';

                $value .= '<option value="tran">' .
                     $this->get('translator')->trans('transfer_credit_from_provider,s_account_to_a_distributor,s_account',[],'transaction')
                    . '</option>';

                break;

            case 1:

                $value .= '<option value="add">' .
                $this->get('translator')->trans('add_new_codes_to_system',[],'transaction')
                    . '</option>';

                $value .= '<option value="pmt">' .
                    $this->get('translator')->trans('credit_provider,s_account',[],'transaction')
                    . '</option>';

                $value .= '<option value="amdt">' .
                    $this->get('translator')->trans('an_amount_is_credited_to_correct_the_price_of_a_code',[],'transaction')
                    . '</option>';

                break;

            case 2:
                $value .= '<option value="rmv">' .
                    $this->get('translator')->trans('remove_codes_from_the_system',[],'transaction')
                    .'</option>';

                $value .= '<option value="amdt">' .
                    $this->get('translator')->trans('debit_provider,s_account',[],'transaction')
                    . '</option>';

                $value .= '<option value="amdt">' .
                    $this->get('translator')->trans('an_amount_is_debited_to_correct_the_price_of_a_code',[],'transaction')
                    . '</option>';

                $value .= '<option value="tran">' .
                    $this->get('translator')->trans('transfer_credit_from_provider,s_account_to_a_distributor,s_account',[],'transaction')
                    . '</option>';
                $value .= '<option value="add">' .
                    $this->get('translator')->trans('add_new_codes_to_system',[],'transaction')
                    . '</option>';

                $value .= '<option value="pmt">' .
                    $this->get('translator')->trans('credit_provider,s_account',[],'transaction')
                    . '</option>';

                $value .= '<option value="amdt">' .
                    $this->get('translator')->trans('an_amount_is_credited_to_correct_the_price_of_a_code',[],'transaction')
                    . '</option>';
                break;
        }
        return new Response($value);
    }


    public function  LoadActionDistAction(Request $req)
    {
        $id = $req->get('id', 0);
        $value = '';
        $value .= '<option value="All">' . 'All' . '</option>';

        switch ($id) {
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






//    Master_Retailer




    public function MasterRetailerUserAction(Request $req,$id)
    {
//        $this->check_ChildAccount($id);

        $em = $this->getDoctrine()->getManager();
        $Account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);
        $users = $Account->getUsers();



        return $this->render('HelloDiDiDistributorsBundle:Master_Ratailer:RetailerUser.html.twig', array(
            'Entiti' => $Account->getEntiti(),
            'Account' => $Account->getParent(),
            'retailerAccount' => $Account,
            'Users' => $users
        ));

    }

    public function MasterRetailerUserEditAction(Request $request, $userid)
    {
//        $this->check_ChildUser($userid);
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('HelloDiDiDistributorsBundle:User')->find($userid);
        $form = $this->createForm(new NewUserType('HelloDiDiDistributorsBundle\Entity\User',2), $user, array('cascade_validation' => true));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->flush();
                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Master_Ratailer:RetailerUserEdit.html.twig', array(
            'retailerAccount' => $user->getAccount(),
            'Entiti' => $user->getEntiti(),
            'userid' => $userid,
            'Account'=>$user->getAccount()->getParent(),
            'form' => $form->createView()
        ));

    }

    public function MasterRetailerUserAddAction(Request $request, $id)
    {
//        $this->check_ChildAccount($id);
        $em = $this->getDoctrine()->getManager();
        $AccountRetailer =  $em->getRepository('HelloDiAccountingBundle:Account')->find($id);

        $user = new User();


        $Entiti = $AccountRetailer->getEntiti();

        $form = $this->createForm(new NewUserType('HelloDiDiDistributorsBundle\Entity\User',2),$user);


        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $user->setAccount($AccountRetailer);
            $user->setEntiti($Entiti);
            if ($form->isValid())
            {
                $em->persist($user);
                $em->flush();

                $this->forward('hello_di_di_notification:NewAction',array('id'=>$AccountRetailer->getId(),'type'=>37,'value'=>$user->getUsername()));


                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl('Master_RetailerUser', array('distid'=>$AccountRetailer->getParent()->getId(),'id' => $AccountRetailer->getId())));
            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Master_Ratailer:RetailerUserAdd.html.twig', array(
            'Entiti' => $Entiti,
            'retailerAccount' =>$AccountRetailer,
            'form' => $form->createView(),
            'Account'=>$AccountRetailer->getParent(),
        ));

    }



    public function MasterRetailersTransactionAction(Request $req,$id)
    {
        $em=$this->getDoctrine()->getManager();

        $paginator = $this->get('knp_paginator');

//        $this->check_ChildAccount($id);

        $AccountRetailer=$em->getRepository('HelloDiAccountingBundle:Account')->find($id);
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
            ->add('DateStart','date',
                array(
                    'widget'=>'single_text',
                    'format' => 'yyyy/MM/dd',
                    'required'=>false,
                    'label'=>'From',
                    'translation_domain'=>'transaction',
                    ))

            ->add('DateEnd','date',
                array(
                    'widget'=>'single_text',
                    'format' => 'yyyy/MM/dd',
                    'required'=>false,
                    'label'=>'To',
                    'translation_domain'=>'transaction',
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
                ->from('HelloDiAccountingBundle:Transaction','Tran')
                ->where('Tran.Account = :Acc')->setParameter('Acc',$AccountRetailer);
            if($data['TypeDate']==0)
            {
                if($data['DateStart']!='')
                    $qb->andwhere('Tran.tranDate >= :DateStart')->setParameter('DateStart',$data['DateStart']);
                if($data['DateEnd']!='')
                    $qb->andwhere('Tran.tranDate <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);

            }

            if($data['TypeDate']==1)
            {

                $typedate=1;
                if($data['DateStart']!='')
                    $qb->andwhere('Tran.tranInsert >= :DateStart')->setParameter('DateStart',$data['DateStart']);
                if($data['DateEnd']!='')
                    $qb->andwhere('Tran.tranInsert <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);

            }

            if ($data['Type'] != 2)
                $qb->andWhere($qb->expr()->eq('Tran.tranType',$data['Type']));

            if($data['Action']!='All')
                $qb->andWhere($qb->expr()->like('Tran.tranAction',$qb->expr()->literal($data['Action'])));

            $qb->addOrderBy('Tran.tranInsert','desc')->addOrderBy('Tran.id','desc');

            $qb=$qb->getQuery();
            $count = count($qb->getResult());
            $qb->setHint('knp_paginator.count', $count);


            }

            catch(\Exception $e)
           {
                $this->get('session')->getFlashBag()->add('error',
                $this->get('translator')->trans('You_entered_an_invalid',array(),'message'));
           }


        }

        $pagination = $paginator->paginate(
            $qb,
            $req->get('page',1) /*page number*/,
            10/*limit per page*/
        );

        return $this->render('HelloDiDiDistributorsBundle:Master_Ratailer:RetailersTransaction.html.twig',
            array(
                'pagination'=>$pagination,
                'form'=>$form->createView(),
                'Account'=>$AccountRetailer->getParent(),
                'retailerAccount' => $AccountRetailer,
                'typedate'=>$typedate
            ));



    }




    public function MasterRetailerSettingAction(Request $req, $id)
    {
//        $this->check_ChildAccount($id);

        $em = $this->getDoctrine()->getManager();

        $RetailerAccount= $em->getRepository('HelloDiAccountingBundle:Account')->find($id);
        $form = $this->createForm(new EditRetailerType(),$RetailerAccount);
        if ($req->isMethod('POST')) {
            $form->handleRequest($req);
            if ($form->isValid()) {
                $em->flush();

                $this->forward('hello_di_di_notification:NewAction',array('id'=>$RetailerAccount->getId(),'type'=>35));

                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Master_Ratailer:RetailerSetting.html.twig', array(
            'Entiti' => $RetailerAccount->getEntiti(),
            'retailerAccount' => $RetailerAccount,
            'Account'=>$RetailerAccount->getParent(),
            'form' => $form->createView()
        ));
    }

    public function MasterRetailerDetailsAction(Request $req,$id)
    {
//        $this->check_ChildAccount($id);
        $em = $this->getDoctrine()->getManager();

        $RetailerAccount = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);

        $entity = $RetailerAccount->getEntiti();
        $editForm = $this->createForm(new EditEntitiRetailerType(),$entity);

        if($req->isMethod('post'))
        {
            $editForm->handleRequest($req);
            if($editForm->isValid())
            {
                $em->flush();

                $this->forward('hello_di_di_notification:NewAction',array('id'=>$RetailerAccount->getId(),'type'=>36));

                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Master_Ratailer:RetailersDetails.html.twig', array(

            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'retailerAccount' => $RetailerAccount,
            'Account'=>$RetailerAccount->getParent(),
        ));
    }


    public function  MasterRetailerFundingAction($id)
    {
//        $this->check_ChildAccount($id);

        $em=$this->getDoctrine()->getManager();

        $AccountRetailer=$em->getRepository('HelloDiAccountingBundle:Account')->find($id);

        $formapplay=$this->createFormBuilder()
            ->add('Amount','money',array(
                'currency'=>$AccountRetailer->getAccCurrency(),
                'invalid_message'=>'You_entered_an_invalid',
                'label'=>'Amount',
                'translation_domain'=>'transaction'))
            ->add('Communications','textarea',array('required'=>true,'label'=>'Communications','translation_domain'=>'transaction'))
            ->add('Description','textarea',array('required'=>true,'label'=>'Description','translation_domain'=>'transaction'))
            ->getForm();

        $formupdate=$this->createFormBuilder()
            ->add('Amount','money',array(
                'currency'=>$AccountRetailer->getAccCurrency(),
                'invalid_message'=>'You_entered_an_invalid',
                'label'=>'Amount','translation_domain'=>'transaction'))
            ->add('As','choice',array('label'=>'As','translation_domain'=>'transaction'
                ,
                'choices'=>
                array(
//                    'required'=>true,
                    ''=>'select_a_action',
                    1=>'Increase',
                    0=>'Decrease')
            ))->getForm();

        return $this->render('HelloDiDiDistributorsBundle:Master_Ratailer:Funding.html.twig',
            array(

                'Account'=>$AccountRetailer->getParent(),
                'retailerAccount'=>$AccountRetailer,
                'formapplay'=>$formapplay->createView(),
                'formupdate'=>$formupdate->createView(),

            ));
    }


    public function  MasterRetailerFundingTransferAction(Request $req,$id)
    {

//        $this->check_ChildAccount($id);


        $balancechecker=$this->get('hello_di_di_distributors.balancechecker');

        $User= $this->get('security.context')->getToken()->getUser();
        $em=$this->getDoctrine()->getManager();
        $AccountRetailer=$em->getRepository('HelloDiAccountingBundle:Account')->find($id);

        $formtransfer=$this->createFormBuilder()
            ->add('Amount',null,array('label'=>'Amount','translation_domain'=>'accounts'))
            ->add('Communications','textarea',array('required'=>true,'label'=>'Communications','translation_domain'=>'transaction'))
            ->add('Description','textarea',array('required'=>true,'label'=>'Description','translation_domain'=>'transaction'))
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
            $trandist->setTranCurrency($AccountRetailer->getParent()->getAccCurrency());
            $trandist->setTranInsert(new \DateTime('now'));
            $trandist->setAccount($AccountRetailer->getParent());
            $trandist->setUser($User);
            $trandist->setTranFees(0);
            $trandist->setTranAction('tran');
            $trandist->setTranType(0);
            $trandist->setTranBalance($AccountRetailer->getParent()->getAccBalance());
            $trandist->setTranDescription($data['Description']);





            #transaction for retailer#

            $tranretailer->setTranDate(new \DateTime('now'));
            $tranretailer->setTranCurrency($AccountRetailer->getAccCurrency());
            $tranretailer->setTranInsert(new \DateTime('now'));
            $tranretailer->setAccount($AccountRetailer);
            $tranretailer->setUser($User);
            $tranretailer->setTranFees(0);
            $tranretailer->setTranAction('tran');
            $tranretailer->setTranType(1);
            $tranretailer->setTranBalance($AccountRetailer->getAccBalance());
            $tranretailer->setTranDescription($data['Communications']);

            $alredyretailer=$AccountRetailer->getAccBalance();
            $alredydist=$AccountRetailer->getParent()->getAccBalance();
            if($data['Amount']>0)
            {
                if($balancechecker->isBalanceEnoughForMoney($AccountRetailer->getParent(),$data['Amount']))
                {
                    $tranretailer->setTranAmount(+$data['Amount']);
                    $trandist->setTranAmount(-$data['Amount']);
                    $em->persist($trandist);
                    $em->persist($tranretailer);
                    $em->flush();

                    $this->forward('hello_di_di_notification:NewAction',array('id'=>$AccountRetailer->getId(),'type'=>32,'value'=>$data['Amount'].$AccountRetailer->getAccCurrency()));
                    $this->forward('hello_di_di_notification:NewAction',array('id'=>$AccountRetailer->getParent()->getId(),'type'=>23,'value'=>$data['Amount'].' '.$AccountRetailer->getParent()->getAccCurrency()));

                   if($AccountRetailer->getAccBalance()+$AccountRetailer->getAccCreditLimit()<=15000)
                       $this->forward('hello_di_di_notification:NewAction',array('id'=>$AccountRetailer->getId(),'type'=>31,'value'=>'15000 ' .$AccountRetailer->getAccCurrency()));

                    if($AccountRetailer->getParent()->getAccBalance()+$AccountRetailer->getParent()->getAccCreditLimit()<=15000)
                    {
                        $this->forward('hello_di_di_notification:NewAction',array('id'=>$AccountRetailer->getParent()->getId(),'type'=>121,'value'=>'15000 ' .$AccountRetailer->getParent()->getAccCurrency()));
                        $this->forward('hello_di_di_notification:NewAction',array('id'=>null,'type'=>121,'value'=>'15000 ' .$AccountRetailer->getParent()->getAccCurrency().'   ('.$AccountRetailer->getParent()->getAccName().')'));
                    }



                    $this->get('session')->getFlashBag()->add('success',
                        $this->get('translator')->trans('Retailer_account_was_changed_from_%alredyretailer%_to_%currentretailer%',
                            array('alredyretailer'=>$alredyretailer,'currentretailer'=>$AccountRetailer->getAccBalance()),
                            'message')
                    );

                    $this->get('session')->getFlashBag()->add('success',
                        $this->get('translator')->trans('Distributor_account_was_changed_from_%alredydist%_to_%currentdist%',
                            array('alredydist'=>$alredydist,'currentdist'=>$AccountRetailer->getParent()->getAccBalance()),
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
        return $this->redirect($this->generateUrl('Master_RetailerFunding',array('distid'=>$AccountRetailer->getParent()->getId(),'id'=>$id)));

  }
    public function  MasterRetailerFundingUpdateAction(Request $req,$id)
    {
//        $this->check_ChildAccount($id);
        $balancechecker=$this->get('hello_di_di_distributors.balancechecker');

        $User= $this->get('security.context')->getToken()->getUser();

        $em=$this->getDoctrine()->getManager();

        $AccountRetailer=$em->getRepository('HelloDiAccountingBundle:Account')->find($id);

        $formupdate=$this->createFormBuilder()
            ->add('Amount',null,array('label'=>'Amount','translation_domain'=>'accounts'))
            ->add('As','choice',array('label'=>'As','translation_domain'=>'transaction',
                'choices'=>array(
                    1=>'Increase',
                    0=>'Decrease')
            ))->getForm();

        if($req->isMethod('POST'))
        {

 try
 {
            $formupdate->handleRequest($req);
            $data=$formupdate->getData();

            $trandist=new Transaction();

            $trandist->setTranDate(new \DateTime('now'));
            $trandist->setTranCurrency($AccountRetailer->getParent()->getAccCurrency());

            $trandist->setTranInsert(new \DateTime('now'));

            $trandist->setUser($User);
            $trandist->setTranFees(0);
            $trandist->setTranAction('crlt');
            $trandist->setTranBalance($AccountRetailer->getParent()->getAccBalance());
            $trandist->setTranType(0);
            $trandist->setAccount($AccountRetailer->getParent());

            $alredyretailer=$AccountRetailer->getAccCreditLimit();
            $alredydist=$AccountRetailer->getParent()->getAccBalance();
            if($data['Amount']>0)
            {

                if($data['As']==1)
                {
                    if($balancechecker->isBalanceEnoughForMoney($AccountRetailer->getParent(),$data['Amount']))
                    {
                        $trandist->setTranDescription('increase retailer,s credit limit ');
                        $trandist->setTranAmount(-$data['Amount']);
                        $AccountRetailer->setAccCreditLimit($AccountRetailer->getAccCreditLimit()+$data['Amount']);
                        $em->persist($trandist);
                        $em->flush();

                        $this->forward('hello_di_di_notification:NewAction',array('id'=>$AccountRetailer->getId(),'type'=>33,'value'=>$data['Amount'].$AccountRetailer->getAccCurrency()));
                        $this->forward('hello_di_di_notification:NewAction',array('id'=>$AccountRetailer->getParent()->getId(),'type'=>23,'value'=>$data['Amount'].$AccountRetailer->getParent()->getAccCurrency()));

                        if($AccountRetailer->getParent()->getAccBalance()+$AccountRetailer->getParent()->getAccCreditLimit()<=15000)
                        {
                            $this->forward('hello_di_di_notification:NewAction',array('id'=>$AccountRetailer->getParent()->getId(),'type'=>121,'value'=>'15000 ' .$AccountRetailer->getParent()->getAccCurrency()));
                            $this->forward('hello_di_di_notification:NewAction',array('id'=>null,'type'=>121,'value'=>'15000 ' .$AccountRetailer->getParent()->getAccCurrency().'   ('.$AccountRetailer->getParent()->getAccName().')'));
                        }


                        $this->get('session')->getFlashBag()->add('success',
                            $this->get('translator')->trans('Retailer_creditlimit_was_changed_from_%alredyretailer%_to_%currentretailer%',
                                array('alredyretailer'=>$alredyretailer,'currentretailer'=>$AccountRetailer->getAccCreditLimit()),
                                'message')
                        );

                        $this->get('session')->getFlashBag()->add('success',
                            $this->get('translator')->trans('Distributor_account_was_changed_from_%alredydist%_to_%currentdist%',
                                array('alredydist'=>$alredydist,'currentdist'=>$AccountRetailer->getParent()->getAccBalance()),
                                'message')
                        );

                    }
                }

                elseif($data['As']==0)
                {

                    if($balancechecker->isAccCreditLimitPlus($AccountRetailer,$data['Amount']))
                    {
                        $AccountRetailer->setAccCreditLimit($AccountRetailer->getAccCreditLimit()- $data['Amount']);
                        $em->flush();

                        $this->forward('hello_di_di_notification:NewAction',array('id'=>$AccountRetailer->getId(),'type'=>34,'value'=>$data['Amount'].$AccountRetailer->getAccCurrency()));


                        $this->get('session')->getFlashBag()->add('success',
                            $this->get('translator')->trans('Retailer_creditlimit_was_changed_from_%alredyretailer%_to_%currentretailer%',
                                array('alredyretailer'=>$alredyretailer,'currentretailer'=>$AccountRetailer->getAccCreditLimit()),
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
        return $this->redirect($this->generateUrl('Master_RetailerFunding',array('distid'=>$AccountRetailer->getParent()->getId(),'id'=>$id)));
  }

    public function MasterLoadActionRetailerAction(Request $req)
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

    //-----
    public function MasterRetailerItemsAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);

        $distaccount = $account->getParent();

        $prices = $account->getPrices();

        return $this->render('HelloDiDiDistributorsBundle:Master_Ratailer:RetailerItems.html.twig', array(
            'Account' => $distaccount,
            'retailerAccount' => $account,
            'prices' => $prices
        ));
    }

    public function MasterRetailerItemsAddAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);

        $distaccount = $account->getParent();

        $price = new Price();
        $price->setPriceCurrency($account->getAccCurrency());
        $price->setPriceStatus(1);
        $price->setAccount($account);
        $price->setIsFavourite(false);

        $form = $this->createFormBuilder($price)
            ->add('Item', 'entity', array(
                'class' => 'HelloDiDiDistributorsBundle:Item',
                'property' => 'itemName',
                'query_builder' => function(EntityRepository $er) use ($account,$distaccount) {
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
                        ->setParameter('aamyid',$distaccount)
                        ;
                },
                'label' => 'Item','translation_domain' => 'item'
            ))
            ->add('price','number',array('label' => 'Price','translation_domain' => 'price'))
            ->getForm();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $distprice = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Item'=>$price->getItem(),'Account'=>$distaccount))->getPrice();
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
                return $this->forward('HelloDiDiDistributorsBundle:Account:MasterRetailerItems', array(
                    'distid'=>$distaccount->getId(),
                    'id' => $account->getId()
                ));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Master_Ratailer:RetailerItemsAdd.html.twig', array(
            'Account' => $distaccount,
            'retailerAccount' => $account,
            'form' => $form->createView()
        ));
    }

    public function MasterRetailerItemsEditAction($priceid, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->find($priceid);

        $distaccount = $price->getAccount()->getParent();

        $oldprice = $price->getPrice();

        $form = $this->createForm(new PriceEditType(null), $price);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $distprice = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Item'=>$price->getItem(),'Account'=>$distaccount))->getPrice();
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
                return $this->forward('HelloDiDiDistributorsBundle:Account:MasterRetailerItems', array(
                    'distid'=>$distaccount->getId(),
                    'id' => $price->getAccount()->getId()
                ));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Master_Ratailer:RetailerItemsEdit.html.twig', array(
            'Account' => $distaccount,
            'retailerAccount' => $price->getAccount(),
            'price' => $price,
            'form' => $form->createView()
        ));
    }

//    End Retailer
}
