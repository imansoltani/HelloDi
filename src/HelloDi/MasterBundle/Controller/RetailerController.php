<?php
namespace HelloDi\MasterBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RetailerController extends Controller
{
    public function distributorindexAction($id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $distributor = $em->getRepository('HelloDiDistributorBundle:Distributor')->findByAccountId($id);
        if(!$distributor)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'account'),'message'));

        $retailers = $distributor->getRetailers();

        return $this->render('HelloDiMasterBundle:retailer:index.html.twig', array(
                'account' => $distributor->getAccount(),
                'retailers' => $retailers,
            ));
    }
    //TODO this file copied from MasterBundle:DistributorController. EDIT IT.
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
        $balanceForm = $this->createForm(new TransactionType($distributor->getCurrency()), $transaction, array('attr'=>array(
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
        $creditLimitForm = $this->createForm(new CreditLimitType($distributor->getCurrency()), $creditLimit, array('attr'=>array(
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

    //items
    public function itemsAction($id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $distributor = $em->getRepository('HelloDiDistributorBundle:Distributor')->findByAccountId($id);
        if(!$distributor)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'account'),'message'));

        return $this->render('HelloDiMasterBundle:distributor:item.html.twig', array(
                'account' => $distributor->getAccount(),
                'distributor' => $distributor
            ));
    }

    //purchases
    public function purchasesAction(Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $distributor = $em->getRepository('HelloDiDistributorBundle:Distributor')->findByAccountId($id);
        if(!$distributor)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'account'),'message'));

        $form = $this->createFormBuilder(null,array('attr'=>array('class'=>'SearchForm')))
            ->add('dateFrom', 'date', array(
                    'format'=>'yyyy/MM/dd',
                    'widget'=>'single_text',
                    'data'=>((new \DateTime('now'))->sub(new \DateInterval('P7D'))),
                    'required' => false,
                    'label' => 'From', 'translation_domain'=>'transaction'
                ))
            ->add('dateTo', 'date', array(
                    'format'=>'yyyy/MM/dd',
                    'widget'=>'single_text',
                    'data'=>(new \DateTime('now')),
                    'required' => false,
                    'label' => 'To','translation_domain'=>'transaction'
                ))
            ->add('itemType', 'choice', array(
                    'label' => 'ItemType','translation_domain'=>'item',
                    'required' => false,
                    'empty_value' => 'All',
                    'choices' => array(
                        Item::DMTU => 'Mobile',
                        Item::CLCD => 'Calling_Card',
                        Item::EPMT => 'E-payment',
                        Item::IMTU => 'IMTU',
                    )
                ))
            ->add('itemName', 'entity', array(
                    'label' => 'Item','translation_domain'=>'item',
                    'empty_value' => 'All',
                    'required' => false,
                    'class' => 'HelloDiCoreBundle:Item',
                    'property' => 'name',
                    'query_builder' => function (EntityRepository $er) use ($distributor) {
                            return $er->createQueryBuilder('item')
                                ->innerJoin('item.prices', 'price')
                                ->andWhere('price.account = :account')->setParameter('account', $distributor->getAccount());
                        }
                ))
            ->add('submit','submit', array(
                    'label'=>'Search','translation_domain'=>'common',
                ))
            ->getForm();

        $transactions = array();

        if($request->isMethod('POST'))
        {
            $form->handleRequest($request);

            if($form->isValid()) {
                $data = $form->getData();

                $qb =  $em->createQueryBuilder()
                    ->select('Tr as TR, count(Tr.Code) as Quantity')
                    ->from('HelloDiAccountingBundle:Transaction','Tr')
                    ->innerJoin('Tr.Account', 'TrAcc')
                    ->innerJoin('Tr.Code', 'TrCo')
                    ->innerJoin('TrCo.Item', 'TrCoIt')
                    ->innerJoin('Tr.TaxHistory', 'TrTh')
                    ->where('Tr.Account = :Acc')->setParameter('Acc',$distributor->getAccount())
                    ->andWhere('Tr.tranAction like :action')->setParameter('action','com')
                    ->groupBy('TrCoIt', 'Tr.BuyingPrice', 'TrTh')
                    ->orderBy('Tr.tranInsert', 'desc')
                ;
                if (isset($data['dateFrom']))
                    $qb->andWhere('Tr.tranDate >= :DateStart')->setParameter('DateStart', $data['DateStart']);
                if (isset($data['dateTo']))
                    $qb->andWhere('Tr.tranDate <= :DateEnd')->setParameter('DateEnd', $data['DateEnd']);
                if (isset($data['itemName']))
                    $qb->andWhere('TrCoIt = :Item')->setParameter('Item',$data['ItemName']);
                if (isset($data['itemType']))
                    $qb->andWhere($qb->expr()->like('TrCoIt.itemType', $qb->expr()->literal($data['ItemType'])));

                $transactions = $qb->getQuery()->getResult();
            }
        }

        if($request->query->has('print')) {
            $html = $this->render('HelloDiMasterBundle:distributor:purchasesPrint.html.twig', array(
                    'transactions' => $transactions
                ));

            return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($html->getContent(),array(
                        'header-html'=>'
                            <div style="font-size:14px;float:left;border:1px solid #999;width:7cm;padding:3px">
                                <b>Distributor Details</b><br/>
                                Account Name: '.$distributor->getAccount()->getName().'<br/>
                                Account Balance: '.$distributor->getAccount()->getBalance().'<br/>
                                Account Currency: '.$distributor->getCurrency().'<br/>
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
        else {
            return $this->render('HelloDiMasterBundle:distributor:purchases.html.twig', array(
                    'transactions' => $transactions,
                    'account' => $distributor->getAccount(),
                    'form' => $form->createView()
                ));
        }
    }

    //users
    public function usersAction($id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);
        if(!$account || $account->getType() != Account::DISTRIBUTOR)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'account'),'message'));

        $users = $account->getUsers();

        return $this->render('HelloDiMasterBundle:distributor:users.html.twig', array(
                'account' => $account,
                'users' => $users,
            ));
    }

    public function usersAddAction(Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);
        if(!$account || $account->getType() != Account::DISTRIBUTOR)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'account'),'message'));

        $user = new User();

        $languages = $this->container->getParameter('languages');

        $form = $this->createForm(new RegistrationFormType($languages, Account::DISTRIBUTOR), $user)
            ->add('submit','submit', array(
                    'label'=>'Add','translation_domain'=>'common',
                    'attr'=>array('first-button')
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('onclick'=>'window.location.assign("'.$this->generateUrl('hello_di_master_distributor_users', array('id' => $id)).'")','last-button')
                ))
        ;

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $user->setEntity($account->getEntity());
                $user->setAccount($account);
                $user->setEnabled(1);
                $em->persist($user);
                $em->flush();
//                $this->forward('hello_di_di_notification:NewAction',array('id'=>$account->getId(),'type'=>21,'value'=>$user->getUsername()));

                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl('hello_di_master_distributor_users', array('id' => $id)));
            }
        }

        return $this->render('HelloDiMasterBundle:distributor:usersAdd.html.twig', array(
                'account' => $account,
                'form' => $form->createView(),
            ));
    }

    public function usersEditAction(Request $request, $user_id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('HelloDiCoreBundle:User')->find($user_id);

        if(!$user || $user->getAccount()->getType() != Account::DISTRIBUTOR)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'user'),'message'));

        $languages = $this->container->getParameter('languages');

        $form = $this->createForm(new RegistrationFormType($languages, Account::DISTRIBUTOR), $user)
            ->remove('plainPassword')
            ->add('submit','submit', array(
                    'label'=>'Update','translation_domain'=>'common',
                    'attr'=>array('first-button')
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('onclick'=>'window.location.assign("'.$this->generateUrl('hello_di_master_distributor_users', array('id' => $user->getAccount()->getId())).'")','last-button')
                ))
        ;

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->flush();

                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl('hello_di_master_distributor_users', array('id' => $user->getAccount()->getId())));
            }
        }

        return $this->render('HelloDiMasterBundle:distributor:usersEdit.html.twig', array(
                'account' => $user->getAccount(),
                'form' => $form->createView()
            ));
    }

    //info
    public function infoAction(Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $distributor = $em->getRepository('HelloDiDistributorBundle:Distributor')->findByAccountId($id);
        if(!$distributor)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'account'),'message'));

        $form = $this->createFormBuilder(array(
                'terms' => $distributor->getAccount()->getTerms(),
                'timezone' => $distributor->getTimezone(),
            ))
            ->add('terms','text',array(
                    'label' => 'Terms','translation_domain' => 'accounts',
                    'required'=>false,
                    'attr'=> array('class'=>'integer_validation'),
                ))
            ->add('timezone','timezone',array(
                    'label' => 'TimeZone','translation_domain' => 'accounts',
                    'required'=>true,
                ))
            ->add('submit','submit', array(
                    'label'=>'Update','translation_domain'=>'common',
                    'attr'=>array('first-button','last-button')
                ))
            ->getForm();

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();

                $distributor->setTimezone($data['timezone']);
                $distributor->getAccount()->setTerms($data['terms']);

                $em->flush();
//                $this->forward('hello_di_di_notification:NewAction',array('id'=>$Account->getId(),'type'=>26));
                $this->get('session')->getFlashBag()->add('success', 'this operation done success!');
            }
        }

        return $this->render('HelloDiMasterBundle:distributor:info.html.twig', array(
                'form' => $form->createView(),
                'account' => $distributor->getAccount(),
                'distributor' => $distributor,
            ));
    }

    //entity
    public function entityAction($id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $account = $em->getRepository('HelloDiAccountingBundle:Account')->find($id);
        if(!$account)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'account'),'message'));

        return $this->render('HelloDiMasterBundle:distributor:entity.html.twig', array(
                'account' => $account,
                'entity' => $account->getEntity()
            ));

    }
}