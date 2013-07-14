<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Entity\Account;
use HelloDi\DiDistributorsBundle\Entity\DetailHistory;
use HelloDi\DiDistributorsBundle\Entity\User;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistMasterType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountProvType;
use HelloDi\DiDistributorsBundle\Form\Entiti\EntitiesSearchType;
use HelloDi\DiDistributorsBundle\Form\Entiti\EntitiType;
use HelloDi\DiDistributorsBundle\Form\User\NewUserDistributorsRetailerInEntityType;
use HelloDi\DiDistributorsBundle\Form\User\UserRegistrationEntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class EntitiController extends Controller
{
    public function ListEntitiesAction(Request $request)
    {
        $paginator = $this->get('knp_paginator');
        $em = $this->getDoctrine()->getEntityManager();
        $user=$this->get('security.context')->getToken()->getUser();
        $formsearch = $this->createFormBuilder()
            ->add('entName', 'text',array('required'=>false))
            ->add('Country', 'entity', array(
                    'class'=>'HelloDi\DiDistributorsBundle\Entity\Country','property' => 'name',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->orderBy('u.name', 'ASC');
                    })
            )
            ->add('HaveAccount', 'choice',array('choices'=>array(2=>'Both',1=>'Provider',0=>'Distributors')))->getForm()
        ;


        $qb = $em->createQueryBuilder();
                     $qb->select('Ent')
                        ->from('HelloDiDiDistributorsBundle:Entiti','Ent')
                        ->innerJoin('Ent.Accounts', 'EntAcc')
                         ->innerJoin('Ent.Country', 'EntCoun')
                         ->where('Ent != :ent')->setParameter('ent',$user->getEntiti())->getQuery();





        if ($request->isMethod('POST'))
        {

            $formsearch->handleRequest($request);
            $data = $formsearch->getData();

            if ($data['Country']->getName() != 'All') {
                $qb->where('EntCoun.iso=:iso');
                $qb->setParameter('iso', $data['Country']->getIso());
            }
            if ($data['HaveAccount'] != 2)
                $qb->andwhere($qb->expr()->eq('EntAcc.accType', $data['HaveAccount']));
            if ($data['HaveAccount'] == 2) {
                $qb->andwhere($qb->expr()->neq('EntAcc.accType', 2));
            }
            if ($data['entName'] != '') {
                $qb->andwhere($qb->expr()->like('Ent.entName', $qb->expr()->literal($data['entName'] . '%')));
            }

        }


//        die('as'.$qb->getQuery()->getResult());
        $qb = $qb->getQuery();
        $count = count($qb);
        $qb->setHint('knp_paginator.count', $count);


        $pagination = $paginator->paginate(
            $qb,
            $request->get('page',1) /*page number*/,
            10/*limit per page*/
        );

        return $this->render('HelloDiDiDistributorsBundle:Entiti:main.html.twig', array(
            'pagination' => $pagination, 'formsearch' => $formsearch->createView()
        ));

    }


    public function indexAction($id)
    {
        return $this->render("HelloDiDiDistributorsBundle:Entiti:index.html.twig", array(
            'entityid' => $id
        ));
    }


    public function accountsAction(Request $request)
    {
        $id = $request->get('entityid');
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);

        $qb=$em->createQueryBuilder('Ent')
            ->select('Ent')
            ->from("HelloDiDiDistributorsBundle:Entiti",'Ent')
            ->innerJoin('Ent.Accounts','EntAcc')
            ->where('Ent= :ent')->setParameter('ent',$entity)
            ->andWhere('EntAcc.accType = 2')->getQuery();

        return $this->render('HelloDiDiDistributorsBundle:Entiti:accounts.html.twig', array(
              'entity' => $entity,
              'distprov'=>$distprov=(count($qb->getResult())>0)?0:1
        ));


    }


    public function usersAction(Request $request)
    {

        $id = $request->get('entityid');

        $em = $this->getDoctrine()->getManager();

        $paginator = $this->get('knp_paginator');

        $entity = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);

        $Users= $entity->getUsers();

        $pagination = $paginator->paginate(
            $Users,
            $request->get('page',1) /*page number*/,
            6/*limit per page*/
        );
        // die('sas'.count($pagination));
        return $this->render('HelloDiDiDistributorsBundle:Entiti:users.html.twig', array(
            'pagination' => $pagination,
            'entity' => $entity,
        ));
    }


    public function AddNewUserEntitiAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);

        $user = new User();

        $form = $this->createForm(new NewUserDistributorsRetailerInEntityType('HelloDiDiDistributorsBundle\Entity\User',$entity)
        , $user,
         array('cascade_validation' => true)
        );

            $formrole=$this->createFormBuilder()
              ->add('roles','choice',
                    array('choices'=>array
                    (
                        'ROLE_RETAILER'=>'ROLE_RETAILER',
                        'ROLE_RETAILER_ADMIN'=>'ROLE_RETAILER_ADMIN'))
                )->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $formrole->handleRequest($request);
            $data=$formrole->getData();
            $user->addRole($data['roles']);
            if ($form->isValid())
            {
                $user->setEntiti($entity);
                $em->persist($user);
                $em->flush();
                return $this->forward("HelloDiDiDistributorsBundle:Entiti:users", array('entityid' => $id));
            }

        }

        return $this->render('HelloDiDiDistributorsBundle:Entiti:AddNewUserEntiti.html.twig',
            array(
                'entity' => $entity,
                'form' => $form->createView(),
                'formrole'=> $formrole->createView()
            ));

    }


    public function infoAction(Request $request)
    {
        $id = $request->get('entityid');
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);

        $edit_form = $this->createForm(new EntitiType(), $entity);

        return $this->render('HelloDiDiDistributorsBundle:Entiti:info.html.twig', array(
            'edit_form' => $edit_form->createView(),
            'entityid' => $id,
            'entity' => $entity
        ));
    }


    public function infoSubmitAction(Request $request)
    {
        $id = $request->get('entityid');
        $em = $this->getDoctrine()->getManager();

        $Entiti = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);
        $edit_form = $this->createForm(new EntitiType(), $Entiti);

        if ($request->isMethod('POST')) {
            $edit_form->handleRequest($request);
            if ($edit_form->isValid()) {
                $em->flush($Entiti);
                return $this->forward("HelloDiDiDistributorsBundle:Entiti:info");
            }
        }
        return $this->render('HelloDiDiDistributorsBundle:Entiti:info.html.twig',
            array(
            'edit_form' => $edit_form->createView(),
            'entityid' => $id,
            'entity' => $Entiti
        ));

    }

    public function addressesAction(Request $request)
    {
        $id = $request->get('entityid');
        $em = $this->getDoctrine()->getManager();

        $addresses = $em->getRepository('HelloDiDiDistributorsBundle:Address')->findBy(array('Entiti' => $id, 'adrsEnd' => null));

        $entity = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);

        return $this->render('HelloDiDiDistributorsBundle:Entiti:addresses.html.twig', array(
            'addresses' => $addresses, 'entity' => $entity
        ));
    }

    public function AddProvAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);
        $acc = new Account();
        $acc->setEntiti($entity);
        $acc->setAccCreationDate(new \DateTime('now'));
        $acc->setAccBalance(0);
        $acc->setAccType(1);
        $acc->setAccCreditLimit(0);
        $acc->setAccTimeZone(null); /////=========*******

        $form = $this->createForm(new AccountProvType(), $acc);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($acc);
                $em->flush($acc);
                return $this->forward("HelloDiDiDistributorsBundle:Entiti:accounts",
                    array(
                        'entityid' => $id
                    ));
            }
        }
        return $this->render('HelloDiDiDistributorsBundle:Entiti:AddProv.html.twig',
            array('id' => $id,
                  'entity' => $entity,
                  'form' => $form->createView()));
    }

    public function AddDistAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);
        $acc = new Account();
        $acc->setEntiti($entity);
        $acc->setAccCreationDate(new \DateTime('now'));
        $acc->setAccBalance(0);
        $acc->setAccType(0);
        $acc->setAccCreditLimit(0);
        $form = $this->createForm(new AccountDistMasterType(), $acc);

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $em->persist($acc);
                $em->flush($acc);
                return $this->forward("HelloDiDiDistributorsBundle:Entiti:accounts",
                    array('entityid' => $id
                    ));
            }
        }
        return $this->render('HelloDiDiDistributorsBundle:Entiti:AddDist.html.twig',
         array('id' => $id,
                'entity' => $entity,
                'form' => $form->createView()));
    }


public function  EditUserEntitiesAction(Request $request,$id)
{
    $em=$this->getDoctrine()->getManager();

    $user=$em->getRepository('HelloDiDiDistributorsBundle:User')->find($id);

        $form_edit=$this->createForm(New  NewUserDistributorsRetailerInEntityType('HelloDiDiDistributorsBundle\Entity\User',$user->getEntiti()), $user, array('cascade_validation' => true));

    if($request->isMethod('POST'))
{
    $form_edit->handleRequest($request);
    if($form_edit->isValid())
    {

        $em->flush();
        return $this->forward("HelloDiDiDistributorsBundle:Entiti:users",
            array('entityid' => $user->getEntiti()->getId()
            ));

    }
}

    return $this->render('HelloDiDiDistributorsBundle:Entiti:EditUserEntiti.html.twig',
        array('form_edit'=>$form_edit->createView(),
            'entity'=>$user->getEntiti(),
            'User'=>$user
        ));
}


    public function  ChangeRoleUserEntitiesAction($id)
    {
        $em=$this->getDoctrine()->getManager();
        $user=$em->getRepository('HelloDiDiDistributorsBundle:User')->find($id);

        $roles = $user->getRoles();
        $role = $roles[0];

        switch($role){

            case 'ROLE_RETAILER':
             $user->removeRole('ROLE_RETAILER');
             $user->addRole('ROLE_RETAILER_ADMIN');
            break;

            case 'ROLE_RETAILER_ADMIN':
                $user->removeRole('ROLE_RETAILER_ADMIN');
                $user->addRole('ROLE_RETAILER');
            break;

            case 'ROLE_DISTRIBUTOR':
                $user->removeRole('ROLE_DISTRIBUTOR');
                $user->addRole('ROLE_DISTRIBUTOR_ADMIN');
            break;

            case 'ROLE_DISTRIBUTOR_ADMIN':
                $user->removeRole('ROLE_DISTRIBUTOR_ADMIN');
                $user->addRole('ROLE_DISTRIBUTOR');
             break;
         }

     $em->flush();
     return $this->forward("HelloDiDiDistributorsBundle:Entiti:users",
         array('entityid' => $user->getEntiti()->getId()
         ));

            }


    public  function addressAction($id)
    {

        $paginator = $this->get('knp_paginator');
        $em=$this->getDoctrine()->getEntityManager();
        $entity=$em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);
        $Address=$entity->getDetailHistories();



        $pagination = $paginator->paginate(
            $Address,
            $this->get('request')->query->get('page', 1) /*page number*/,
            6/*limit per page*/
        );
        // die('sas'.count($pagination));
        return $this->render('HelloDiDiDistributorsBundle:Entiti:Address.html.twig', array(
            'pagination' => $pagination,
            'entity' => $entity,
        ));

    }

    public  function EditAddressAction(Request $req,$id)
    {

        $em=$this->getDoctrine()->getEntityManager();
        $entity=$em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);

        $DetaHis=new DetailHistory();
        $form=$this->createForm(new EntitiType(),$entity);

        if($req->isMethod('POST'))
        {

            $form->handleRequest($req);
            if($form->isValid($form))
            {
                $DetaHis->setAdrs1($entity->getEntAdrs1());
                $DetaHis->setAdrs2($entity->getEntAdrs2());
                $DetaHis->setAdrs3($entity->getEntAdrs3());
                $DetaHis->setAdrsNp($entity->getEntNp());
                $DetaHis->setAdrsCity($entity->getEntCity());
                $DetaHis->setCountry($entity->getCountry());
                $DetaHis->setEntiti($entity);
                $DetaHis->setAdrsDate(new \DateTime('now'));
                $em->persist($DetaHis);
                $em->flush();
  return $this->redirect($this->generateUrl('Ent_Address',array('id'=>$id)));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Entiti:EditAddress.html.twig', array(
            'form_edit'=>$form->createView(),
            'entity' => $entity,
        ));
    }
}