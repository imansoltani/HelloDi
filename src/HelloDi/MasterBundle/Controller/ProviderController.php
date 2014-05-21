<?php
namespace HelloDi\MasterBundle\Controller;

use HelloDi\AccountingBundle\Container\TransactionContainer;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\AccountingBundle\Entity\Transaction;
use HelloDi\CoreBundle\Entity\Entity;
use HelloDi\CoreBundle\Entity\Provider;
use HelloDi\MasterBundle\Form\ProviderAccountEntityUserType;
use HelloDi\MasterBundle\Form\TransactionType;
use HelloDi\MasterBundle\Form\TransferType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ProviderController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $providers = $em->getRepository('HelloDiCoreBundle:Provider')->findAll();

        return $this->render('HelloDiMasterBundle:provider:index.html.twig', array(
            'providers' => $providers
        ));
    }

    public function addAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $provider = new Provider();

        $account = new Account();
        $account->setCreationDate(new \DateTime('now'));
        $account->setType(Account::PROVIDER);
        $provider->setAccount($account);

        $entity = new Entity();
        $account->setEntity($entity);
        $entity->addAccount($account);

        $currencies = $this->container->getParameter('Currencies.Account');
        $languages = $this->container->getParameter('languages');

        $form = $this->createForm(new ProviderAccountEntityUserType($currencies,$languages), $provider, array('cascade_validation' => true));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($provider);
                $em->persist($account);
                $em->persist($entity);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'this operation done success !');
                return $this->redirect($this->generateUrl('hello_di_master_provider_index'));
            }
        }

        return $this->render('HelloDiMasterBundle:provider:add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    //transactions
    public function transactionsAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);

        if(!$account)
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

        return $this->render('HelloDiMasterBundle:provider:transaction.html.twig', array(
            'transactions' => $transactions,
            'account' => $account,
            'form' => $form->createView()
        ));
    }

    public function transactionRegisterAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $provider = $em->getRepository('HelloDiCoreBundle:Provider')->findByAccountId($id);

        if(!$provider)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'Provider'),'message'));

        $transaction = new Transaction();
        $form = $this->createForm(new TransactionType($provider->getCurrency()),$transaction,array('method'=>'post','attr'=>array(
                'class' => 'YesNoMessage',
                'header' => $this->get('translator')->trans('Register_transaction',array(),'message'),
                'message' => $this->get('translator')->trans('Are_you_sure_you_perform_this_operation?',array(),'message'),
            )))
            ->add('register','submit', array(
                    'label'=>'Register','translation_domain'=>'common',
                    'attr'=>array('first-button')
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('onclick'=>'history.go(-1)','last-button')
                ))
            ;

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if($form->isValid())
            {
                $result = $this->get('accounting')->processTransaction(array(new TransactionContainer(
                        $provider->getAccount(),
                        $transaction->getAmount(),
                        $transaction->getDescription(),
                        $transaction->getFees()
                )));

                if($result)
                {
                    $this->get('session')->getFlashBag()->add('success', 'this operation done success !');
                    return $this->redirect($this->generateUrl('hello_di_master_provider_transaction',array('id'=>$id)));
                }
                else
                    $this->get('session')->getFlashBag()->add('error', 'this account has not enough balance!');
            }
        }

        return $this->render('HelloDiMasterBundle:provider:transactionRegister.html.twig',array(
            'form' => $form->createView(),
            'account' => $provider->getAccount(),
        ));
    }

    public function transactionTransferAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $provider = $em->getRepository('HelloDiCoreBundle:Provider')->findByAccountId($id);

        if(!$provider)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'Provider'),'message'));

        $form = $this->createForm(new TransferType($provider),null,array('method'=>'post','attr'=>array(
                'class' => 'YesNoMessage',
                'message' => $this->get('translator')->trans('Are_you_sure_you_perform_this_operation?',array(),'message'),
                'header' => $this->get('translator')->trans('Make_transfer',array(),'message'),
            )))
            ->add('register','submit', array(
                    'label'=>'Register','translation_domain'=>'common',
                    'attr'=>array('first-button')
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('onclick'=>'history.go(-1)','last-button')
                ))
        ;

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if($form->isValid())
            {
                $data = $form->getData();

                /** @var Provider $providerDestination */
                $providerDestination =  $data['provider'];

                $result = $this->get('accounting')->processTransfer(
                    $data['amount'],
                    $this->getUser(),
                    $providerDestination->getAccount(),
                    $data['descriptionForOrigin'],
                    $data['descriptionForDestination']
                );

                if($result)
                {
                    $this->get('session')->getFlashBag()->add('success', 'this operation done success !');
                    return $this->redirect($this->generateUrl('hello_di_master_provider_transaction',array('id'=>$id)));
                }
                else
                    $this->get('session')->getFlashBag()->add('error', 'this account has not enough balance!');
            }
        }

        return $this->render('HelloDiMasterBundle:provider:transactionTransfer.html.twig', array(
                'form' => $form->createView(),
                'account' => $provider->getAccount(),
            ));
    }

    //items
    public function itemsAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);

        if(!$account)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'account'),'message'));

        $prices = $account->getPrices();

        return $this->render('HelloDiMasterBundle:provider:items.html.twig', array(
                'account' => $account,
                'prices' => $prices
            ));
    }
}