<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Entity\DetailHistory;
use HelloDi\DiDistributorsBundle\Entity\Entiti;
use HelloDi\DiDistributorsBundle\Form\Distributors\NewRetailersType;
use HelloDi\DiDistributorsBundle\Entity\User;
use HelloDi\DiDistributorsBundle\Form\Distributors\NewUserRetailersType;
use HelloDi\DiDistributorsBundle\Form\Distributors\NewUserDistributorsType;
use HelloDi\DiDistributorsBundle\Form\Distributors\RetailerSearchType;
use HelloDi\DiDistributorsBundle\Form\Entiti\EntitiType;
use HelloDi\DiDistributorsBundle\Form\Retailers\AccountRetailerSettingType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HelloDi\DiDistributorsBundle\Entity\Account;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

class DistributorsController extends Controller
{
    public function dashboardAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $Account = $user->getAccount();

        return $this->render('HelloDiDiDistributorsBundle:Distributors:dashboard.html.twig', array('Account' => $Account, 'Entiti' => $Account->getEntiti(), 'User' => $user));

    }

    //Retailers

    public  function  saleAction(Request $req){


        $User= $this->get('security.context')->getToken()->getUser();

        $em=$this->getDoctrine()->getManager();
        $query=null;
        //load first list search

        $qb=$em->createQueryBuilder();
        $qb->select('Co')
            ->from('HelloDiDiDistributorsBundle:Code','Co')
            ->innerjoin('Co.Transactions','CoTr')
            ->where('Co.status=:st')->setParameter('st',0)
            ->andwhere('CoTr.Account=:ac')->setParameter('ac',$User->getAccount());
        $query=$qb->getQuery();



        $form=$this->createFormBuilder()

            ->add('ItemType','choice',
                array('choices'=>
                array('3'=>'All','1'=>'Item.TypeChioce.Internet','0' =>'Item.TypeChioce.Mobile','2' =>'Item.TypeChioce.Tel')))

            ->add('ItemName', 'entity',
                array(
                    'empty_data' => 'All',
                    'class' => 'HelloDiDiDistributorsBundle:Item',
                    'property' => 'itemName',
                ))


            ->add('Account', 'entity',
                array(
                    'class' => 'HelloDiDiDistributorsBundle:Account',
                    'property' => 'accName',
                    'empty_data'=>'All',
                    'query_builder' => function(EntityRepository $er) use ($User) {
                        return $er->createQueryBuilder('a')
                            ->where('a.Parent = :ap')
                            ->orderBy('a.accName', 'ASC')
                            ->setParameter('ap',$User->getAccount());
                    }
                ))


            ->add('DateStart','date',array())
            ->add('DateEnd','date',array())->getForm();

        if($req->isMethod('POST'))
        {
            $form->bind($req);
            $data=$form->getData();
            $qb=$em->createQueryBuilder();
            $qb->select('Co')
                ->from('HelloDiDiDistributorsBundle:Code','Co')
                ->innerjoin('Co.Item','CoIt')
                ->innerjoin('CoIt.Prices','CoItPr')
                ->innerjoin('Co.Transactions','CoTr')
                ->where('Co.status= 0')
                ->andwhere('CoTr.tranDate >= :DateStart')->setParameter('DateStart',$data['DateStart'])
                ->andwhere('CoTr.tranDate <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);

            if($data['Account']!='All')
            {

                $qb=$qb->andwhere('CoTr.Account =:account')->setParameter('account',$data['Account']);

            }

            if($data['ItemType']!=3)
            {
                $qb=$qb->andwhere('CoIt.itemType =:ItemType')->setParameter('ItemType',$data['ItemType']);

            }

            if($data['ItemName']!='All')
                $qb=$qb->andWhere($qb->expr()->like('CoIt.itemName',$qb->expr()->literal($data['ItemName'])));


            $query=$qb->getQuery();

        }

        $count = count($query->getResult());
        $query = $query->setHint('knp_paginator.count', $count);
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1) /*page number*/,
            5/*limit per page*/
        );

        return $this->render('HelloDiDiDistributorsBundle:Distributors:ReportSales.html.twig',

            array(
                'pagination'=>$pagination,
                'form'=>$form->createView(),
                'User'=>$User,
                'Account' =>$User->getAccount(),
                'Entiti' =>$User->getEntiti()));


    }

    public function DistProfileAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $Account = $user->getAccount();
        return $this->render('HelloDiDiDistributorsBundle:Distributors:Profile.html.twig', array('Account' => $Account, 'Entiti' => $Account->getEntiti(), 'User' => $user));
    }

    public function DistStaffAction()
    {
        $Account = $this->get('security.context')->getToken()->getUser()->getAccount();
        $users = $Account->getUsers();
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $users,
            $this->get('request')->query->get('page', 1) /*page number*/,
            6/*limit per page*/
        );
        return $this->render('HelloDiDiDistributorsBundle:Distributors:Staff.html.twig', array('Entiti' => $Account->getEntiti(), 'Account' => $Account, 'pagination' => $pagination));
    }

    public function DistStaffAddAction(Request $request, $id)
    {
        $user = new User();
        $em = $this->getDoctrine()->getManager();
        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $Entiti = $Account->getEntiti();

        $form = $this->createForm(new NewUserDistributorsType('HelloDiDiDistributorsBundle\Entity\User'), $user, array('cascade_validation' => true));
        $formrole = $this->createFormBuilder()
            ->add('roles', 'choice', array('choices' => array('ROLE_DISTRIBUTOR' => 'ROLE_DISTRIBUTOR', 'ROLE_DISTRIBUTOR_ADMIN' => 'ROLE_DISTRIBUTOR_ADMIN')))->getForm();

        if ($request->isMethod('POST')) {
            $form->bind($request);
            $formrole->bind($request);
            $data = $formrole->getData();
            $user->addRole(($data['roles']));
            $user->setAccount($Account);
            $user->setEntiti($Entiti);
            $user->setStatus(1);
            if ($form->isValid()) {
                $em->persist($user);
                $em->flush();

                return $this->redirect($this->generateUrl('DistStaff', array('id' => $Account->getId())));

            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Distributors:StaffAdd.html.twig', array('Entiti' => $Account->getEntiti(), 'Account' => $Account, 'form' => $form->createView(), 'formrole' => $formrole->createView()));

    }

    public function DistStaffEditAction(Request $request, $id)
    {


        $user = new User();
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('HelloDiDiDistributorsBundle:User')->find($id);
        $form = $this->createForm(new NewUserDistributorsType('HelloDiDiDistributorsBundle\Entity\User'), $user, array('cascade_validation' => true));

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                if ($user->getStatus() == 0)
                    $user->setStatus(0);
                else
                    $user->setStatus(1);
                $em->flush();
                return $this->redirect($this->generateUrl('DistStaff', array('id' => $user->getAccount()->getId())));
            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Distributors:StaffEdit.html.twig', array('Account' => $user->getAccount(), 'Entiti' => $user->getEntiti(), 'userid' => $id, 'form' => $form->createView()));

    }

    public function DistChangeRoleAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('HelloDiDiDistributorsBundle:User')->find($id);
        $roles = $user->getRoles();
        $role = $roles[0];
        switch ($role) {

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
        return $this->redirect($this->generateUrl('DistStaff', array('id' => $user->getAccount()->getId())));

    }

//---------click pn open list Retailers----------
    public function DistRetailerUserAction($id) //id Account
    {
        $em = $this->getDoctrine()->getManager();
        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $users = $Account->getUsers();
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $users,
            $this->get('request')->query->get('page', 1) /*page number*/,
            6/*limit per page*/
        );

        return $this->render('HelloDiDiDistributorsBundle:Distributors:RetailerUser.html.twig', array('Entiti' => $Account->getEntiti(), 'Account' => $Account, 'pagination' => $pagination));

    }

    public function DistRetailerUserEditAction(Request $request, $id)
    {

        $user = new User();
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('HelloDiDiDistributorsBundle:User')->find($id);
        $form = $this->createForm(new NewUserRetailersType('HelloDiDiDistributorsBundle\Entity\User'), $user, array('cascade_validation' => true));

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                if ($user->getStatus() == 0)
                    $user->setStatus(0);
                else
                    $user->setStatus(1);
                $em->flush();
                return $this->redirect($this->generateUrl('DistRetailerUser', array('id' => $user->getAccount()->getId())));
            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Distributors:RetailerUserEdit.html.twig', array('Account' => $user->getAccount(), 'Entiti' => $user->getEntiti(), 'userid' => $id, 'form' => $form->createView()));

    }

    public function DistRetailerUserAddAction(Request $request, $id)
    {

        $user = new User();
        $em = $this->getDoctrine()->getManager();
        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $Entiti = $Account->getEntiti();

        $form = $this->createForm(new NewUserRetailersType('HelloDiDiDistributorsBundle\Entity\User'), $user, array('cascade_validation' => true));
        $formrole = $this->createFormBuilder()
            ->add('roles', 'choice', array('choices' => array('ROLE_RETAILER' => 'ROLE_RETAILER', 'ROLE_RETAILER_ADMIN' => 'ROLE_RETAILER_ADMIN')))->getForm();

        if ($request->isMethod('POST')) {
            $form->bind($request);
            $formrole->bind($request);
            $data = $formrole->getData();
            $user->addRole(($data['roles']));
            $user->setAccount($Account);
            $user->setEntiti($Entiti);
            $user->setStatus(1);
            if ($form->isValid()) {
                $em->persist($user);
                $em->flush();

                return $this->redirect($this->generateUrl('DistRetailerUser', array('id' => $Account->getId())));

            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Distributors:RetailerUserAdd.html.twig', array('Entiti' => $Account
            ->getEntiti(), 'Account' => $Account, 'form' => $form->createView(), 'formrole' => $formrole->createView()));

    }

    public function DistRetailerUserChangeRoleAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('HelloDiDiDistributorsBundle:User')->find($id);
        $roles = $user->getRoles();
        $role = $roles[0];
        switch ($role) {

            case 'ROLE_RETAILER':
                $user->removeRole('ROLE_RETAILER');
                $user->addRole('ROLE_RETAILER_ADMIN');
                break;

            case 'ROLE_RETAILER_ADMIN':
                $user->removeRole('ROLE_RETAILER_ADMIN');
                $user->addRole('ROLE_RETAILER');
                break;
        }

        $em->flush();
        return $this->redirect($this->generateUrl('DistRetailerUser', array('id' => $user->getAccount()->getId())));

    }

    public function DistNewRetailerAction(Request $request)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $Account = $user->getAccount();

        $em = $this->getDoctrine()->getManager();
        $userget = $this->container->get('security.context')->getToken()->getUser();
        $need = $userget->getAccount();
        $Currency = $need->getAccCurrency();

        $user = new User();
        $AdrsDetai = new DetailHistory();
        $Entiti = new Entiti();
        $Account = new Account();

        $Account->setAccCreditLimit(0);
        $Account->setAccCreationDate(new \DateTime('now'));
        $Account->setAccTimeZone('Africa/Abidjan');
        $Account->setAccType(2);
        $Account->setAccBalance(0);
        $Account->setAccCurrency($Currency);
        $Account->setParent($need);


        $Account->setEntiti($Entiti);
        $user->setEntiti($Entiti);
        $Entiti->addUser($user);

        $Entiti->addAccount($Account);

        $user->setAccount($Account);
        $user->setStatus(1);


        $form = $this->createForm(new NewRetailersType(), $Entiti, array('cascade_validation' => true));

        if ($request->isMethod('POST')) {

            $form->bind($request);

            //if ($form->isValid()) {

            $em->persist($Entiti);
            $AdrsDetai->setCountry($Entiti->getCountry());
            $em->persist($Account);
            $em->persist($user);
            $AdrsDetai->setAdrsDate(new \DateTime('now'));
            $AdrsDetai->setEntiti($Entiti);
            $AdrsDetai->setAdrs1($Entiti->getEntAdrs1());
            $AdrsDetai->setAdrs2($Entiti->getEntAdrs2());
            $AdrsDetai->setAdrs3($Entiti->getEntAdrs3());
            $AdrsDetai->setAdrsCity($Entiti->getEntCity());
            $AdrsDetai->setAdrsNp($Entiti->getEntNp());
            $AdrsDetai->setEntiti($Entiti);
            $em->persist($AdrsDetai);
            $em->flush();


            //  return $this->redirect($this->generateUrl('ShowMyAccount'));

            return $this->redirect($this->generateUrl('Retailer_Transaction', array('id' => $Entiti->getId())));
            //}

        }

        return $this->render('HelloDiDiDistributorsBundle:Distributors:NewRetailer.html.twig', array(
            'form_Relaited_New' => $form->createView(),
            'Account' => $Account
        ));

    }

    public function ShowRetaierAccountAction(Request $request)
    {
        $form_searchprov = $this->createForm(new RetailerSearchType());

        $user = $this->get('security.context')->getToken()->getUser();
        $Account = $user->getAccount();

        $em = $this->getDoctrine()->getManager();


        $check = $user->getAccount()->getId();


        $qb = $em->createQueryBuilder()
            ->select('retailer')
            ->from('HelloDiDiDistributorsBundle:Account', 'retailer')
            ->innerJoin('retailer.Entiti', 'Ent')
            ->where('retailer.Parent =:check')
            ->setParameter('check', $check);

        if ($request->isMethod('POST')) {

            $form_searchprov->bind($request);
            $dataform = $form_searchprov->getData();


            if ($dataform['retName'] != '')
                $qb->andwhere($qb->expr()->like('retailer.accName', $qb->expr()->literal($dataform['retName'] . '%')));
            if ($dataform['retCityName'] != '')
                $qb->andwhere($qb->expr()->like('Ent.entCity', $qb->expr()->literal($dataform['retCityName'] . '%')));
            if ($dataform['retBalance'] == 1)
                if ($dataform['retBalanceValue'] != '')
                    $qb->andwhere($qb->expr()->gte('retailer.accBalance', $dataform['retBalanceValue']));
            if ($dataform['retBalance'] == 0)
                if ($dataform['retBalanceValue'])
                    $qb->andwhere($qb->expr()->lte('retailer.accBalance', $dataform['retBalanceValue']));
            if ($dataform['retcurency'] != '')
                $qb->andwhere($qb->expr()->like('retailer.accCurrency', $qb->expr()->literal($dataform['retcurency'] . '%')));
////        if ($dataform['id'] != '')
////            $qb->andwhere($qb->expr()->eq('Acc.id', $dataform['id']));
////        $query = $qb->getQuery();

        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $qb,
            $this->get('request')->query->get('page', 1) /*page number*/,
            5/*limit per page*/
        );
        return $this->render('HelloDiDiDistributorsBundle:Distributors:ShowRetailers.html.twig', array (
            'pagination' => $pagination,
            'form_searchprov' => $form_searchprov->createView(),
            'Account' => $Account
        ));

    }

/////---kazem--

    public function RetailersTransactionAction(Request $req,$id)
    {

        $em=$this->getDoctrine()->getManager();
        $Account=$em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $query=$em->getRepository('HelloDiDiDistributorsBundle:Transaction')->findBy(array('Account'=>$Account));

        $form=$this->createFormBuilder()
            ->add('Type','choice',array('choices'=>array('All'=>'All','Sale'=>'Sales','Paym'=>'Payment','Cred'=>'CreditNotes','Tras'=>'Transfer','Add'=>'Add')))
            ->add('DateStart','date',array())
            ->add('DateEnd','date',array())
            ->add('TypeDate','choice', array(
                'expanded'   => true,
                'choices'    => array(
                    0 => 'Trade Date',
                    1 => 'Looking Date',
                )
            ))->getForm();


        if($req->isMethod('POST'))
        {
            $form->bind($req);
            $data=$form->getData();
            $qb=$em->createQueryBuilder();
            $qb->select('Tran')
                ->from('HelloDiDiDistributorsBundle:Transaction','Tran')
                ->where('Tran.Account = :Acc')->setParameter('Acc',$Account);
            if($data['TypeDate']==0)
            {

                $qb=$qb->andwhere('Tran.tranDate >= :DateStart')->setParameter('DateStart',$data['DateStart']);
                $qb=$qb->andwhere('Tran.tranDate <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);

            }

            if($data['TypeDate']==1)
            {

                $qb=$qb->where('Tran.tranInsert >= :DateStart')->setParameter('DateStart',$data['DateStart']);
                $qb=$qb->andwhere('Tran.tranInsert <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);

            }

            if($data['Type']!='All')
            {
                $qb=$qb->andWhere($qb->expr()->like('Tran.tranAction',$qb->expr()->literal($data['Type'])));

            }

            $query=$qb->getQuery();
        }
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1) /*page number*/,
            5/*limit per page*/
        );

        return $this->render('HelloDiDiDistributorsBundle:Distributors:RetailersTransaction.html.twig',
        array(
            'pagination'=>$pagination,
            'form'=>$form->createView(),
            'Account' =>$Account,
            'Entiti' =>$Account->getEntiti()
        ));


    }



public function  DistTransactionAction(Request $req)
{

    $Account=$this->get('security.context')->getToken()->getUser()->getAccount();
    $em=$this->getDoctrine()->getManager();
    $query=$em->getRepository('HelloDiDiDistributorsBundle:Transaction')->findBy(array('Account'=>$Account));

    $form=$this->createFormBuilder()
        ->add('Type','choice',array('choices'=>array('All'=>'All','Sale'=>'Sales','Paym'=>'Payment','Cred'=>'CreditNotes','Tras'=>'Transfer','Add'=>'Add')))
        ->add('DateStart','date',array())
        ->add('DateEnd','date',array())
        ->add('TypeDate','choice', array(
            'expanded'   => true,
            'choices'    => array(
                0 => 'Trade Date',
                1 => 'Looking Date',
            )
        ))->getForm();


    if($req->isMethod('POST'))
    {
        $form->bind($req);
        $data=$form->getData();
        $qb=$em->createQueryBuilder();
        $qb->select('Tran')
            ->from('HelloDiDiDistributorsBundle:Transaction','Tran')
            ->where('Tran.Account = :Acc')->setParameter('Acc',$Account);

        if($data['TypeDate']==0)
        {

            $qb=$qb->andwhere('Tran.tranDate >= :DateStart')->setParameter('DateStart',$data['DateStart']);
            $qb=$qb->andwhere('Tran.tranDate <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);

        }

        if($data['TypeDate']==1)
        {

            $qb=$qb->andwhere('Tran.tranInsert >= :DateStart')->setParameter('DateStart',$data['DateStart']);
            $qb=$qb->andwhere('Tran.tranInsert <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);

        }

        if($data['Type']!='All')
        {
            $qb=$qb->andWhere($qb->expr()->like('Tran.tranAction',$qb->expr()->literal($data['Type'])));

        }

        $query=$qb->getQuery();
        $count = count($query->getResult());
        $query = $query->setHint('knp_paginator.count', $count);

    }
    $paginator = $this->get('knp_paginator');
    $pagination = $paginator->paginate(
        $query,
        $this->get('request')->query->get('page', 1) /*page number*/,
        10/*limit per page*/
    );

    return $this->render('HelloDiDiDistributorsBundle:Distributors:Transaction.html.twig',
        array(
            'pagination'=>$pagination,
            'form'=>$form->createView(),
            'Account' =>$Account,
            'Entiti' =>$Account->getEntiti()
        ));







}


//----endkazem----//


    public function DistRetailerSettingAction(Request $req, $id) //id account
    {
        $em = $this->getDoctrine()->getManager();
        $retacc = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $form = $this->createForm(new AccountRetailerSettingType(), $retacc);

        if ($req->isMethod('POST')) {
            $form->bind($req);
            if ($form->isValid()) {
                $em->flush();
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Distributors:RetailerSetting.html.twig', array(
            'Entiti' => $retacc->getEntiti(),
            'Account' => $retacc,
            'form' => $form->createView()
        ));
    }

    public function DetailsAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);

        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->findBy(array('Entiti' => $entity, 'accType' => 0));

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Entiti entity.');
        }



        $editForm = $this->createForm(new EntitiType(), $entity);

        return $this->render('HelloDiDiDistributorsBundle:Distributors:Details.html.twig', array(
            'account' => $Account,
            'Account' => $account,
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
        ));
    }

    public function editRetailersAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Entiti entity.');
        }


        $editForm = $this->createForm(new EntitiType(), $entity);
        $editForm->bind($request);


        $em->persist($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('Retailer_Transaction', array('id' => $id)));

    }

    //items
    public function ShowItemsAction(Request $request)
    {
        $account = $this->get('security.context')->getToken()->getUser()->getAccount();

        $form = $this->createFormBuilder()
            ->add('type', 'choice', array(
                'choices' => array(
                    '-1' => 'Item.All',
                    '1' => 'Item.TypeChioce.Internet',
                    '0' => 'Item.TypeChioce.Mobile',
                    '2' => 'Item.TypeChioce.Tel',
                ),
                'label' => 'Item.Type', 'translation_domain' => 'item'
            ))
            ->getForm();

        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder()
            ->select('item')
            ->from('HelloDiDiDistributorsBundle:Item', 'item')
            ->innerJoin('item.Prices', 'prices')
            ->innerJoin('prices.Account', 'account')
            ->where('account = :acc')
            ->setParameter('acc', $account);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            $data = $form->getData();
            if ($data['type'] != '-1')
                $qb = $qb->andWhere('item.itemType = :type')->setParameter('type', $data['type']);
        }

        $count = count($qb->getQuery()->getResult());
        $query = $qb->getQuery()->setHint('knp_paginator.count', $count);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1),
            10
        );
        return $this->render('HelloDiDiDistributorsBundle:Distributors:items.html.twig', array(
            'pagination' => $pagination,
            'form' => $form->createView(),
            'Account' => $account
        ));
    }

    public function ItemPerRetailerAction(Request $request, $id)
    {
        $account = $this->get('security.context')->getToken()->getUser()->getAccount();
        $form = $this->createFormBuilder()
            ->add('fff', 'collection', array(
                'type' => 'checkbox',
                'options' => array(
                    'required' => false,
                ),
            ))
            ->getForm();

        return $this->render('HelloDiDiDistributorsBundle:Distributors:ItemsPerRetailer.html.twig', array(
            'form' => $form->createView(),
            'Account' => $account
        ));
    }

    public function RetailerItemsAction($id)
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();

        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $qb = $em->createQueryBuilder()
            ->select('item')
            ->from('HelloDiDiDistributorsBundle:Item', 'item')
            ->innerJoin('item.Prices', 'prices')
            ->innerJoin('prices.Account', 'account')
            ->where('account = :acc')
            ->setParameter('acc', $account);

        $items = $qb->getQuery()->getResult();

        return $this->render('HelloDiDiDistributorsBundle:Distributors:RetailerItems.html.twig', array(
            'items' => $items,
            'Account' => $myaccount
        ));
    }

    public function RetailerItemsAddAction($id)
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();

        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $qb = $em->createQueryBuilder()
            ->select('item')
            ->from('HelloDiDiDistributorsBundle:Item', 'item')
            ->innerJoin('item.Prices', 'prices')
            ->innerJoin('prices.Account', 'account')
            ->where('account = :acc')
            ->setParameter('acc', $account);

        $items = $qb->getQuery()->getResult();

        return $this->render('HelloDiDiDistributorsBundle:Distributors:RetailerItemsAdd.html.twig', array(
            'items' => $items,
            'Account' => $myaccount
        ));
    }
}

