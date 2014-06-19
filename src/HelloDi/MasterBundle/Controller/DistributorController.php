<?php
namespace HelloDi\MasterBundle\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\AccountingBundle\Container\TransactionContainer;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\AccountingBundle\Entity\CreditLimit;
use HelloDi\AccountingBundle\Entity\Transaction;
use HelloDi\CoreBundle\Entity\Entity;
use HelloDi\CoreBundle\Entity\User;
use HelloDi\DistributorBundle\Entity\Distributor;
use HelloDi\MasterBundle\Form\CreditLimitType;
use HelloDi\MasterBundle\Form\DistributorAccountUserType;
use HelloDi\MasterBundle\Form\EntityType;
use HelloDi\MasterBundle\Form\TransactionType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DistributorController extends Controller
{
    public function indexAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $distributors = $em->getRepository('HelloDiDistributorBundle:Distributor')->findAll();

        return $this->render('HelloDiMasterBundle:distributor:index.html.twig', array(
                'distributors' => $distributors
            ));
    }

    public function addAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $distributor = new Distributor();

        $account = new Account();
        $account->setCreationDate(new \DateTime('now'));
        $account->setType(Account::DISTRIBUTOR);
        $distributor->setAccount($account);

        $entity = new Entity();
        $account->setEntity($entity);
        $entity->addAccount($account);

        $user = new User();
        $user->setAccount($account);
        $user->setEntity($entity);
        $account->addUser($user);
        $entity->addUser($user);

        $currencies = $this->container->getParameter('Currencies.Account');
        $languages = $this->container->getParameter('languages');

        $form = $this->createForm(new DistributorAccountUserType($currencies,$languages), $distributor, array('cascade_validation' => true));
        $form->get('account')->add('entity',new EntityType());

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($distributor);
                $em->persist($account);
                $em->persist($entity);
                $em->persist($user);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'this operation done success !');
                return $this->redirect($this->generateUrl('hello_di_master_distributor_index'));
            }
        }

        return $this->render('HelloDiMasterBundle:distributor:add.html.twig', array(
                'form' => $form->createView(),
            ));
    }

    //transactions
    public function transactionsAction(Request $request, $id)
    {//TODO must be edit
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);
        if(!$account || $account->getType() != Account::DISTRIBUTOR)
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

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if($form->isValid())
            {
//                $data = $form->getData();
                $qb = $em->createQueryBuilder()
                    ->select('transaction')
                    ->from('HelloDiAccountingBundle:Transaction', 'transaction')
                    ->where('transaction.account = :account')->setParameter('account', $account)
                    ->orderBy('transaction.date', 'desc')->addOrderBy('transaction.id', 'desc')
                    ->getQuery();
            }
        }

        $transactions = $this->get('knp_paginator')->paginate(
            $qb,
            $request->get('page', 1),
            10
        );

        return $this->render('HelloDiMasterBundle:distributor:transaction.html.twig', array(
                'transactions' => $transactions,
                'account' => $account,
                'form' => $form->createView()
            ));
    }

    public function fundingAction(Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $distributor = $em->getRepository('HelloDiDistributorBundle:Distributor')->findByAccountId($id);
        if(!$distributor)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'account'),'message'));

        $transaction = new Transaction();
        $balanceForm = $this->createForm(new TransactionType($distributor->getCurrency()), $transaction, array('method'=>'post','attr'=>array(
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
        $creditLimitForm = $this->createForm(new CreditLimitType($distributor->getCurrency()), $creditLimit, array('method'=>'post','attr'=>array(
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
                $beforeBalance = $distributor->getAccount()->getBalance();

                $result = $this->get('accounting')->processTransaction(array(new TransactionContainer(
                        $distributor->getAccount(),
                        $transaction->getAmount(),
                        $transaction->getDescription(),
                        $transaction->getFees()
                    )));

                if($result) {
                    $this->get('session')->getFlashBag()->add('success',
                        $this->get('translator')->trans('Distributor_account_was_changed_from_%alredydist%_to_%currentdist%',
                            array('alredydist'=>$beforeBalance,'currentdist'=>$distributor->getAccount()->getBalance()),
                            'message')
                    );
                    return $this->redirect($this->generateUrl('hello_di_master_distributor_transaction_funding', array('id' => $id)));
                }
                else
                    $this->get('session')->getFlashBag()->add('error', 'this account has not enough balance!');
            }

            if($creditLimitForm->isValid())
            {
                $beforeCreditLimit = $distributor->getAccount()->getCreditLimitAmount();

                $result = $this->get('accounting')->newCreditLimit(
                    $creditLimit->getAmount(),
                    $this->getUser(),
                    $distributor->getAccount()
                );

                if($result) {
                    $this->get('session')->getFlashBag()->add('success',
                        $this->get('translator')->trans('Distributor_creditlimit_was_changed_from_%alredydist%_to_%currentdist%',
                            array('alredydist'=>$beforeCreditLimit,'currentdist'=>$distributor->getAccount()->getCreditLimitAmount()),
                            'message')
                    );
                    return $this->redirect($this->generateUrl('hello_di_master_distributor_transaction_funding', array('id' => $id)));
                }
                else
                    $this->get('session')->getFlashBag()->add('error', 'this account has not enough balance!');
            }
        }

        return $this->render('HelloDiMasterBundle:distributor:funding.html.twig',
            array(
                'distributor' => $distributor,
                'account' => $distributor->getAccount(),
                'balanceForm' => $balanceForm->createView(),
                'creditLimitForm' => $creditLimitForm->createView(),

            ));
    }


}