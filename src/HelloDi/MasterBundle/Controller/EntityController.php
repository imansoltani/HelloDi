<?php

namespace HelloDi\MasterBundle\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\CoreBundle\Entity\Entity;
use HelloDi\CoreBundle\Entity\Provider;
use HelloDi\CoreBundle\Entity\User;
use HelloDi\DiDistributorsBundle\Form\Entiti\EditEntitiType;
use HelloDi\DiDistributorsBundle\Form\User\NewUserDistributorsRetailerInEntityType;
use HelloDi\DistributorBundle\Entity\Distributor;
use HelloDi\MasterBundle\Form\DistributorAccountUserType;
use HelloDi\MasterBundle\Form\EntitySearchType;
use HelloDi\MasterBundle\Form\ProviderAccountUserType;
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
            ->add('submit','submit', array(
                    'label'=>'Search','translation_domain'=>'common',
                ));

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder()
            ->select('entity as entity_row', 'account.type as type', 'count(account) as type_count')
            ->from('HelloDiCoreBundle:Entity','entity')
            ->innerJoin('entity.accounts','account')
            ->groupBy('account.type','entity.id')
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
        $row_result = null;
        foreach ($qb->getQuery()->getResult() as $row) {
            if($row_result == null || $row_result['entity']->getId() != $row['entity_row']->getId()) {
                if ($row_result) $result [] = $row_result;
                $row_result['entity'] = $row['entity_row'];
                $row_result['provider_count'] = 0;
                $row_result['distributor_count'] = 0;
                $row_result['retailer_count'] = 0;
                $row_result['api_count'] = 0;
            }
            switch ($row['type']) {
                case Account::PROVIDER: $row_result['provider_count'] = $row['type_count']; break;
                case Account::DISTRIBUTOR: $row_result['distributor_count'] = $row['type_count']; break;
                case Account::RETAILER: $row_result['retailer_count'] = $row['type_count']; break;
                case Account::API: $row_result['api_count'] = $row['type_count']; break;
            }
        }
        if ($row_result) $result [] = $row_result;

        $entities = $this->get('knp_paginator')->paginate($result, $request->get('page', 1), 10);

        return $this->render('HelloDiMasterBundle:entity:index.html.twig', array(
            'entities' => $entities,
            'form' => $form->createView()
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
            ->from('HelloDiCoreBundle:Provider', 'provider')
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
            ->add('submit','submit', array(
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
            ->add('submit','submit', array(
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
    public function usersAction(Request $req,$id)
    {


        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);

        return $this->render('HelloDiDiDistributorsBundle:Entiti:users.html.twig', array(
            'pagination' =>$entity->getUsers(),
            'entity' => $entity,
        ));
    }

    public function AddNewUserEntitiAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);

        $user = new User();

        if (count($em->getRepository('HelloDiAccountingBundle:Account')->findBy(array(
                    'Entiti' => $entity,
                    'accType' => 2))
            ) ==1
        )
        {

            $form = $this->createForm(new NewUserDistributorsRetailerInEntityType
                ('HelloDiDiDistributorsBundle\Entity\User',$entity,2)
                , $user,
                array('cascade_validation' => true)
            );
        }


        else
        {

            $form = $this->createForm(new NewUserDistributorsRetailerInEntityType
                ('HelloDiDiDistributorsBundle\Entity\User',$entity,0)
                , $user,
                array('cascade_validation' => true)
            );
        }

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid())
            {
                $user->setEntiti($entity);
                $em->persist($user);
                $em->flush();

              if($user->getAccount()->getAccType()==0)
                $this->forward('hello_di_di_notification:NewAction',array('id'=>$user->getAccount()->getId(),'type'=>21,'value'=>$user->getUsername()));
              else
                 $this->forward('hello_di_di_notification:NewAction',array('id'=>$user->getAccount()->getId(),'type'=>37,'value'=>$user->getUsername()));

                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl('Ent_Users',array('id'=>$id)));
            }

        }

        return $this->render('HelloDiDiDistributorsBundle:Entiti:AddNewUserEntiti.html.twig',
            array(
                'entity' => $entity,
                'form' => $form->createView(),
            ));

    }

    public function  EditUserEntitiesAction(Request $request,$userid)
{
    $em=$this->getDoctrine()->getManager();

    $user=$em->getRepository('HelloDiDiDistributorsBundle:User')->find($userid);


    if (count($em->getRepository('HelloDiAccountingBundle:Account')->findBy(array(
                'Entiti' => $user->getEntiti(),
                'accType' => 2))
        ) ==1
    )
    {

        $form_edit = $this->createForm(new NewUserDistributorsRetailerInEntityType
            ('HelloDiDiDistributorsBundle\Entity\User',$user->getEntiti(),$type=2)
            , $user,
            array('cascade_validation' => true)
        );
    }


    else
    {

        $form_edit = $this->createForm(new NewUserDistributorsRetailerInEntityType
            ('HelloDiDiDistributorsBundle\Entity\User',$user->getEntiti(),$type=0)
            , $user,
            array('cascade_validation' => true)
        );
    }



    if($request->isMethod('POST'))
{
    $form_edit->handleRequest($request);
    if($form_edit->isValid())
    {
        $em->flush();
        $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
    }
}

    return $this->render('HelloDiDiDistributorsBundle:Entiti:EditUserEntiti.html.twig',
        array(
            'form_edit'=>$form_edit->createView(),
            'entity'=>$user->getEntiti(),
            'User'=>$user
        ));
}

    //info
    public function infoAction(Request $request,$id)
    {

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);

        $edit_form = $this->createForm(new EditEntitiType(), $entity);

        if ($request->isMethod('POST')) {

            $edit_form->handleRequest($request);

            if ($edit_form->isValid()) {

                $em->flush($entity);

                foreach($entity->getAccounts() as $Account)
                {
                    if($Account->getAccType()==0)
                        $this->forward('hello_di_di_notification:NewAction',array('id'=>$Account->getId(),'type'=>27));
                    elseif($Account->getAccType()==2)
                        $this->forward('hello_di_di_notification:NewAction',array('id'=>$Account->getId(),'type'=>36));

                }

                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Entiti:info.html.twig', array(
                'edit_form' => $edit_form->createView(),
                'entityid' => $id,
                'entity' => $entity
            ));
    }
}