<?php

namespace HelloDi\MasterBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\CoreBundle\Entity\Entity;
use HelloDi\AggregatorBundle\Entity\Provider;
use HelloDi\CoreBundle\Entity\User;
use HelloDi\DistributorBundle\Entity\Distributor;
use HelloDi\MasterBundle\Form\DistributorAccountUserType;
use HelloDi\MasterBundle\Form\EntitySearchType;
use HelloDi\MasterBundle\Form\EntityType;
use HelloDi\MasterBundle\Form\ProviderAccountUserType;
use HelloDi\UserBundle\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class EntityController extends Controller
{
    public function indexAction(Request $request)
    {
        $form = $this->createForm(new EntitySearchType(), null, array(
                'attr' => array('class' => 'SearchForm'),
                'method' => 'get',
            ))
            ->add('search','submit', array(
                    'label'=>'Search','translation_domain'=>'common',
                ));

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder()
            ->select('entity')
            ->from('HelloDiCoreBundle:Entity','entity')
            ->LeftJoin('entity.accounts','account')
            ->where('entity != :my_entity')->setParameter('my_entity', $this->getUser()->getEntity())
        ;

        $form->submit($request->query->all());

        $data = $form->getData();

        if($form->isValid())
        {
            if (isset($data['country']))
                $qb->andwhere('entity.country = :country')->setParameter('country', $data['country']);

            if (isset($data['entityName']))
                $qb->andwhere('entity.name like :name')->setParameter("name", $data['entityName'] . '%');

            if (count($data['accountTypes'])>0)
                $qb->andwhere('account.type in (:types)')->setParameter("types", implode(',', $data['accountTypes']));
        }

        $result = array();
        foreach ($qb->getQuery()->getResult() as $entity) {
            /** @var Entity $entity */
            $row_result['entity'] = $entity;
            $row_result['api_count'] = 0;
            $row_result['provider_count'] = 0;
            $row_result['distributor_count'] = 0;
            $row_result['retailer_count'] = 0;

            foreach ($entity->getAccounts() as $account) {
                /** @var Account $account */
                switch ($account->getType()) {
                    case Account::API: $row_result['api_count']++; break;
                    case Account::PROVIDER: $row_result['provider_count']++; break;
                    case Account::DISTRIBUTOR: $row_result['distributor_count']++; break;
                    case Account::RETAILER: $row_result['retailer_count']++; break;
                }
            }
            $result[] = $row_result;
        }

        $entities = $this->get('knp_paginator')->paginate($result, $request->get('page', 1), 10);

        return $this->render('HelloDiMasterBundle:entity:index.html.twig', array(
                'entities' => $entities,
                'form' => $form->createView(),
        ));
    }

    //accounts
    public function accountsAction($id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HelloDiCoreBundle:Entity')->find($id);
        if (!$entity || $entity == $this->getUser()->getEntity())
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>$this->get('translator')->trans('Entity',array(),'entity')),'message'));

        $api_s = $em->createQueryBuilder()
            ->select('api')
            ->from('HelloDiCoreBundle:Api', 'api')
            ->innerJoin('api.account', 'account')
            ->where('account.entity = :entity')->setParameter('entity', $entity)
            ->getQuery()->getResult();

        $providers = $em->createQueryBuilder()
            ->select('provider')
            ->from('HelloDiAggregatorBundle:Provider', 'provider')
            ->innerJoin('provider.account', 'account')
            ->where('account.entity = :entity')->setParameter('entity', $entity)
            ->getQuery()->getResult();

        $distributors = $em->createQueryBuilder()
            ->select('distributor')
            ->from('HelloDiDistributorBundle:Distributor', 'distributor')
            ->innerJoin('distributor.account', 'account')
            ->where('account.entity = :entity')->setParameter('entity', $entity)
            ->getQuery()->getResult();

        $retailers = $em->createQueryBuilder()
            ->select('retailer')
            ->from('HelloDiRetailerBundle:Retailer', 'retailer')
            ->innerJoin('retailer.account', 'account')
            ->where('account.entity = :entity')->setParameter('entity', $entity)
            ->getQuery()->getResult();

        return $this->render('HelloDiMasterBundle:entity:accounts.html.twig', array(
                'entity' => $entity,
                'api_s' => $api_s,
                'providers' => $providers,
                'distributors' => $distributors,
                'retailers' => $retailers
        ));
    }

    public function newProviderAction(Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HelloDiCoreBundle:Entity')->find($id);
        if (!$entity || $entity == $this->getUser()->getEntity())
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>$this->get('translator')->trans('Entity',array(),'entity')),'message'));

        $provider = new Provider();

        $account = new Account();
        $account->setCreationDate(new \DateTime('now'));
        $account->setType(Account::PROVIDER);
        $provider->setAccount($account);
        $entity->addAccount($account);
        $account->setEntity($entity);

        $currencies = $this->container->getParameter('Currencies.Account');
        $languages = $this->container->getParameter('languages');

        $form = $this->createForm(new ProviderAccountUserType($currencies,$languages), $provider, array('cascade_validation' => true))
            ->add('add','submit', array(
                    'label'=>'Add','translation_domain'=>'common',
                    'attr'=>array('first-button')
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('onclick'=>'window.location.assign("'.$this->generateUrl('hello_di_master_entity_accounts_index',array('id' => $id)).'")','last-button')
                ))
        ;

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($account);
                $em->persist($provider);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl('hello_di_master_entity_accounts_index',array('id'=>$id)));
            }
        }

        return $this->render('HelloDiMasterBundle:entity:newProvider.html.twig', array(
                'entity' => $entity,
                'form' => $form->createView()
            ));
    }

    public function newDistributorAction(Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HelloDiCoreBundle:Entity')->find($id);
        if (!$entity || $entity == $this->getUser()->getEntity())
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>$this->get('translator')->trans('Entity',array(),'entity')),'message'));

        $distributor = new Distributor();

        $account = new Account();
        $account->setCreationDate(new \DateTime('now'));
        $account->setType(Account::DISTRIBUTOR);
        $distributor->setAccount($account);
        $entity->addAccount($account);
        $account->setEntity($entity);

        $currencies = $this->container->getParameter('Currencies.Account');
        $languages = $this->container->getParameter('languages');

        $form = $this->createForm(new DistributorAccountUserType($currencies,$languages), $distributor, array('cascade_validation' => true))
            ->add('add','submit', array(
                    'label'=>'Add','translation_domain'=>'common',
                    'attr'=>array('first-button')
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('onclick'=>'window.location.assign("'.$this->generateUrl('hello_di_master_entity_accounts_index',array('id' => $id)).'")','last-button')
                ))
        ;

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($account);
                $em->persist($distributor);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl('hello_di_master_entity_accounts_index',array('id'=>$id)));
            }
        }

        return $this->render('HelloDiMasterBundle:entity:newDistributor.html.twig', array(
                'entity' => $entity,
                'form' => $form->createView()
            ));
    }

    //users
    public function usersAction($id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HelloDiCoreBundle:Entity')->find($id);
        if (!$entity || $entity == $this->getUser()->getEntity())
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>$this->get('translator')->trans('Entity',array(),'entity')),'message'));

        $countNotProvider = $em->createQueryBuilder()
            ->select('count(account)')
            ->from('HelloDiAccountingBundle:Account', 'account')
            ->where('account.entity = :entity')->setParameter('entity', $entity)
            ->andWhere('account.type != :type')->setParameter('type', Account::PROVIDER)
            ->getQuery()->getSingleResult();

        $haveOnlyProvider = $countNotProvider[1] == 0;

        return $this->render('HelloDiMasterBundle:entity:users.html.twig', array(
                'entity' => $entity,
                'haveOnlyProvider' => $haveOnlyProvider,
        ));
    }

    public function NewUserAction(Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HelloDiCoreBundle:Entity')->find($id);
        if (!$entity || $entity == $this->getUser()->getEntity())
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>$this->get('translator')->trans('Entity',array(),'entity')),'message'));

        $accountType = $em->getRepository('HelloDiAccountingBundle:Account')->findOneBy(array(
                'entity' => $entity,
                'type' => Account::RETAILER
            )) ? Account::RETAILER : Account::DISTRIBUTOR;

        $languages = $this->container->getParameter('languages');

        $user = new User();

        $form = $this->createForm(new RegistrationFormType($languages, $accountType), $user, array('cascade_validation' => true))
            ->add('account', 'entity', array(
                    'class' => 'HelloDiAccountingBundle:Account', 'property' => 'name',
                    'required' => true,
                    'query_builder' => function(EntityRepository $er)use ($entity) {
                            return $er->createQueryBuilder('u')
                                ->where('u.type != :type')->setParameter('type', Account::PROVIDER)
                                ->andwhere('u.entity=:ent')->setParameter('ent',$entity)
                                ;
                        },
                ))
            ->add('add','submit', array(
                    'label'=>'Add','translation_domain'=>'common',
                    'attr'=>array('first-button')
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('onclick'=>'window.location.assign("'.$this->generateUrl('hello_di_master_entity_users_index',array('id' => $id)).'")','last-button')
                ))
        ;

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $user->setEntity($entity);
                $entity->addAccount($user->getAccount());

                $em->persist($user->getAccount());
                $em->persist($user);
                $em->flush();

//              if($user->getAccount()->getType()==0)
//                $this->forward('hello_di_di_notification:NewAction',array('id'=>$user->getAccount()->getId(),'type'=>21,'value'=>$user->getUsername()));
//              else
//                 $this->forward('hello_di_di_notification:NewAction',array('id'=>$user->getAccount()->getId(),'type'=>37,'value'=>$user->getUsername()));

                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl('hello_di_master_entity_users_index',array('id'=>$id)));
            }
        }

        return $this->render('HelloDiMasterBundle:entity:newUser.html.twig', array(
                'entity' => $entity,
                'form' => $form->createView(),
            ));
    }

    public function EditUserAction(Request $request, $user_id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('HelloDiCoreBundle:User')->find($user_id);
        if (!$user || $user->getEntity() == $this->getUser()->getEntity())
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>$this->get('translator')->trans('User',array(),'user')),'message'));

        $entity = $user->getEntity();

        $accountType = $em->getRepository('HelloDiAccountingBundle:Account')->findOneBy(array(
                'entity' => $entity,
                'type' => Account::RETAILER
            )) ? Account::RETAILER : Account::DISTRIBUTOR;

        $languages = $this->container->getParameter('languages');

        $form = $this->createForm(new RegistrationFormType($languages, $accountType), $user, array('cascade_validation' => true))
            ->remove('plainPassword')
            ->add('account', 'entity', array(
                    'class' => 'HelloDiAccountingBundle:Account', 'property' => 'name',
                    'required' => true,
                    'query_builder' => function(EntityRepository $er)use ($entity) {
                            return $er->createQueryBuilder('u')
                                ->where('u.type != :type')->setParameter('type', Account::PROVIDER)
                                ->andwhere('u.entity=:ent')->setParameter('ent',$entity)
                                ;
                        },
                ))
            ->add('update','submit', array(
                    'label'=>'Update','translation_domain'=>'common',
                    'attr'=>array('first-button')
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('onclick'=>'window.location.assign("'.$this->generateUrl('hello_di_master_entity_users_index',array('id' => $entity->getId())).'")','last-button')
                ))
        ;

        if($request->isMethod('POST')) {
            $form->handleRequest($request);

            if($form->isValid())
            {
                $em->flush();

                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl('hello_di_master_entity_users_index',array('id' => $entity->getId())));
            }
        }

        return $this->render('HelloDiMasterBundle:entity:editUser.html.twig', array(
                'form' => $form->createView(),
                'entity' => $entity,
            ));
    }

    //info
    public function infoAction(Request $request,$id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HelloDiCoreBundle:Entity')->find($id);
        if (!$entity || $entity == $this->getUser()->getEntity())
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>$this->get('translator')->trans('Entity',array(),'entity')),'message'));

        $form = $this->createForm(new EntityType(), $entity)
            ->add('update','submit', array(
                    'label'=>'Update','translation_domain'=>'common',
                    'attr'=>array('first-button','last-button')
                ))
        ;

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->flush($entity);

//                foreach($entity->getAccounts() as $Account) {
//                    if($Account->getAccType()==0)
//                        $this->forward('hello_di_di_notification:NewAction',array('id'=>$Account->getId(),'type'=>27));
//                    elseif($Account->getAccType()==2)
//                        $this->forward('hello_di_di_notification:NewAction',array('id'=>$Account->getId(),'type'=>36));
//                }

                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
            }
        }

        return $this->render('HelloDiMasterBundle:entity:info.html.twig', array(
                'form' => $form->createView(),
                'entity' => $entity
            ));
    }
}