<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Entity\Account;
use HelloDi\DiDistributorsBundle\Entity\DetailHistory;
use HelloDi\DiDistributorsBundle\Entity\User;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistMasterType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountProvType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountType;
use HelloDi\DiDistributorsBundle\Form\Entiti\EditAddressEntitiType;
use HelloDi\DiDistributorsBundle\Form\Entiti\EditEntitiMasterType;
use HelloDi\DiDistributorsBundle\Form\Entiti\EditEntitiType;
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
            ->add('entName', 'text',array('required'=>false,'label'=>'EntityName','translation_domain' => 'entity'))
            ->add('Country', 'entity', array('label'=>'Country','translation_domain' => 'entity',
                    'class'=>'HelloDi\DiDistributorsBundle\Entity\Country','property' => 'name',
                     'empty_value'=>'All',
                     'empty_data'=>'',
                     'required'=>false
                )
            )
        ->add('Retailer', 'checkbox', array('translation_domain' => 'accounts',
            'label'     => 'Retailer',
            'required'  => false,
        ))
        ->add('Distributors', 'checkbox', array('translation_domain' => 'accounts',
                'label'     => 'Distributors',
                'required'  => false,
            ))
       ->add('Provider', 'checkbox', array('translation_domain' => 'accounts',
                'label'     => 'Provider',
                'required'  => false,
                ))->getForm()
        ;

//die('sdd'.$user->getEntiti()->getId());

        $entity=$this->getUser()->getEntiti();

        $qb = $em->createQueryBuilder();
                     $qb->select('Ent')
                        ->from('HelloDiDiDistributorsBundle:Entiti','Ent')
                         ->innerJoin('Ent.Accounts','EntAcc')
                         ->Where('Ent != :E')->setParameter('E',$user->getEntiti());


        if ($request->isMethod('POST'))
        {

            $formsearch->handleRequest($request);
            $data = $formsearch->getData();

            if ($data['Country'])
                $qb->andwhere('Ent.Country= :cun')->setParameter('cun',$data['Country']);

            if ($data['entName'] != '')
                $qb->andwhere($qb->expr()->like('Ent.entName', $qb->expr()->literal($data['entName'] . '%')));


            if($data['Retailer'])
                $qb->andwhere($qb->expr()->eq('EntAcc.accType',2));
            if($data['Distributors'])
                $qb->andwhere($qb->expr()->eq('EntAcc.accType',0));
            if($data['Provider'])
                $qb->andwhere($qb->expr()->eq('EntAcc.accType',1));

            if($data['Provider'] and $data['Distributors'] and !$data['Retailer'])
            {

             $qb->orWhere($qb->expr()->eq('EntAcc.accType',1));
             $qb->orWhere($qb->expr()->eq('EntAcc.accType',0));

             }
        }
        $qb = $qb->getQuery();
        $count = count($qb->getResult());

        $qb->setHint('knp_paginator.count', $count);


        $pagination = $paginator->paginate(
            $qb,
            $request->get('page',1) /*page number*/,
            10/*limit per page*/
        );

        return $this->render('HelloDiDiDistributorsBundle:Entiti:main.html.twig', array(
            'pagination' => $pagination,
            'formsearch' => $formsearch->createView()
        ));


    }
    public function accountsAction(Request $request,$id)
    {

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

        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);

        $user = new User();

        if (count($em->getRepository('HelloDiDiDistributorsBundle:Account')->findBy(array(
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
                $this->get('session')->getFlashBag()->add('success','this operation done success !');
                return $this->redirect($this->generateUrl('Ent_Users',array('id'=>$id)));
            }

        }

        return $this->render('HelloDiDiDistributorsBundle:Entiti:AddNewUserEntiti.html.twig',
            array(
                'entity' => $entity,
                'form' => $form->createView(),
            ));

    }


    public function infoAction(Request $request,$id)
    {

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);

        $edit_form = $this->createForm(new EditEntitiType(), $entity);

        if ($request->isMethod('POST')) {

            $edit_form->handleRequest($request);

            if ($edit_form->isValid()) {

                $em->flush($entity);
                $this->get('session')->getFlashBag()->add('success','this operation done success !');
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Entiti:info.html.twig', array(
            'edit_form' => $edit_form->createView(),
            'entityid' => $id,
            'entity' => $entity
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
        $acc->getAccDefaultLanguage(null);

        $form = $this->createForm(new AccountType(), $acc);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($acc);
                $em->flush($acc);
                $this->get('session')->getFlashBag()->add('success','this operation done success !');
                return $this->redirect($this->generateUrl('Ent_Accounts',array('id'=>$id)));
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
        $acc->setAccTerms(null);
        $form = $this->createForm(new AccountType(), $acc);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($acc);
                $em->flush($acc);
                $this->get('session')->getFlashBag()->add('success','this operation done success !');
                return $this->redirect($this->generateUrl('Ent_Accounts',array('id'=>$id)));
            }
        }
        return $this->render('HelloDiDiDistributorsBundle:Entiti:AddDist.html.twig',
         array('id' => $id,
                'entity' => $entity,
                'form' => $form->createView()));
    }


public function  EditUserEntitiesAction(Request $request,$userid)
{
    $em=$this->getDoctrine()->getManager();

    $user=$em->getRepository('HelloDiDiDistributorsBundle:User')->find($userid);


    if (count($em->getRepository('HelloDiDiDistributorsBundle:Account')->findBy(array(
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
        $this->get('session')->getFlashBag()->add('success','this operation done success !');
    }
}

    return $this->render('HelloDiDiDistributorsBundle:Entiti:EditUserEntiti.html.twig',
        array(
            'form_edit'=>$form_edit->createView(),
            'entity'=>$user->getEntiti(),
            'User'=>$user
        ));
}





    public  function addressAction(Request $req, $id)
    {


        $em=$this->getDoctrine()->getEntityManager();
        $entity=$em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);
        $Address=$entity->getDetailHistories();


        return $this->render('HelloDiDiDistributorsBundle:Entiti:Address.html.twig', array(
            'pagination' => $Address,
            'entity' => $entity,
        ));

    }

    public  function EditAddressAction(Request $req,$addrid)
    {

        $em=$this->getDoctrine()->getEntityManager();
        $entity=$em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($addrid);

        $DetaHis=new DetailHistory();
        $form=$this->createForm(new EditAddressEntitiType(),$entity);

        $entname=$entity->getEntName();
        $entvatnumber=$entity->getEntVatNumber();
        $enttel1=$entity->getEntTel1();
        $enttel2=$entity->getEntTel2();
        $entfax=$entity->getEntFax();
        $entweb=$entity->getEntWebsite();

        if($req->isMethod('POST'))
        {

            $form->handleRequest($req);
            if($form->isValid($form))
            {
                $entity->setEntName($entname);
                $entity->setEntVatNumber($entvatnumber);
                $entity->setEntTel1($enttel1);
                $entity->setEntTel2($enttel2);
                $entity->setEntFax($entfax);
                $entity->setEntWebsite($entweb);

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
                $this->get('session')->getFlashBag()->add('success','this operation done success !');
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Entiti:EditAddress.html.twig', array(
            'form_edit'=>$form->createView(),
            'entity' => $entity,
        ));
    }





}