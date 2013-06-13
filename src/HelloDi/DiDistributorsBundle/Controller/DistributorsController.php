<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Entity\DetailHistory;
use HelloDi\DiDistributorsBundle\Entity\Entiti;
use HelloDi\DiDistributorsBundle\Entity\Price;
use HelloDi\DiDistributorsBundle\Entity\PriceHistory;
use HelloDi\DiDistributorsBundle\Entity\Transaction;
use HelloDi\DiDistributorsBundle\Form\Distributors\NewRetailersType;
use HelloDi\DiDistributorsBundle\Entity\User;
use HelloDi\DiDistributorsBundle\Form\Distributors\NewUserRetailersType;
use HelloDi\DiDistributorsBundle\Form\Distributors\NewUserDistributorsType;
use HelloDi\DiDistributorsBundle\Form\Distributors\RetailerSearchType;
use HelloDi\DiDistributorsBundle\Form\Entiti\EntitiType;
use HelloDi\DiDistributorsBundle\Form\PriceEditType;
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

    public function saleAction(Request $req){

        $User= $this->get('security.context')->getToken()->getUser();
        $Account=$User->getAccount();

        $em=$this->getDoctrine()->getManager();

        $qb=$em->createQueryBuilder();

        $qb->select('Tr')
            ->from('HelloDiDiDistributorsBundle:Transaction','Tr')
            /*for GroupBy*/  ->innerJoin('Tr.Code','TrCo')->innerJoin('TrCo.Item','TrCoIt')->innerJoin('Tr.Account','TrAc')
            ->Where($qb->expr()->like('Tr.tranAction',$qb->expr()->literal('sale')));

        foreach($Account->getChildrens() as $child)
        {
            $qb=$qb->orwhere('Tr.Account=:ac')->setParameter('ac',$child);
            // ->andWhere($qb->expr()->like('Tr.tranAction',$qb->expr()->literal('sale')));

        }

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
                    'query_builder' => function(EntityRepository $er) use ($Account) {
                        return $er->createQueryBuilder('a')
                            ->where('a.Parent = :ap')
                            ->orderBy('a.accName', 'ASC')
                            ->setParameter('ap',$Account);
                    }
                ))


            ->add('DateStart','date',array())
            ->add('DateEnd','date',array())
            ->add('GroupBy','choice',array('choices'=>array('NotGroupBy'=>'NotGroupBy','TrCoIt.itemName'=>'Item Name','TrAc.accName'=>'Retainer Name')))
            ->getForm();

        if($req->isMethod('POST'))
        {
            $form->bind($req);
            $data=$form->getData();
            $qb=$em->createQueryBuilder();
            $qb->select('Tr')
                ->from('HelloDiDiDistributorsBundle:Transaction','Tr')

                /*for groupBy*/
                ->innerJoin('Tr.Code','TrCo')->innerJoin('TrCo.Item','TrCoIt')->innerJoin('Tr.Account','TrAc')
                /**/

                ->Where($qb->expr()->like('Tr.tranAction',$qb->expr()->literal('sale')))
                ->andwhere('Tr.tranDate >= :DateStart')->setParameter('DateStart',$data['DateStart'])
                ->andwhere('Tr.tranDate <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);

            if($data['Account']!='All')

                $qb=$qb->andwhere('Tr.Account =:account')->setParameter('account',$data['Account']);


            if($data['ItemType']!=3)

                $qb=$qb->andwhere('TrCoIt.itemType =:ItemType')->setParameter('ItemType',$data['ItemType']);



            if($data['ItemName']!='All')
                $qb=$qb->andWhere($qb->expr()->like('TrCoIt.itemName',$qb->expr()->literal($data['ItemName'])));

            if($data['GroupBy']!='NotGroupBy')
            {
                $qb=$qb->GroupBy($data['GroupBy']);
            }


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

        return $this->render('HelloDiDiDistributorsBundle:Distributors:ReportSale.html.twig',

            array(
                'pagination'=>$pagination,
                'form'=>$form->createView(),
                'User'=>$User,
                'Account' =>$User->getAccount(),
                'Entiti' =>$User->getEntiti()));

    }


    public function  DetailsSaleAction(Request $req,$id)
    {

        $em=$this->getDoctrine()->getManager();

        $tran=$em->getRepository('HelloDiDiDistributorsBundle:Transaction')->find($id);

        $BuPrice=$em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array(
         'Account'=>$tran->getAccount()->getParent()
        ,'Item'=>$tran->getCode()->getItem()));

        $SePrice=$em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array(
            'Account'=>$tran->getAccount()
           ,'Item'=>$tran->getCode()->getItem()));

        return $this->render('HelloDiDiDistributorsBundle:Distributors:DetailsReportSale.html.twig',
            array(
                'tran'=>$tran,
                'BuPrice'=>$BuPrice,
                'SePrice'=>$SePrice
            ));

    }

    public function  FundingAction($id)
    {

        $em=$this->getDoctrine()->getManager();

        $Account=$em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $formapplay=$this->createFormBuilder()
            ->add('Amount')
            ->add('Description','textarea',array())
            ->getForm();

        $formupdate=$this->createFormBuilder()
            ->add('Amount','text')
            ->add('As','choice',array(
                'preferred_choices'=>array('Credit'),
                'choices'=>array('Credit'=>'Credit','Debit'=>'Debit')
            ))->getForm();

        return $this->render('HelloDiDiDistributorsBundle:Distributors:Funding.html.twig',
            array(
                'Entiti'=>$Account->getEntiti(),
                'Account'=>$Account,
                'formapplay'=>$formapplay->createView(),
                'formupdate'=>$formupdate->createView(),

            ));
    }

    public function  FundingTransferAction(Request $req,$id)
    {
        $User= $this->get('security.context')->getToken()->getUser();
        $em=$this->getDoctrine()->getManager();

        $Account=$em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $AccountParent=$Account->getParent();
        $formtransfer=$this->createFormBuilder()
            ->add('Amount')
            ->add('Description','textarea',array())
            ->getForm();

        if($req->isMethod('post'))
        {
            $tran=new Transaction();
            $formtransfer->bind($req);
            $data=$formtransfer->getData();

            //objeavt transaction//

            $tran->setTranDate(new \DateTime('now'));
            $tran->setTranCurrency($Account->getAccCurrency());

            $tran->setTranInsert(new \DateTime('now'));
            $tran->setAccount($Account);
            $tran->setTranAction('tras');
            $tran->setUser($User);
            $tran->setTranFees(0);

            if($data['Description']!='')$tran->setTranDescription($data['Description']);

            if($data['Amount']!='')
            {
                $AccountParent->setAccBalance($AccountParent->getAccBalance()-$data['Amount']);
                $Account->setAccBalance($Account->getAccBalance()+$data['Amount']);
            }
            $em->persist($tran);
            $em->flush();

        }

        return $this->redirect($this->generateUrl('DistRetailerFunding',array('id'=>$id)));

    }

    public function  FundingUpdateAction(Request $req,$id)
    {
        $User= $this->get('security.context')->getToken()->getUser();
        $em=$this->getDoctrine()->getManager();

        $Account=$em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $AccountParent=$Account->getParent();
        $formupdate=$this->createFormBuilder()
            ->add('Amount','text')
            ->add('As','choice',array('preferred_choices'=>array('Credit'),
                'choices'=>array('Credit'=>'Credit','Debit'=>'Debit')
            ))->getForm();

        if($req->isMethod('POST'))
        {
            $formupdate->bind($req);
            $data=$formupdate->getData();

            $tran=new Transaction();

            $tran->setTranDate(new \DateTime('now'));
            $tran->setTranCurrency($Account->getAccCurrency());

            $tran->setTranInsert(new \DateTime('now'));


            $tran->setUser($User);
            $tran->setTranFees(0);


            if($data['As']=='Credit')
            {
                $AccountParent->setAccBalance($AccountParent->getAccBalance()-$data['Amount']);
                $Account->setAccCreditLimit($Account->getAccCreditLimit()+ $data['Amount']);
                $tran->setAccount($AccountParent);
                $tran->setTranAction('Credit');
                $em->persist($tran);
           ///وقتی که سرویس اماده شد دستورات اصلاح خواهد شد
            }
            elseif($data['As']=='Debit')
            {
                $Account->setAccCreditLimit($Account->getAccCreditLimit()- $data['Amount']);
                $tran->setTranAction('Debit');

            }


            $em->flush();
        }
        return $this->redirect($this->generateUrl('DistRetailerFunding',array('id'=>$id)));
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
    public function DistRetailerUserAction($id)
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
        $em = $this->getDoctrine()->getManager();
        $retailerAccount = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $users = $retailerAccount->getUsers();
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $users,
            $this->get('request')->query->get('page', 1) /*page number*/,
            6/*limit per page*/
        );

        return $this->render('HelloDiDiDistributorsBundle:Distributors:RetailerUser.html.twig', array(
                'Entiti' => $retailerAccount->getEntiti(),
                'retailerAccount' => $retailerAccount,
                'Account' => $myaccount,
                'pagination' => $pagination
            ));

    }

    public function DistRetailerUserEditAction(Request $request, $id)
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
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
        return $this->render('HelloDiDiDistributorsBundle:Distributors:RetailerUserEdit.html.twig', array(
                'retailerAccount' => $user->getAccount(),
                'Account' => $myaccount,
                'Entiti' => $user->getEntiti(),
                'userid' => $id,
                'form' => $form->createView()
            ));

    }

    public function DistRetailerUserAddAction(Request $request, $id)
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
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
        return $this->render('HelloDiDiDistributorsBundle:Distributors:RetailerUserAdd.html.twig', array(
                'Entiti' => $Account->getEntiti(),
                'retailerAccount' =>$Account,
                'Account' => $myaccount,
                'form' => $form->createView(),
                'formrole' => $formrole->createView()
            ));

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

/////---jaadidkazem--

    public function RetailersTransactionAction(Request $req,$id)
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
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
            ))
            ->getForm();


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
            'Account' =>$myaccount,
            'retailerAccount' => $Account,
            'Entiti' =>$Account->getEntiti()
        ));


    }

    public function DistTransactionAction(Request $req)
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

//----endjadidkazem----//

    public function DistRetailerSettingAction(Request $req, $id) //id account
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
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
            'Account' => $myaccount,
            'retailerAccount' => $retacc,
            'form' => $form->createView()
        ));
    }

    public function DetailsAction($id)
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();
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
            'Account' => $myaccount,
            'retailerAccount' =>$account,
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
    public function ShowItemsAction()
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();

        $prices = $myaccount->getPrices();

        return $this->render('HelloDiDiDistributorsBundle:Distributors:items.html.twig', array(
            'prices' => $prices,
            'Account' => $myaccount
        ));
    }

    public function ItemPerRetailerAction(Request $request, $itemid)
    {
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($itemid);

        $account = $this->get('security.context')->getToken()->getUser()->getAccount();

        $form = $this->createFormBuilder()
            ->add('checks', 'entity', array(
                    'class' => 'HelloDiDiDistributorsBundle:Account',
                    'expanded' => 'true',
                    'multiple' => 'true',
                    'query_builder' => function(EntityRepository $er) use ($account,$item) {
                        return $er->createQueryBuilder('u')
                            ->leftJoin('u.Prices','prices')
                            ->where('prices is null or prices.Item = :item')
                            ->andWhere('u.Parent = :parent')
                            ->setParameter('item',$item)
                            ->setParameter('parent',$account)
                            ;
                    }
            ))
            ->add('NewPrice','text')
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $newprice = $data['NewPrice'];
                foreach ($data['checks'] as $accountretailer)
                {
                    if(count($accountretailer->getPrices())!=0)
                    {
                        $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Item'=>$item,'Account'=>$accountretailer));
                        $price->setPrice($newprice);

                        $pricehistory = new PriceHistory();
                        $pricehistory->setPrice($newprice);
                        $pricehistory->setDate(new \DateTime('now'));
                        $pricehistory->setPrices($price);
                        $em->persist($pricehistory);
                    }
                    else
                    {
                        $price = new Price();
                        $price->setPrice($newprice);
                        $price->setPriceCurrency($accountretailer->getAccCurrency());
                        $price->setPriceStatus(true);
                        $price->setIsFavourite(true);
                        $price->setItem($item);
                        $price->setAccount($accountretailer);
                        $em->persist($price);

                        $pricehistory = new PriceHistory();
                        $pricehistory->setPrice($newprice);
                        $pricehistory->setDate(new \DateTime('now'));
                        $pricehistory->setPrices($price);
                        $em->persist($pricehistory);
                    }
                }
                $em->flush();
                return $this->forward('HelloDiDiDistributorsBundle:Distributors:ShowItems');
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Distributors:ItemsPerRetailer.html.twig', array(
            'form' => $form->createView(),
            'Account' => $account,
            'itemid' => $itemid
        ));
    }

    public function RetailerItemsAction($id)
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();

        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $prices = $account->getPrices();

        return $this->render('HelloDiDiDistributorsBundle:Distributors:RetailerItems.html.twig', array(
            'Account' => $myaccount,
            'retailerAccount' => $account,
            'prices' => $prices
        ));
    }

    public function RetailerItemsAddAction($id, Request $request)
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();

        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $price = new Price();
        $price->setPriceCurrency($account->getAccCurrency());
        $price->setPriceStatus(1);
        $price->setAccount($account);
        $price->setIsFavourite(false);

        $form = $this->createFormBuilder($price)
            ->add('Item', 'entity', array(
                    'class' => 'HelloDiDiDistributorsBundle:Item',
                    'property' => 'itemName',
                    'query_builder' => function(EntityRepository $er) use ($account,$myaccount) {
                        return $er->createQueryBuilder('u')
                            ->where ('u.id NOT IN (
                            SELECT ii.id
                            FROM HelloDiDiDistributorsBundle:Item ii
                            JOIN ii.Prices pp
                            JOIN pp.Account aa
                            WHERE aa = :aaid
                        )')
                            ->andWhere('u.id IN (
                            SELECT iii.id
                            FROM HelloDiDiDistributorsBundle:Item iii
                            JOIN iii.Prices ppp
                            JOIN ppp.Account aaa
                            WHERE aaa = :aamyid
                        )')
                            ->setParameter('aaid',$account)
                            ->setParameter('aamyid',$myaccount)
                            ;
                    }
                ))
            ->add('price')
            ->getForm();
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $em->persist($price);

                $pricehistory = new PriceHistory();
                $pricehistory->setDate(new \DateTime('now'));
                $pricehistory->setPrice($price->getPrice());
                $pricehistory->setPrices($price);
                $em->persist($pricehistory);

                $em->flush();
                return $this->forward('HelloDiDiDistributorsBundle:Distributors:RetailerItems', array(
                        'id' => $account->getId()
                    ));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Distributors:RetailerItemsAdd.html.twig', array(
                'Account' => $myaccount,
                'retailerAccount' => $account,
                'form' => $form->createView()
            ));
    }

    public function RetailerItemsEditAction($priceid, Request $request)
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();

        $em = $this->getDoctrine()->getManager();
        $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->find($priceid);
        $oldprice = $price->getPrice();

        $form = $this->createForm(new PriceEditType(), $price);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                if ($price->getPrice() != $oldprice) {
                    $pricehistory = new PriceHistory();
                    $pricehistory->setDate(new \DateTime('now'));
                    $pricehistory->setPrice($price->getPrice());
                    $pricehistory->setPrices($price);
                    $em->persist($pricehistory);
                }
                $em->flush();


                return $this->forward('HelloDiDiDistributorsBundle:Distributors:RetailerItems', array(
                        'id' => $price->getAccount()->getId()
                    ));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Distributors:RetailerItemsEdit.html.twig', array(
                'Account' => $myaccount,
                'retailerAccount' => $price->getAccount(),
                'price' => $price,
                'form' => $form->createView()
            ));
    }
}

