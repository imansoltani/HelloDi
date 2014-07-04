<?php
namespace HelloDi\DistributorBundle\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\AccountingBundle\Container\TransactionContainer;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\AccountingBundle\Entity\CreditLimit;
use HelloDi\AccountingBundle\Entity\Transaction;
use HelloDi\CoreBundle\Entity\Entity;
use HelloDi\CoreBundle\Entity\User;
use HelloDi\DistributorBundle\Form\RetailerAccountUserType;
use HelloDi\DistributorBundle\Form\RetailerSearchType;
use HelloDi\MasterBundle\Form\CreditLimitType;
use HelloDi\MasterBundle\Form\EntityType;
use HelloDi\MasterBundle\Form\TransactionType;
use HelloDi\RetailerBundle\Entity\Retailer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RetailerController extends Controller
{
    public function indexAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $distributor = $em->getRepository('HelloDiDistributorBundle:Distributor')->findOneBy(array('account'=> $this->getUser()->getAccount()));

        $qb = $em->createQueryBuilder()
            ->select('retailer')
            ->from('HelloDiRetailerBundle:Retailer', 'retailer')
            ->where('retailer.distributor = :distributor')->setParameter('distributor', $distributor);

        $form = $this->createForm(new RetailerSearchType($distributor, $em),null,array('attr'=>array('class'=>'SearchForm')))
            ->add('search','submit');

        if($request->isMethod('post'))
        {
            $form->handleRequest($request);

            if($form->isValid()) {
                $data = $form->getData();

                $qb ->innerJoin('retailer.account', 'account')
                    ->innerJoin('account.entity', 'entity');

                if(isset($data['city']))
                    $qb->andWhere('entity.city = :city')->setParameter('city', $data['city']);

                if(isset($data['balanceValue']))
                    $qb->andWhere('account.balance '.$data['balanceType'].' :balance')
                        ->setParameter('balance', $data['balanceValue']);
            }
        }

        $retailers = $qb->getQuery()->getResult();

        return $this->render('HelloDiDistributorBundle:retailer:index.html.twig', array(
                'retailers' => $retailers,
                'form' => $form->createView(),
        ));
    }

    public function addAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $distributor = $em->getRepository('HelloDiDistributorBundle:Distributor')->findOneBy(array('account'=>$this->getUser()->getAccount()));

        $retailer = new Retailer();
        $retailer->setDistributor($distributor);
        $distributor->addRetailer($retailer);

        $account = new Account();
        $account->setCreationDate(new \DateTime('now'));
        $account->setType(Account::DISTRIBUTOR);
        $retailer->setAccount($account);

        $entity = new Entity();
        $account->setEntity($entity);
        $entity->addAccount($account);

        $user = new User();
        $user->setAccount($account);
        $user->setEntity($entity);
        $account->addUser($user);
        $entity->addUser($user);

        $languages = $this->container->getParameter('languages');

        $form = $this->createForm(new RetailerAccountUserType($languages), $retailer, array('cascade_validation' => true));
        $form->get('account')->add('entity',new EntityType());

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($retailer);
                $em->persist($account);
                $em->persist($entity);
                $em->persist($user);
                $em->flush();

//                $this->forward('hello_di_di_notification:NewAction',array('id'=>null,'type'=>13,'value'=>'   ('.$Account->getAccName().')'));
                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl('hello_di_distributor_retailer_index'));
            }
        }

        return $this->render('HelloDiDistributorBundle:retailer:add.html.twig', array(
                'form' => $form->createView(),
                'account' => $account
            ));
    }

    //transactions
    public function transactionAction(Request $request, $id)
    {//TODO must be edit
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $retailer = $em->getRepository('HelloDiRetailerBundle:Retailer')->findByAccountId($id);
        if(!$retailer || $retailer->getDistributor()->getAccount() != $this->getUser()->getAccount())
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'account'),'message'));

        $form = $this->createFormBuilder(null,array('attr'=>array('class'=>'SearchForm')))
            ->add('TypeDate','choice',array(
                    'translation_domain' => 'transaction',
                    'expanded' => true,
                    'choices' => array(
                        0 => 'TradeDate',
                        1 => 'BookingDate',
                    )
                ))
            ->add('FromDate','date',array(
                    'widget' => 'single_text',
                    'format' => "yyyy/MM/dd",
                    'disabled' => false,
                    'required' => false,
                    'label' => 'From',
                    'translation_domain' => 'transaction'
                ))
            ->add('ToDate','date',array(
                    'widget' => 'single_text',
                    'format' => "yyyy/MM/dd",
                    'disabled' => false,
                    'required' => false,
                    'label' => 'To',
                    'translation_domain' => 'transaction'
                ))
            ->add('type','choice',array(
                    'label' => 'Type',
                    'translation_domain' => 'transaction',
                    'choices' => array(
                        2 => 'All',
                        1 => 'Credit',
                        0 => 'Debit'
                    )
                ))
            ->add('Action','choice',array('label' => 'Action','translation_domain' => 'transaction','choices' => array(
                    'All' => 'All',
                    'add' => 'add_new_codes_to_system',
                    'pmt' => 'credit_provider,s_account',
                    'amdt' => 'an_amount_is_credited_to_correct_the_price_of_a_code',
                    'abmdt' => 'an_amount_is_debited_to_correct_the_price_of_a_code',
                    'rmv' => 'remove_codes_from_to_system',
                    'aamdt' => 'debit_provider,s_account',
                    'tran' => 'transfer_credit_from_provider,s_account_to_a_distributor,s_account',
                )))
            ->setMethod('post')
            ->add('search','submit')
            ->getForm();

        $qb = array();

        if($request->isMethod('POST'))
        {
            $form->handleRequest($request);

            if($form->isValid())
            {
//                $data = $form->getData();

//                $qb=$em->createQueryBuilder();
//                $qb->select('Tran')
//                    ->from('HelloDiAccountingBundle:Transaction','Tran')
//                    ->where('Tran.Account = :Acc')->setParameter('Acc',$Account);
//
//                if($data['TypeDate']==1)$typedate=1; else $typedate=0;
//
//                if($data['DateStart']!='')
//                    $qb->andwhere('Tran.tranInsert >= :DateStart')->setParameter('DateStart',$data['DateStart']);
//                if($data['DateEnd']!='')
//                    $qb->andwhere('Tran.tranInsert <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);
//
//                if ($data['Type'] != 2)
//                    $qb->andWhere($qb->expr()->eq('Tran.tranType',$data['Type']));
//
//                if($data['Action']!='All')
//                    $qb->andWhere($qb->expr()->like('Tran.tranAction',$qb->expr()->literal($data['Action'])));
//
//                $qb->addOrderBy('Tran.tranInsert','desc')->addOrderBy('Tran.id','desc');;
//
//                $qb=$qb->getQuery();
//                $count = count($qb->getResult());
//                $qb->setHint('knp_paginator.count', $count);

                $qb = $em->createQueryBuilder()
                    ->select('transaction')
                    ->from('HelloDiAccountingBundle:Transaction', 'transaction')
                    ->where('transaction.account = :account')->setParameter('account', $retailer->getAccount())
                    ->orderBy('transaction.date', 'desc')->addOrderBy('transaction.id', 'desc')
                    ->getQuery();
            }
        }

        $transactions = $this->get('knp_paginator')->paginate(
            $qb,
            $request->get('page', 1),
            10
        );

        return $this->render('HelloDiDistributorBundle:retailer:transaction.html.twig', array(
                'transactions' => $transactions,
                'form' => $form->createView(),
                'retailerAccount' => $retailer->getAccount(),
            ));
    }

    public function fundingAction(Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $retailer = $em->getRepository('HelloDiRetailerBundle:Retailer')->findByAccountId($id);
        if(!$retailer || $retailer->getDistributor()->getAccount() != $this->getUser()->getAccount())
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'account'),'message'));

        $transaction = new Transaction();
        $balanceForm = $this->createForm(new TransactionType($retailer->getDistributor()->getCurrency()), $transaction, array('method'=>'post','attr'=>array(
                'class' => 'YesNoMessage',
                'header' => $this->get('translator')->trans('Fund_distributor',array(),'message'),
                'message' => $this->get('translator')->trans('Are_you_sure_you_perform_this_operation?',array(),'message'),
            )))
            ->add('apply','submit', array(
                    'label'=>'Apply','translation_domain'=>'common',
                    'attr'=>array('first-button')
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('onclick'=>'history.go(-1)','last-button')
                ))
        ;

        $creditLimit = new CreditLimit();
        $creditLimitForm = $this->createForm(new CreditLimitType($retailer->getDistributor()->getCurrency()), $creditLimit, array('method'=>'post','attr'=>array(
                'class' => 'YesNoMessage',
                'header' => $this->get('translator')->trans('Fund_distributor',array(),'message'),
                'message' => $this->get('translator')->trans('Are_you_sure_you_perform_this_operation?',array(),'message'),
            )))
            ->add('update','submit', array(
                    'label'=>'Update','translation_domain'=>'common',
                    'attr'=>array('first-button')
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('onclick'=>'history.go(-1)','last-button')
                ))
        ;

        if($request->isMethod('POST')) {
            $balanceForm->handleRequest($request);
            $creditLimitForm->handleRequest($request);

            if($balanceForm->isValid())
            {
                $beforeBalance = $retailer->getAccount()->getBalance();

                $result = $this->get('accounting')->processTransaction(array(new TransactionContainer(
                        $retailer->getAccount(),
                        $transaction->getAmount(),
                        $transaction->getDescription(),
                        $transaction->getFees()
                    )));

                if($result) {
                    $this->get('session')->getFlashBag()->add('success',
                        $this->get('translator')->trans('Distributor_account_was_changed_from_%alredydist%_to_%currentdist%',
                            array('alredydist'=>$beforeBalance,'currentdist'=>$retailer->getAccount()->getBalance()),
                            'message')
                    );
                    return $this->redirect($this->generateUrl('hello_di_master_distributor_transaction_funding', array('id' => $id)));
                }
                else
                    $this->get('session')->getFlashBag()->add('error', 'this account has not enough balance!');
            }

            if($creditLimitForm->isValid())
            {
                $beforeCreditLimit = $retailer->getAccount()->getCreditLimitAmount();

                $result = $this->get('accounting')->newCreditLimit(
                    $creditLimit->getAmount(),
                    $this->getUser(),
                    $retailer->getAccount()
                );

                if($result) {
                    $this->get('session')->getFlashBag()->add('success',
                        $this->get('translator')->trans('Distributor_creditlimit_was_changed_from_%alredydist%_to_%currentdist%',
                            array('alredydist'=>$beforeCreditLimit,'currentdist'=>$retailer->getAccount()->getCreditLimitAmount()),
                            'message')
                    );
                    return $this->redirect($this->generateUrl('hello_di_master_distributor_transaction_funding', array('id' => $id)));
                }
                else
                    $this->get('session')->getFlashBag()->add('error', 'this account has not enough balance!');
            }
        }

        return $this->render('HelloDiMasterBundle:distributor:funding.html.twig', array(
                'retailer' => $retailer,
                'retailerAccount' => $retailer->getAccount(),
                'balanceForm' => $balanceForm->createView(),
                'creditLimitForm' => $creditLimitForm->createView(),
            ));
    }
}
