<?php
namespace HelloDi\MasterBundle\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\AccountingBundle\Container\TransactionContainer;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\AccountingBundle\Entity\Transaction;
use HelloDi\AggregatorBundle\Form\InputSearchType;
use HelloDi\CoreBundle\Entity\Entity;
use HelloDi\AggregatorBundle\Entity\Input;
use HelloDi\AggregatorBundle\Entity\Provider;
use HelloDi\DistributorBundle\Entity\Distributor;
use HelloDi\MasterBundle\Form\EntityType;
use HelloDi\MasterBundle\Form\InputType;
use HelloDi\MasterBundle\Form\ProviderAccountUserType;
use HelloDi\MasterBundle\Form\TransactionType;
use HelloDi\MasterBundle\Form\TransferType;
use HelloDi\PricingBundle\Entity\Price;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class ProviderController extends Controller
{
    public function indexAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $providers = $em->createQueryBuilder()
            ->select('provider', 'account', 'entity')
            ->from('HelloDiAggregatorBundle:Provider', 'provider')
            ->innerJoin('provider.account', 'account')
            ->innerJoin('account.entity', 'entity')
            ->getQuery()->getResult();
        ;

        return $this->render('HelloDiMasterBundle:provider:index.html.twig', array(
            'providers' => $providers
        ));
    }

    public function addAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $provider = new Provider();

        $account = new Account();
        $account->setCreationDate(new \DateTime('now'));
        $account->setType(Account::PROVIDER);
        $provider->setAccount($account);

        $entity = new Entity();
        $account->setEntity($entity);
        $entity->addAccount($account);

        $currencies = $this->container->getParameter('currencies.account');
        $languages = $this->container->getParameter('languages');
        $countries = $this->container->getParameter('countries');

        $form = $this->createForm(new ProviderAccountUserType($currencies,$languages), $provider, array('cascade_validation' => true));
        $form->get('account')->add('entity',new EntityType($countries));

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
    {//TODO must be edit
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $account = $em->getRepository('HelloDiAccountingBundle:Account')->findOneBy(array('id'=>$id,'type'=>Account::PROVIDER));
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

        return $this->render('HelloDiMasterBundle:provider:transaction.html.twig', array(
            'transactions' => $transactions,
            'account' => $account,
            'form' => $form->createView()
        ));
    }

    public function transactionRegisterAction($id, Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $provider = $em->getRepository('HelloDiAggregatorBundle:Provider')->findByAccountId($id);

        if(!$provider)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'Provider'),'message'));

        $transaction = new Transaction();
        $form = $this->createForm(new TransactionType($provider->getCurrency()),$transaction,array('attr'=>array(
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
                $beforeBalance = $provider->getAccount()->getBalance();

                $result = $this->get('accounting')->processTransaction(array(new TransactionContainer(
                        $provider->getAccount(),
                        $transaction->getAmount(),
                        $transaction->getDescription(),
                        0.0,
                        $transaction->getFees()
                )));

                if($result)
                {
                    $this->get('session')->getFlashBag()->add('success',
                        $this->get('translator')->trans('Provider_account_was_changed_from_%alredyprov%_to_%currentprov%',
                            array('alredyprov'=>$beforeBalance,'currentprov'=>$provider->getAccount()->getBalance()),
                            'message')
                    );
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
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $provider = $em->getRepository('HelloDiAggregatorBundle:Provider')->findByAccountId($id);

        if(!$provider)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'Provider'),'message'));

        $form = $this->createForm(new TransferType($provider) ,null, array('attr' => array(
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

                /** @var Distributor $distributor */
                $distributor =  $data['distributor'];

                $result = $this->get('accounting')->processTransfer(
                    $data['amount'],
                    $this->getUser(),
                    $distributor->getAccount(),
                    $data['descriptionForOrigin'],
                    $data['descriptionForDestination']
                );

                if($result)
                {
                    $beforeDistributorBalance = $distributor->getAccount()->getBalance();
                    $beforeProviderBalance = $provider->getAccount()->getBalance();

                    $this->get('session')->getFlashBag()->add('success',
                        $this->get('translator')->trans('Distributor_account_was_changed_from_%alredydist%_to_%currentdist%',
                            array('alredydist'=>$beforeDistributorBalance,'currentdist'=>$distributor->getAccount()->getBalance()),
                            'message')
                    );


                    $this->get('session')->getFlashBag()->add('success',
                        $this->get('translator')->trans('Provider_account_was_changed_from_%alredyprov%_to_%currentprov%',
                            array('alredyprov'=>$beforeProviderBalance,'currentprov'=>$provider->getAccount()->getBalance()),
                            'message')
                    );

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
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $provider = $em->getRepository('HelloDiAggregatorBundle:Provider')->findByAccountId($id);
        if(!$provider)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'account'),'message'));

        $prices = $em->createQueryBuilder()
            ->select('price', 'item', 'operator')
            ->from('HelloDiPricingBundle:Price', 'price')
            ->where('price.account = :account')->setParameter('account', $provider->getAccount())
            ->innerJoin('price.item', 'item')
            ->innerJoin('item.operator', 'operator')
            ->getQuery()->getResult();

        return $this->render('HelloDiMasterBundle:provider:items.html.twig', array(
                'account' => $provider->getAccount(),
                'provider' => $provider,
                'prices' => $prices
            ));
    }

    public function uploadAction(Request $request, $price_id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $price = $em->getRepository('HelloDiPricingBundle:Price')->find($price_id);
        if(!$price)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'item'),'message'));

        $provider = $em->getRepository('HelloDiAggregatorBundle:Provider')->findOneBy(array('account'=>$price->getAccount()));

        $input = new Input();
        $input->setItem($price->getItem());
        $input->setDateProduction(new \DateTime());
        $input->setDateExpiry(new \DateTime());

        $form = $this->createForm(new InputType(), $input,array('attr'=>array(
                'class' => 'YesNoMessage',
                'header' => $this->get('translator')->trans('Upload',array(),'code'),
                'message' => $this->get('translator')->trans('Are_you_sure_you_perform_this_operation?',array(),'message'),
            )))
            ->add('update','submit', array(
                    'label'=>'Upload','translation_domain'=>'code',
                    'attr'=>array('first-button')
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('onclick'=>'window.location.assign("'.$this->generateUrl('hello_di_master_provider_items', array('id' => $price->getAccount()->getId())).'")','last-button')
                ));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $input->setDateInsert(new \DateTime());
                $input->setProvider($provider);
                $input->setUser($this->getUser());
                $input->upload();

                try {
                    $delimiter = $form->get('delimiter')->getData();
                    $count = $this->get('aggregator')->testFileCodes($input, $delimiter);

                    $this->get('aggregator')->clearUploadInSession($this->get("session"));
                    $this->get("session")->set('last_upload', $input);
                    $this->get("session")->set('last_upload_delimiter', $delimiter);

                    return $this->render('HelloDiMasterBundle:provider:uploadSubmit.html.twig', array(
                            'account' => $price->getAccount(),
                            'input' => $input,
                            'count' => $count,
                            'price' => $price
                        ));

                } catch(\Exception $e) {
                    $form->get('file')->addError(new FormError($e->getMessage()));
                    $input->removeUpload();
                }
            }
        }

        return $this->render('HelloDiMasterBundle:provider:upload.html.twig', array(
                'account' => $price->getAccount(),
                'price' => $price,
                'form' => $form->createView(),
            ));
    }

    public function uploadAcceptedAction($price_id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var Input $input */
        $input = $this->get("session")->get('last_upload');
        if(!$input)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'upload'),'message'));

        $input->setItem($em->getRepository('HelloDiCoreBundle:Item')->find($input->getItem()->getId()));
        $input->setUser($em->getRepository('HelloDiCoreBundle:User')->find($input->getUser()->getId()));
        $input->setProvider($em->getRepository('HelloDiAggregatorBundle:Provider')->find($input->getProvider()->getId()));

        $delimiter = $this->get("session")->get('last_upload_delimiter');

        if($input->getUser() != $this->getUser()) {
            $this->get('aggregator')->clearUploadInSession($this->get("session"));
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'upload'),'message'));
        }

        try {
            $this->get('aggregator')->buyingCodes($input, $delimiter);

            $this->get('aggregator')->clearUploadInSession($this->get("session"));

            $this->get('session')->getFlashBag()->add('success', 'this operation done success!');
            return $this->redirect($this->generateUrl('hello_di_master_provider_items',array(
                        'id'=>$input->getProvider()->getAccount()->getId()
                    )));
        }catch (\Exception $e){
            $this->get('aggregator')->clearUploadInSession($this->get("session"));

            $this->get('session')->getFlashBag()->add('error', 'this operation has error: '.$e->getMessage());
            return $this->redirect($this->generateUrl('hello_di_master_provider_items_upload',array(
                        'id' => $input->getProvider()->getAccount()->getId(),
                        'price_id' => $price_id
                    )));
        }
    }

    //inputs
    public function inputsAction(Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $provider = $em->getRepository('HelloDiAggregatorBundle:Provider')->findByAccountId($id);
        if(!$provider)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'account'),'message'));

        $form = $this->createForm(new InputSearchType($provider->getAccount()), null, array(
                'attr' => array('class' => 'SearchForm'),
                'method' => 'get',
            ))
            ->add('search','submit', array(
                    'label'=>'Search','translation_domain'=>'common',
                ));

        $form->handleRequest($request);

        $qb = $em->createQueryBuilder()
            ->select('input', 'item', 'provider_transaction')
            ->from('HelloDiAggregatorBundle:Input', 'input')
            ->where('input.provider = :provider')->setParameter('provider', $provider)
            ->innerJoin('input.item', 'item')
            ->innerJoin('input.providerTransaction', 'provider_transaction')
            ->orderBy('input.id', 'desc');

        if($form->isValid())
        {
            $form_data = $form->getData();

            if(isset($form_data['from']))
                $qb->andWhere('input.dateInsert >= :from')->setParameter('from', $form_data['from']);

            if(isset($form_data['to']))
                $qb->andWhere('input.dateInsert <= :to')->setParameter('to', $form_data['to']);

            if(isset($form_data['item']))
                $qb->andWhere('item = :item')->setParameter('item', $form_data['item']);
        }

        $inputs = $this->get('knp_paginator')->paginate($qb->getQuery(), $request->get('page', 1), 20);

        return $this->render('HelloDiMasterBundle:provider:inputs.html.twig', array(
                'account' => $provider->getAccount(),
                'provider' => $provider,
                'inputs' => $inputs,
                'form' => $form->createView()
            ));
    }

    //removed
    public function removedAction(Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $provider = $em->getRepository('HelloDiAggregatorBundle:Provider')->findByAccountId($id);
        if(!$provider)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'account'),'message'));

        $form = $this->createForm(new InputSearchType($provider->getAccount()), null, array(
                'attr' => array('class' => 'SearchForm'),
                'method' => 'get',
            ))
            ->add('search','submit', array(
                    'label'=>'Search','translation_domain'=>'common',
                ));

        $form->handleRequest($request);

        $qb = $em->createQueryBuilder()
            ->select('code', 'pin', 'item')
            ->from('HelloDiAggregatorBundle:Code', 'code')
            ->innerJoin('code.pins', 'pin')
            ->innerJoin('pin.transaction', 'transaction')
            ->where('transaction.account = :provider_account')->setParameter('provider_account', $provider->getAccount())
            ->innerJoin('code.item', 'item')
            ->orderBy('pin.id', 'desc');

        if($form->isValid())
        {
            $form_data = $form->getData();

            if(isset($form_data['from']))
                $qb->andWhere('pin.date >= :from')->setParameter('from', $form_data['from']);

            if(isset($form_data['to']))
                $qb->andWhere('pin.date <= :to')->setParameter('to', $form_data['to']);

            if(isset($form_data['item']))
                $qb->andWhere('item = :item')->setParameter('item', $form_data['item']);
        }

        $removed_codes = $this->get('knp_paginator')->paginate($qb->getQuery(), $request->get('page', 1), 20);

        return $this->render('HelloDiMasterBundle:provider:removed.html.twig', array(
                'account' => $provider->getAccount(),
                'provider' => $provider,
                'removed_codes' => $removed_codes,
                'form' => $form->createView()
            ));
    }

    //info
    public function infoAction(Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $provider = $em->getRepository('HelloDiAggregatorBundle:Provider')->findByAccountId($id);
        if(!$provider)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'account'),'message'));

        $languages = $this->container->getParameter('languages');
        $languages = array_combine($languages, $languages);

        $form = $this->createFormBuilder(array(
                'terms' => $provider->getAccount()->getTerms(),
                'timezone' => $provider->getTimezone(),
                'defaultLanguage' => $provider->getAccount()->getDefaultLanguage(),
                'vat' => $provider->getVat(),
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
            ->add('vat', 'choice', array(
                    'choices'   => array(1 => 'By Country', 0 => 'Set Zero'),
                    'required'  => true,
                    'expanded' => true
                ))
            ->getForm();

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();

                $provider->setTimezone($data['timezone']);
                $provider->getAccount()->setTerms($data['terms']);
                $provider->getAccount()->setDefaultLanguage($data['defaultLanguage']);
                $provider->setVat($data['vat']);

                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'this operation done success!');
            }
        }

        return $this->render('HelloDiMasterBundle:provider:info.html.twig', array(
                'form' => $form->createView(),
                'account' => $provider->getAccount(),
                'provider' => $provider,
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

        return $this->render('HelloDiMasterBundle:provider:entity.html.twig', array(
                'account' => $account,
                'entity' => $account->getEntity()
            ));

    }
}