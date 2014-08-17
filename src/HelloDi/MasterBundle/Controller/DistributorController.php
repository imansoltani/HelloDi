<?php
namespace HelloDi\MasterBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use HelloDi\AccountingBundle\Container\TransactionContainer;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\AccountingBundle\Entity\CreditLimit;
use HelloDi\AccountingBundle\Entity\Transaction;
use HelloDi\AggregatorBundle\Entity\Code;
use HelloDi\AggregatorBundle\Entity\Pin;
use HelloDi\AggregatorBundle\Form\PinType;
use HelloDi\AggregatorBundle\Form\SaleSearchType;
use HelloDi\CoreBundle\Entity\Entity;
use HelloDi\CoreBundle\Entity\Item;
use HelloDi\CoreBundle\Entity\User;
use HelloDi\DistributorBundle\Entity\Distributor;
use HelloDi\MasterBundle\Form\CreditLimitType;
use HelloDi\MasterBundle\Form\DistributorAccountUserType;
use HelloDi\MasterBundle\Form\EntityType;
use HelloDi\MasterBundle\Form\TransactionType;
use HelloDi\RetailerBundle\Entity\Retailer;
use HelloDi\UserBundle\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

        $account = $em->getRepository('HelloDiAccountingBundle:Account')->findOneBy(array('id'=>$id,'type'=>Account::DISTRIBUTOR));
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
            ->add('page', 'hidden', array('required' => false, 'attr'=>array('class'=>'search_page')))
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
            $form->get('page')->getData()?:1,
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

    //sale
    public function salesAction(Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $distributor = $em->getRepository('HelloDiDistributorBundle:Distributor')->findByAccountId($id);
        if(!$distributor)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'account'),'message'));

        $form_select = $this->createForm(new PinType(), new Pin());

        $form = $this->createForm(new SaleSearchType($distributor->getAccount()), null, array(
                'attr' => array('class' => 'SearchForm'),
                'method' => 'get',
            ))
            ->add('search','submit', array(
                    'label'=>'Search','translation_domain'=>'common',
                ));

        $form->handleRequest($request);

        $qb = $em->createQueryBuilder()
            ->select('code as code_row, pin, item, transaction, commissioner_transaction, ret_account, dist_account')
            ->from('HelloDiAggregatorBundle:Code', 'code')
            ->innerJoin('code.item', 'item')
            ->innerJoin('code.pins', 'pin')
            ->innerJoin('pin.transaction', 'transaction')
            ->innerJoin('transaction.account', 'ret_account')
            ->innerJoin('pin.commissionerTransaction', 'commissioner_transaction')
            ->innerJoin('commissioner_transaction.account', 'dist_account')
            ->where('dist_account = :dist_account')->setParameter('dist_account', $distributor->getAccount())
//            ->andWhere('pin.type = :type')->setParameter('type', Pin::SALE)
//            ->orderBy('code.id asc, pin.date desc')

//            ->leftJoin('code.pins', 'pin_credit_note','with','pin_credit_note.type = :type1')
//            ->setParameter('type1', Pin::CREDIT_NOTE)
//            ->leftJoin('pin_credit_note.transaction', 'transaction_credit_note', 'with', 'transaction_credit_note.account = transaction.account')
//
//            ->having('count(pin) > count (pin_credit_note)')

            ->orderBy('pin.id', 'desc');

        $group = false;

        if($form->isValid())
        {
            $form_data = $form->getData();

            $group = in_array(1, $form_data['group_by']);

            if(isset($form_data['itemType']))
                $qb->andWhere('item.type = :item_type')->setParameter('item_type', $form_data['itemType']);

            if(isset($form_data['item']))
                $qb->andWhere('item = :item')->setParameter('item', $form_data['item']);

            if(isset($form_data['retailer'])) {
                /** @var Retailer $retailer */
                $retailer = $form_data['retailer'];
                $qb->andWhere('ret_account = :ret_account')->setParameter('ret_account', $retailer->getAccount());
            }

            if(isset($form_data['from']))
                $qb->andWhere('pin.date >= :from')->setParameter('from', $form_data['from']);

            if(isset($form_data['to']))
                $qb->andWhere('pin.date <= :to')->setParameter('to', $form_data['to']);

            if($group)
                $qb ->addSelect('count(code.id) as quantity, DATE(pin.date) AS groupDate, sum(transaction.amount) as sum_retailer, sum(commissioner_transaction.amount) as sum_distributor')
                    ->groupBy('groupDate, item, ret_account');
        }

        $sales = $this->get('knp_paginator')->paginate($qb->getQuery()->getResult(), $request->get('page', 1), 20);

        return $this->render('HelloDiMasterBundle:distributor:sales.html.twig', array(
                'account' => $distributor->getAccount(),
                'distributor' => $distributor,
                'sales' => $sales,
                'form' => $form->createView(),
                'group' => $group,
                'form_select' => $form_select
            ));
    }

    public function CreditNoteAction(Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $distributor = $em->getRepository('HelloDiDistributorBundle:Distributor')->findByAccountId($id);
        if(!$distributor)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'account'),'message'));

        $pin = new Pin();
        $pin->setUser($this->getUser());

        $form_select = $this->createForm(new PinType(), $pin);

        $form_select->handleRequest($request);
        if ($form_select->isValid()) {
            try {
                $pin = $this->get('aggregator')->creditNoteCodes($pin, $distributor->getAccount());

                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully. '.$pin->getCount(). " codes credit noted by ".$pin->getTransaction()->getAmount().".", array(), 'message'));
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans($e->getMessage(), array(), 'message'));
            }
        }
        else
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('the_operation_failed', array(), 'message'));

        return $this->redirect($this->getRequest()->headers->get('referer'));
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
            ->add('search','submit', array(
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

        $account = $em->getRepository('HelloDiAccountingBundle:Account')->findOneBy(array('id'=>$id, 'type'=>Account::DISTRIBUTOR));
        if(!$account)
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

        $account = $em->getRepository('HelloDiAccountingBundle:Account')->findOneBy(array('id'=>$id, 'type'=>Account::DISTRIBUTOR));
        if(!$account)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'account'),'message'));

        $user = new User();

        $languages = $this->container->getParameter('languages');

        $form = $this->createForm(new RegistrationFormType($languages, Account::DISTRIBUTOR), $user)
            ->add('add','submit', array(
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
            ->add('update','submit', array(
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

        $languages = $this->container->getParameter('languages');
        $languages = array_combine($languages, $languages);

        $form = $this->createFormBuilder(array(
                'terms' => $distributor->getAccount()->getTerms(),
                'timezone' => $distributor->getTimezone(),
                'defaultLanguage' => $distributor->getAccount()->getDefaultLanguage(),
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
            ->add('defaultLanguage','choice',array(
                    'label' => 'DefaultLanguage','translation_domain' => 'accounts',
                    'choices'=>$languages,
                    'required'=>true,
                ))
            ->add('update','submit', array(
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
                $distributor->getAccount()->setDefaultLanguage($data['defaultLanguage']);

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

        $account = $em->getRepository('HelloDiAccountingBundle:Account')->findOneBy(array('id'=>$id, 'type'=>Account::DISTRIBUTOR));
        if(!$account)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'account'),'message'));

        return $this->render('HelloDiMasterBundle:distributor:entity.html.twig', array(
                'account' => $account,
                'entity' => $account->getEntity()
            ));

    }
}