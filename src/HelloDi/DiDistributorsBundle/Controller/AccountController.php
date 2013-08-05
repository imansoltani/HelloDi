<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Entity\Code;
use HelloDi\DiDistributorsBundle\Entity\DetailHistory;
use HelloDi\DiDistributorsBundle\Entity\Entiti;
use HelloDi\DiDistributorsBundle\Entity\Input;
use HelloDi\DiDistributorsBundle\Entity\Price;
use HelloDi\DiDistributorsBundle\Entity\PriceHistory;
use HelloDi\DiDistributorsBundle\Entity\Transaction;
use HelloDi\DiDistributorsBundle\Entity\User;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistChildSearchType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistChildType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistMasterType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistSearchType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountProvType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountSearchDistType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountSearchProvType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountType;
use HelloDi\DiDistributorsBundle\Form\Account\EditDistType;
use HelloDi\DiDistributorsBundle\Form\Account\EditProvType;
use HelloDi\DiDistributorsBundle\Form\Account\EditRetailerType;
use HelloDi\DiDistributorsBundle\Form\Account\EntitiAccountprovType;
use HelloDi\DiDistributorsBundle\Form\Account\MakeAccountIn2StepType;
use HelloDi\DiDistributorsBundle\Form\Distributors\NewRetailersType;
use HelloDi\DiDistributorsBundle\Form\Entiti\EditEntitiRetailerType;
use HelloDi\DiDistributorsBundle\Form\PriceEditType;
use HelloDi\DiDistributorsBundle\Form\User\NewUserType;
use HelloDi\DiDistributorsBundle\Form\User\UserDistSearchType;
use HelloDi\DiDistributorsBundle\Form\searchProvRemovedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HelloDi\DiDistributorsBundle\Entity\Account;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use HelloDi\DiDistributorsBundle\Form\searchProvTransType;
use Symfony\Component\Validator\Constraints\DateTime;


class AccountController extends Controller
{
    public function ShowMyAccountProvAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $query = $em->getRepository('HelloDiDiDistributorsBundle:Account')->findBy(array('accType' => 1));

        return $this->render('HelloDiDiDistributorsBundle:Account:ShowMyAccountProv.html.twig', array
        ('pagination' => $query));

    }

//Master

//    public function EditChildAccountAction(Request $request, $id)
//    {
//        $em = $this->getDoctrine()->getManager();
//
//        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
//
//        $idp = $account->getparent()->getId();
//
//        if (!$account) {
//            throw $this->createNotFoundException('Unable to find Account entity.');
//        }
//
//        $editForm = $this->createForm(new AccountDistChildType(), $account);
//        if ($request->isMethod('POST')) {
//
//            $editForm->bind($request);
//            if ($editForm->isValid()) {
//                $em->flush();
//
//                return $this->redirect($this->generateUrl('ShowChildAccount', array('id' => $idp)));
//
//            }
//
////        }
//        return $this->render('HelloDiDiDistributorsBundle:Account:EditChildAccount.html.twig', array(
//            'account' => $account,
//            'edit_form' => $editForm->createView(),
//        ));
//
//    }

//    public function EditAccountAction(Request $request, $id)
//    {
//        $em = $this->getDoctrine()->getManager();
//        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
//        $edit_form = $this->createForm(new AccountDistMasterType(), $account);
//        if ($request->isMethod('POST')) {
//            $edit_form->bind($request);
//            if ($edit_form->isValid()) {
//
//                $em->flush($account);
//                return $this->redirect($this->generateUrl('ShowMyAccountProv'));
//            }
//        }
//
//        return $this->render('HelloDiDiDistributorsBundle:Account:EditAccount.html.twig', array(
//            'account' => $account,
//            'edit_form' => $edit_form->createView()
//        ));
//    }
////
//    public function AddAccountProvMasterAction()
//    {
//
//        $em = $this->getDoctrine()->getEntityManager();
//
//        $Entiti=$this->get('security.context')->getToken()->getUser()->getEntiti();
//
//        $entities =$em->createQueryBuilder();
//            $entities->select('Ent')
//                     ->from('HelloDiDiDistributorsBundle:Entiti','Ent')
//                     ->innerJoin('Ent.Accounts','EntAcc')
//                     ->where('Ent.id !=:id ')->setParameter('id',$Entiti->getId())
//                     ->andWhere('EntAcc.accType != 2');
//
//        $entities=$entities->getQuery()->getResult();
//
//        $paginator = $this->get('knp_paginator');
//
//        $pagination = $paginator->paginate(
//            $entities,
//            $this->get('request')->query->get('page', 1) /*page number*/,
//            10/*limit per page*/
//        );
//        return $this->render('HelloDiDiDistributorsBundle:Account:AddAccountProvMaster.html.twig',
//
//            array(
//                'pagination' => $pagination
//                ));
//
//
//    }
//
//    public function AddAccountProvMasterOkAction(Request $request, $id)
//    {
//
//        $Account = new Account();
//        $em = $this->getDoctrine()->getManager();
//        $entity = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);
//        $Form = $this->createForm(new AccountProvType(), $Account);
//
//        $Form->bind($request);
//
//        if ($Form->isValid()) {
//            $Account->setEntiti($entity);
//            $Account->setAccCreationDate(new \DateTime('now'));
//            $Account->setAccTimeZone(null);
//            $Account->setAccType(1);
//            $Account->setAccBalance(0);
//            $Account->setAccCreditLimit(0);
//            $Account->setAccDefaultLanguage(null);
//            $Account->setParent(null);
//            $Account->setAccTerms(0);
//            $Account->setAccTimeZone(null);
//            $em->persist($Account);
//            $em->flush();
//            return $this->redirect($this->generateUrl('ShowMyAccountProv'));
//
//        }
//        return $this->render('HelloDiDiDistributorsBundle:Account:AddAccountProvMasterOk.html.twig', array(
//            'entity' => $entity,
//            'form' => $Form->createView(),
//        ));
//
//
//    }


    public function AddAccountProveMaster2StepAction(Request $request)
    {


        $em = $this->getDoctrine()->getManager();


        $AdrsDetai = new DetailHistory();
        $Entiti = new Entiti();
        $Account = new Account();


        $Account->setAccCreationDate(new \DateTime('now'));
        $Account->setAccTimeZone(null);
        $Account->setAccType(1);
        $Account->setAccBalance(0);
        $Account->setAccCreditLimit(0);


        $Account->setEntiti($Entiti);
        $Entiti->addAccount($Account);


        $form2step = $this->createForm(new MakeAccountIn2StepType(), $Entiti, array('cascade_validation' => true));

        if ($request->isMethod('POST')) {
            $form2step->bind($request);

            if ($form2step->isValid()) {
                $em->persist($Entiti);
                $AdrsDetai->setCountry($Entiti->getCountry());
                $em->persist($Account);
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

                $this->get('session')->getFlashBag()->add('success', 'this operation done success !');
                return $this->redirect($this->generateUrl('ShowMyAccountProv'));
            }

        }


        return $this->render('HelloDiDiDistributorsBundle:Account:AddAccountProvMaster2Step.html.twig', array(
            'form2step' => $form2step->createView(),
        ));
    }


    public function AddAccountDistMaster2StepAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $AdrsDetai = new DetailHistory();
        $Entiti = new Entiti();
        $Account = new Account();
        $User = new User();

        $Account->setAccCreationDate(new \DateTime('now'));
        $Account->setAccTimeZone(null);
        $Account->setAccType(0);
        $Account->setAccBalance(0);
        $Account->setAccCreditLimit(0);

        $Account->setEntiti($Entiti);
        $Entiti->addAccount($Account);


        $User->setEntiti($Entiti);
        $Entiti->addUser($User);

        $User->setAccount($Account);
        $Account->addUser($User);

        $form2step = $this->createForm(new MakeAccountIn2StepType(), $Entiti,
            array(
                'cascade_validation' => true
            ));

        if ($request->isMethod('POST')) {
            $form2step->handleRequest($request);
            if ($form2step->isValid()) {
                $em->persist($Entiti);
                $AdrsDetai->setCountry($Entiti->getCountry());
                $em->persist($Account);
                $em->persist($User);
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
                $this->get('session')->getFlashBag()->add('success', 'this operation done success !');
                return $this->redirect($this->generateUrl('ShowMyAccountDist'));

            }

        }


        return $this->render('HelloDiDiDistributorsBundle:Account:AddAccountDistMaster2Step.html.twig', array(
            'form2step' => $form2step->createView(),

        ));
    }


    public function ManageProvAction($id)
    {
        return $this->render('HelloDiDiDistributorsBundle:Account:ManageProv.html.twig', array('id' => $id));
    }

    public function EditAccountProvAction(Request $request, $id)
    {

        $account = new Account();
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $edit_form = $this->createForm(new EditProvType(), $account);


        if ($request->isMethod('post')) {

            $edit_form->handleRequest($request);
            if ($edit_form->isValid()) {

                $em->flush($account);
                $this->get('session')->getFlashBag()->add('success', 'this operation done success !');

            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Account:EditAccountProv.html.twig', array(
                'form_edit' => $edit_form->createView(),
                'Account' => $account,
                'id' => $id)
        );
    }


    public function  DistTransactionAction(Request $req, $id)
    {

        $paginator = $this->get('knp_paginator');

        $em = $this->getDoctrine()->getManager();

        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $datetype = 0;


        $qb = array();
        $form = $this->createFormBuilder()

            ->add('DateStart', 'text', array('required' => false, 'label' => 'From:'))
            ->add('DateEnd', 'text', array('required' => false, 'label' => 'To:'))
            ->add('TypeDate', 'choice', array(
                'expanded' => true,
                'choices' => array(
                    0 => 'Trade Date',
                    1 => 'Looking Date',
                )
            ))
            ->add('Type', 'choice', array('label' => 'Type:',
                'choices' => array(
                    2 => 'All',
                    1 => 'Credit',
                    0 => 'Debit'
                )))
            ->add('Action', 'choice', array('label' => 'Action:',
                'choices' =>
                array(
                    'All' => 'All',
                    'pmt' => 'credit distributor,s account',
                    'amdt' => 'debit distributor,s account',
                    'crnt' => 'issue a credit note for a sold code',
                    'com_pmt' => 'debit distributor,s account for the commisson payments',
                    'pmt' => 'ogone payment on its own account',
                    'tran' => 'transfer credit from provider,s account to a distributor,s account',
                    'tran' => 'transfer credit from distributors account to a retailer,s account',
                    'crtl' => 'increase retailer,s credit limit',
                    'com' => 'credit commissons when a retailer sells a code'

                )))
            ->getForm();


        if ($req->isMethod('POST')) {
            $form->handleRequest($req);
            $data = $form->getData();
            $qb = $em->createQueryBuilder();
            $qb->select('Tran')
                ->from('HelloDiDiDistributorsBundle:Transaction', 'Tran')
                ->where('Tran.Account = :Acc')->setParameter('Acc', $Account);
            if ($data['TypeDate'] == 0) {
                if ($data['DateStart'] != '')
                    $qb->andwhere('Tran.tranDate >= :DateStart')->setParameter('DateStart', $data['DateStart']);
                if ($data['DateEnd'] != '')
                    $qb->andwhere('Tran.tranDate <= :DateEnd')->setParameter('DateEnd', $data['DateEnd']);
            }

            if ($data['TypeDate'] == 1) {
                $datetype = 1;
                if ($data['DateStart'] != '')
                    $qb->andwhere('Tran.tranInsert >= :DateStart')->setParameter('DateStart', $data['DateStart']);
                if ($data['DateEnd'] != '')
                    $qb->andwhere('Tran.tranInsert <= :DateEnd')->setParameter('DateEnd', $data['DateEnd']);

            }

            if ($data['Type'] != 2)
                $qb->andWhere($qb->expr()->eq('Tran.tranType', $data['Type']));

            if ($data['Action'] != 'All')
                $qb->andWhere($qb->expr()->like('Tran.tranAction', $qb->expr()->literal($data['Action'])));


            $qb->addOrderBy('Tran.tranInsert', 'desc')->addOrderBy('Tran.id','desc');

            $qb = $qb->getQuery();
            $count = count($qb->getResult());
            $qb->setHint('knp_paginator.count', $count);

        }

        $pagination = $paginator->paginate(
            $qb,
            $req->get('page', 1) /*page number*/,
            10/*limit per page*/
        );


        return $this->render('HelloDiDiDistributorsBundle:Account:DistTransaction.html.twig',
            array(
                'pagination' => $pagination,
                'form' => $form->createView(),
                'Account' => $Account,
                'Entiti' => $Account->getEntiti(),
                'datetype' => $datetype
            ));

    }



    public function  FundingAction($id)
    {

        $em = $this->getDoctrine()->getManager();

        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $formapplay = $this->createFormBuilder()
            ->add('Amount', null, array('label' => 'Amount:'))
            ->add('As', 'choice', array('label' => 'As:',
                'empty_value' => 'choice a action',
                'preferred_choices' => array('Credit'),
                'choices' => array(
                    0 => 'Debit',
                    1 => 'Credit',
                    2 => 'Debit (commission)'
                )
            ))
            ->add('Description', 'textarea',
                array('label' => 'Description:',
                    'required' => true
                ))
            ->getForm();

        $formupdate = $this->createFormBuilder()
            ->add('Amount', 'text', array('label' => 'Amount:'))
            ->add('As', 'choice', array('label' => 'As:',
                'empty_value' => 'choice a action',
                'choices' =>
                array(
                    1 => 'Increase',
                    0 => 'Decrease'
                )
            ))->getForm();

        return $this->render('HelloDiDiDistributorsBundle:Account:Funding.html.twig',
            array(
                'Entiti' => $Account->getEntiti(),
                'Account' => $Account,
                'formapplay' => $formapplay->createView(),
                'formupdate' => $formupdate->createView(),

            ));
    }

    public function  FundingUpdateBalanceAction(Request $req, $id)
    {
        $balancechecker = $this->get('hello_di_di_distributors.balancechecker');

        $User = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $formapplay = $this->createFormBuilder()
            ->add('Amount')
            ->add('As', 'choice', array(
                'preferred_choices' => array('pmt'),
                'choices'
                => array(
                    0 => 'Debit',
                    1 => 'Credit',
                    2 => 'Debit (commission)'
                )))
            ->add('Description', 'textarea', array('required' => true))
            ->getForm();

        if ($req->isMethod('post')) {

            $trandist = new Transaction();
            $formapplay->handleRequest($req);
            $data = $formapplay->getData();

            //objeavt transaction//

            $trandist->setTranDate(new \DateTime('now'));
            $trandist->setTranCurrency($Account->getAccCurrency());
            $trandist->setTranInsert(new \DateTime('now'));
            $trandist->setAccount($Account);
            $trandist->setUser($User);
            $trandist->setTranFees(0);
            $trandist->setTranDescription($data['Description']);
            $trandist->setTranBalance($Account->getAccBalance());


            if (($data['Amount'] > 0)) {
                switch ($data['As']) {
                    case 0:

                        if ($balancechecker->isMoreThanCreditLimit($Account, $data['Amount'])) {
                            $trandist->setTranType(0);
                            $trandist->setTranAmount(-$data['Amount']);
                            $trandist->setTranAction('amdt');
                            $em->persist($trandist);
                            $em->flush();
                            $this->get('session')->getFlashBag()->add('success', 'this operation done success !');
                        }

                        break;

                    case 1:

                        $trandist->setTranType(1);
                        $trandist->setTranAmount(+$data['Amount']);
                        $trandist->setTranAction('pmt');
                        $em->persist($trandist);
                        $em->flush();
                        $this->get('session')->getFlashBag()->add('success', 'this operation done success !');

                        break;

                    case 2:

                        if ($balancechecker->isMoreThanCreditLimit($Account, $data['Amount']))
                        {
                            $trandist->setTranType(0);
                            $trandist->setTranAmount(-$data['Amount']);
                            $trandist->setTranAction('com_pmt');
                            $em->persist($trandist);
                            $em->flush();
                            $this->get('session')->getFlashBag()->add('success', 'this operation done success !');
                        }
                        break;
                }


            }
            else

                $this->get('session')->getFlashBag()->add('error', ' zero isn,t accept !');

        }

        return $this->redirect($this->generateUrl('MasterDistFunding', array('id' => $id)));

    }

    public function  FundingUpdateCredilimitAction(Request $req, $id)
    {

        $em = $this->getDoctrine()->getManager();
        $balancechecker = $this->get('hello_di_di_distributors.balancechecker');
        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $formupdate = $this->createFormBuilder()
            ->add('Amount', 'text')
            ->add('As', 'choice', array('preferred_choices' => array('Credit'),
                'choices' => array(
                    1 => 'Increase',
                    0 => 'Decrease')
            ))->getForm();

        if ($req->isMethod('POST')) {
            $formupdate->handleRequest($req);
            $data = $formupdate->getData();

          switch($data['As'] )
          {
              case 0:
                  if ($balancechecker->isAccCreditLimitPlus($Account, $data['Amount'])) {
                      $Account->setAccCreditLimit($Account->getAccCreditLimit() - $data['Amount']);
                      $this->get('session')->getFlashBag()->add('success', 'this operation done success !');
                  }
                  break;
              case 1:
                  $Account->setAccCreditLimit($Account->getAccCreditLimit() + $data['Amount']);
                  $this->get('session')->getFlashBag()->add('success', 'this operation done success !');
                  break;

          }

            $em->flush();

        }
        return $this->redirect($this->generateUrl('MasterDistFunding', array('id' => $id)));
    }


    public function  SaleAction(Request $req, $id)
    {


        $group = 0;
        $em = $this->getDoctrine()->getEntityManager();
        $qb = array();
        //load first list search
        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $form = $this->createFormBuilder()

            ->add('ItemType', 'choice',
                array('label' => 'Type:', 'choices' =>
                array(
                    'All' => 'All',
                    'dmtu' => 'Mobile',
                    'clcd' => 'calling card',
                    'empt' => 'e-payment',

                )))

            ->add('ItemName', 'entity',
                array('label' => 'Name:',
                    'empty_data' => '',
                    'empty_value' => 'All',
                    'required' => false,
                    'class' => 'HelloDiDiDistributorsBundle:Item',
                    'property' => 'itemName',
                    'query_builder' => function (EntityRepository $er) use ($Account) {
                        return $er->createQueryBuilder('i')
                            ->innerJoin('i.Prices', 'ip')
                            ->where('ip.priceStatus = 1')
                            ->andwhere('ip.Account = :Acc')->setParameter('Acc',$Account);
                        }
                ))


            ->add('Account', 'entity',
                array('label' => 'Retailer(s):',
                    'class' => 'HelloDiDiDistributorsBundle:Account',
                    'property' => 'accName',
                    'required' => false,
                    'empty_value' => 'All',
                    'empty_data' => '',
                    'query_builder' => function (EntityRepository $er) use ($Account) {
                        return $er->createQueryBuilder('a')
                            ->where('a.Parent = :ap')
                            ->orderBy('a.accName', 'ASC')
                            ->setParameter('ap', $Account);
                    }
                ))


            ->add('DateStart', 'text', array('disabled' => false, 'required' => false, 'label' => 'From:'))
            ->add('DateEnd', 'text', array('disabled' => false, 'required' => false, 'label' => 'To:'))

            ->add('GroupBy', 'choice', array('label' => 'GroupBy:',
                'expanded' => true,
                 'multiple'=>true,
                'choices' => array(
                    1 => 'daily sales grouped by item and retailer',
                )))

            ->getForm();


        if ($req->isMethod('POST')) {
            $form->handleRequest($req);
            $data = $form->getData();

            $qb = $em->createQueryBuilder();

            if ($data['GroupBy']) {
                $qb->select('Tr as TR','count(Tr.id) as Quantity');
                $group = 1;
            } else $qb->select('Tr as TR');


            $qb->from('HelloDiDiDistributorsBundle:Transaction', 'Tr')
//                /*for groupBy*/
                ->innerJoin('Tr.Code', 'TrCo')
                ->innerJoin('Tr.Account', 'TrAc')
                ->innerJoin('TrCo.Item', 'TrCoIt')
                /**/
                ->Where($qb->expr()->like('Tr.tranAction', $qb->expr()->literal('sale')));

            if ($data['DateStart'] != '')
                $qb->andwhere('Tr.tranDate >= :DateStart')->setParameter('DateStart', $data['DateStart']);
            if ($data['DateEnd'] != '')
                $qb->andwhere('Tr.tranDate <= :DateEnd')->setParameter('DateEnd', $data['DateEnd']);

            if ($data['Account'])
                $qb->andwhere('Tr.Account =:account')->setParameter('account', $data['Account']);
            else {
                $child = $Account->getChildrens();
                foreach ($child as $acc) {
                    $qb->orwhere('Tr.Account = :Acc')->setParameter('Acc', $acc);
                }
            }

            if ($data['ItemType'] != 'All')
                $qb->andwhere($qb->expr()->like('TrCoIt.itemType ', $qb->expr()->literal($data['ItemType'])));
//
            if ($data['ItemName'])
                $qb->andwhere('TrCoIt.itemName = :item')->setParameter('item', $data['ItemName']);


            if ($data['GroupBy'])
                $qb->GroupBy('Tr.tranDate','TrCoIt.itemName','Tr.tranAmount');

            if (!$data['GroupBy'])
                $qb->addOrderBy('Tr.tranDate', 'desc')->addOrderBy('Tr.id', 'desc');


            $qb = $qb->getQuery();

            $count = count($qb->getResult());

            $qb->setHint('knp_paginator.count', $count);

        }

        if($group==1)
        {
            $pagination = $qb->getResult();
        }
        else
        {
            $paginator = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $qb,
                $req->get('page', 1) /*page number*/,
                10/*limit per page*/
            );
        }


        $formprint=$this->createFormBuilder()
            ->add('option','choice',array('label'=>'option:',

                'expanded'=>true,
                'choices'=>array(
                    0=>'retailer statements',
                    1=>'retailer revenues'
                )
            ))->getForm();


$com=$em->getRepository('HelloDiDiDistributorsBundle:Transaction')->findBy(array(
    'Account'=>$Account,
    'tranAction'=>'com'
     ));

        return $this->render('HelloDiDiDistributorsBundle:Account:ReportSales.html.twig',array(
                'pagination' => $pagination,
                'form' => $form->createView(),
                'formprint'=>$formprint->createView(),
                'Account' => $Account,
                'group'=>$group,
                'Entiti' => $Account->getEntiti(),
                'com'=>$com

        ));


    }


    public function GetComAction($id)
    {

        $em=$this->getDoctrine()->getManager();

        $tran=$em->getRepository('HelloDiDiDistributorsBundle:Transaction')->find($id);

        $com=$em->getRepository('HelloDiDiDistributorsBundle:Transaction')->findOneBy(array(
            'Account'=>$tran->getAccount()->getParent(),
            'Code'=>$tran->getCode(),
            'User'=>$tran->getUser(),
            'tranAction'=>'com',
            'tranDate'=>$tran->getTranDate(),
            'tranCurrency'=>$tran->getTranCurrency()
        ));

        return new Response($com->getTranAmount());

    }

    public function ProvTransferAction($id, Request $req)
    {
        $AccountBalance = $this->get('hello_di_di_distributors.balancechecker');
        $em = $this->getDoctrine()->getEntityManager();

        $User = $this->get('security.context')->getToken()->getUser();
        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $countisprov = 0;
        $isprove = $em->createQueryBuilder();
        $isprove->select('Acc')
            ->from('HelloDiDiDistributorsBundle:Account', 'Acc')
            ->Where('Acc.Entiti = :Ent')->setParameter('Ent', $Account->getEntiti())
            ->andWhere('Acc.accType =0')
            ->andWhere('Acc.accCurrency=:Cur')->setParameter('Cur', $Account->getAccCurrency());

        $countisprov = count($isprove->getQuery()->getResult());

        $form = $this->createFormBuilder()
            ->add('Amount', 'text', array())
            ->add('Accounts', 'entity', array(
                'empty_value' => 'select a account',
                'empty_data' => '',
                'class' => 'HelloDiDiDistributorsBundle:Account',
                'property' => 'NamewithCurrency',
                'required' => true,
                'query_builder' => function (EntityRepository $er) use ($Account) {
                    return $er->createQueryBuilder('Acc')
                        ->Where('Acc.Entiti = :Ent')->setParameter('Ent', $Account->getEntiti())
                        ->andWhere('Acc.accType =0')
                        ->andWhere('Acc.accCurrency=:Cur')->setParameter('Cur', $Account->getAccCurrency());
                }
            ))
            ->add('Description', 'textarea', array('required' => true))
            ->add('Communications', 'textarea', array('required' => true))
            ->getForm();

        $tranprov = new Transaction();
        $trandist = new Transaction();

        $tranprov->setTranBookingValue(null);
        $tranprov->setTranDate(new \DateTime('now'));
        $tranprov->setTranInsert(new \DateTime('now'));

        $trandist->setTranBookingValue(null);
        $trandist->setTranDate(new \DateTime('now'));
        $trandist->setTranInsert(new \DateTime('now'));

        $trandist->setTranAction('tran');
        $tranprov->setTranAction('tran');

        if ($req->isMethod('POST')) {
            $form->handleRequest($req);
            $data = $form->getData();

            #transaction for prov#

            $tranprov->setTranAmount(-$data['Amount']);
            $tranprov->setAccount($Account);
            $tranprov->setUser($User);
            $tranprov->setTranDescription($data['Description']);
            $tranprov->setTranFees(0);
            $tranprov->setTranCurrency($Account->getAccCurrency());
            $tranprov->setTranType(0);
            $tranprov->setTranBalance($Account->getAccBalance());
            #0 for debit

            #transaction for dist#
            $trandist->setTranAmount(+$data['Amount']);
            $trandist->setTranType(1); #1 for credit
            $trandist->setAccount($data['Accounts']);
            $trandist->setUser($User);
            $trandist->setTranDescription($data['Communications']);
            $trandist->setTranFees(0);
            $trandist->setTranCurrency($data['Accounts']->getAccCurrency());
            $trandist->setTranBalance($data['Accounts']->getAccBalance());

            if ($data['Amount'] > 0) {
                $em->persist($trandist);
                $em->persist($tranprov);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'this operation done success !');
            }
            else
                $this->get('session')->getFlashBag()->add('error', 'zero isn,t accept!');
        }


        return $this->render('HelloDiDiDistributorsBundle:Account:ProvTranTransfer.html.twig', array(
            'Account' => $Account,
            'User' => $User,
            'Entity' => $Account->getEntiti(),
            'form' => $form->createView(),
            'CountProv' => $countisprov
        ));


    }


    public function  ProvRegisterAction($id, Request $Req)
    {
        $AccountBalance = $this->get('hello_di_di_distributors.balancechecker');
        $em = $this->getDoctrine()->getEntityManager();

        $User = $this->get('security.context')->getToken()->getUser();
        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $tran = new Transaction();

        $form = $this->createFormBuilder()
            ->add('CreditDebit', 'choice', array(
                'expanded' => true,
                'choices' => array(

                    1 => 'Credit:',
                    0 => 'Debit:'
                )

            ))
            ->add('Amount', 'text', array(
                'required' => true, 'label' => 'Amount:',
            ))
            ->add('TradeDate', 'text', array('label' => 'Trade Date:'))
            ->add('Description', 'textarea', array('required' => true, 'label' => 'Description:',))
            ->add('Fees', 'text', array('required' => false, 'label' => 'Fees:',))->getForm();

        if ($Req->isMethod('POST')) {
            $form->submit($Req);
            $data = $form->getData();

            $tran->setTranCurrency($Account->getAccCurrency());
            $tran->setUser($User);
            $tran->setAccount($Account);
            $tran->setTranDate(new \DateTime('now'));
            $tran->setTranInsert(new \DateTime('now'));
            $tran->setTranBalance($Account->getAccBalance());
            $tran->setTranDescription($data['Description']);

            if ($data['Fees'] != '')
                $tran->setTranFees($data['Fees']);
            else
                $tran->setTranFees(0);

            if ($data['Amount'] > 0) {
                switch ($data['CreditDebit']) {
                    case 1:
                        $tran->setTranAction('pmt');
                        $tran->setTranType(1);
                        $tran->setTranAmount(+$data['Amount']);
                        $em->persist($tran);
                        $em->flush();
                        $this->get('session')->getFlashBag()->add('success', 'this operation done success !');
                        break;


                    case 0:
                        $tran->setTranAction('amdt');
                        $tran->setTranType(0);
                        $tran->setTranAmount(-$data['Amount']);
                        $em->persist($tran);
                        $em->flush();
                        $this->get('session')->getFlashBag()->add('success', 'this operation done success !');

                        break;

                }
            } else
                $this->get('session')->getFlashBag()->add('error', 'more than zero is accept !');
        }
        return $this->render('HelloDiDiDistributorsBundle:Account:ProvTranRegister.html.twig',
            array(
                'form' => $form->createView(),
                'Account' => $Account,
                'User' => $User,
                'Entity' => $Account->getEntiti(),
            ));

    }

    public function PurchasesAction($id, Request $req)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $User = $this->get('security.context')->getToken()->getUser();
        $paginator = $this->get('knp_paginator');

        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);


        $qb = array();

        $form = $this->createFormBuilder()
            ->add('DateStart', 'text', array('disabled' => false, 'required' => false, 'label' => 'From:'))
            ->add('DateEnd', 'text', array('disabled' => false, 'required' => false, 'label' => 'To:'))
            ->add('ItemType', 'choice',
                array('label' => 'Type:', 'choices' =>
                array(
                    'All' => 'All',
                    'dmtu' => 'Mobile',
                    'clcd' => 'calling card',
                    'empt' => 'e-payment',

                )))

            ->add('ItemName', 'entity',
                array('label' => 'Name:',
                    'empty_data' => '',
                    'empty_value' => 'All',
                    'required' => false,
                    'class' => 'HelloDiDiDistributorsBundle:Item',
                    'property' => 'itemName',
                    'query_builder' => function (EntityRepository $er) use ($Account) {
                        return $er->createQueryBuilder('i')
                            ->innerJoin('i.Prices', 'ip')
                            ->where('ip.priceStatus = 1')
                            ->andwhere('ip.Account = :Acc')->setParameter('Acc', $Account);

                    }
                ))->getForm();

        if ($req->isMethod('POST')) {
            $form->handleRequest($req);
            $data = $form->getData();

            $qb = $em->createQueryBuilder();
//            ,Count(Tr.tranDate) as c
        $qb->select('Tr as TR, count(Tr.Code) as Quantity,abs(Tr.tranAmount)as BuyingPrice')
                ->from('HelloDiDiDistributorsBundle:Transaction','Tr')
                ->innerJoin('Tr.Account', 'TrAcc')
                ->innerJoin('Tr.Code', 'TrCo')
                ->innerJoin('TrCo.Item', 'TrCoIt')
                ->where($qb->expr()->like('Tr.tranAction', $qb->expr()->literal('com')));
            if ($data['DateStart'] != '')
                $qb->andWhere('Tr.tranDate >= :DateStart')->setParameter('DateStart', $data['DateStart']);
            if ($data['DateEnd'] != '')
                $qb->andWhere('Tr.tranDate <= :DateEnd')->setParameter('DateEnd', $data['DateEnd']);
            if ($data['ItemType'] != 'All')
                $qb->andWhere('TrCoIt.itemType = :ItemType')->setParameter('ItemType', $data['ItemType']);
            if ($data['ItemName'])
                $qb->andWhere($qb->expr()->like('TrCoIt.itemName ', $qb->expr()->literal($data['ItemName']->getItemName())));

            $qb->groupBy('TrCo.Item')->addGroupBy('BuyingPrice');

            $qb->addOrderBy('Tr.tranDate', 'desc');

            $qb = $qb->getQuery();
            $qb = $qb->getResult();

//            $count = count($qb->getResult());
//            $qb->setHint('knp_paginator.count', $count);

        }


//        $pagination = $paginator->paginate(
//            $qb,
//            $req->get('page', 1) /*page number*/,
//            10/*limit per page*/
//        );

        return $this->render('HelloDiDiDistributorsBundle:Account:Purchases.html.twig', array(
            'pagination' => $qb,
            'Account' => $Account,
            'User' => $User,
            'Entity' => $Account->getEntiti(),
            'form' => $form->createView()
        ));
    }


    public function AddAccountDistMasterAction(Request $request)
    {

        $entitimaster = $this->get('security.context')->getToken()->getUser()->getEntiti();

        if (!$entitimaster) throw $this->createNotFoundException('Unable to find Entiti entity.');

        $Account = new Account();

        $form = $this->createForm(new AccountDistMasterType(), $Account);

        if ($request->isMethod('POST')) {

            $form->submit($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $Account->setEntiti($entitimaster);
                $Account->setAccCreationDate(new \DateTime('now'));
                $Account->setAccBalance(0);
                $Account->setAccType(0);
                $Account->setAccCreditLimit(0);
                $em->persist($Account);
                $em->flush();
                return $this->redirect($this->generateUrl('ShowMyAccountDist'));
            }

        }

        return $this->render('HelloDiDiDistributorsBundle:Account:AddAccountDistMaster.html.twig',
            array(
                'form' => $form->createView()
            ));


    }

    public function ShowMyAccountDistAction(Request $request)
    {


        $em = $this->getDoctrine()->getManager();
        $query = $em->getRepository('HelloDiDiDistributorsBundle:Account')->findBy(array('accType' => 0));
        return $this->render('HelloDiDiDistributorsBundle:Account:ShowMyAccountDist.html.twig', array
        ('pagination' => $query));


    }

////////////////////

    public function ManageDistAction($id)
    {
        return $this->render('HelloDiDiDistributorsBundle:Account:ManageDist.html.twig', array('id' => $id));
    }

///////////////////
    public function ManageDistInfoAction(Request $request)
    {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $edit_form = $this->createForm(new AccountDistMasterType(), $Account);
        return $this->render('HelloDiDiDistributorsBundle:Account:ManageDistInfo.html.twig',
            array('edit_form' => $edit_form->createView(), 'Account' => $Account));

    }


    public function ManageDistChildrenAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder()
            ->add('accName', 'text', array('required' => false))
            ->add('accCreditLimit', 'choice', array('choices' => (array(0 => 'Have Not', 1 => 'Have'))))
            ->add('accBalance', 'choice', array('choices' => (array(0 => 'Lower Than', 1 => 'More Than'))))
            ->add('accBalanceValue', 'text', array('required' => false))->getForm();
        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $query = array();
        $query = $Account->getChildrens();

        if ($request->isMethod('post')) {
            $form->handleRequest($request);
            $data = $form->getData();
            $qb = $em->createQueryBuilder()
                ->select('Acc')
                ->from('HelloDiDiDistributorsBundle:Account', 'Acc')
                ->Where('Acc.Parent=:AccountParent')
                ->setParameter('AccountParent', $Account);
            if ($data['accName'] != null)
                $qb->andWhere($qb->expr()->like('Acc.accName', $qb->expr()->literal($data['accName'] . '%')));

            if ($data['accCreditLimit'] == 0)
                $qb->andwhere($qb->expr()->eq('Acc.accCreditLimit', 0));

            if ($data['accCreditLimit'] == 1)
                $qb->andwhere($qb->expr()->gt('Acc.accCreditLimit', 0));


            if ($data['accBalance'] == 1)
                if ($data['accBalanceValue'] != '')
                    $qb->andwhere($qb->expr()->gte('Acc.accBalance', $data['accBalanceValue']));

            if ($data['accBalance'] == 0)
                if ($data['accBalanceValue'] != '')
                    $qb->andwhere($qb->expr()->lte('Acc.accBalance', $data['accBalanceValue']));

            $query = $qb->getQuery();

            $count = count($query->getResult());
            $query->setHint('knp_paginator.count', $count);

        }


        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->get('page', 1) /*page number*/,
            10/*limit per page*/
        );

        return $this->render('HelloDiDiDistributorsBundle:Account:ManageDistChildren.html.twig',
            array(
                'form' => $form->createView(),
                'pagination' => $pagination,
                'Account' => $Account));

    }


    public function ManageDistUserAction(Request $request, $id)
    {
        $paginator = $this->get('knp_paginator');

        $em = $this->getDoctrine()->getManager();


        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $result = $Account->getUsers();


        $qb = $em->createQueryBuilder();
        $qb->select('Usr')
            ->from('HelloDiDiDistributorsBundle:User', 'Usr')
            ->where('Usr.Account = :Acc')->setParameter('Acc', $Account);
        $qb = $qb->getQuery();

        return $this->render('HelloDiDiDistributorsBundle:Account:ManageDistUser.html.twig',

            array(
                'pagination' => $qb->getResult(),
                'Account' => $Account
            ));

    }

    public function ManageDistSettingsAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();
        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $form_edit = $this->createForm(new EditDistType, $Account);


        if ($request->isMethod('POST')) {
            $form_edit->handleRequest($request);
            if ($form_edit->isValid()) {
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'this operation done success !');
            }

        }


        return $this->render('HelloDiDiDistributorsBundle:Account:ManageDistSettings.html.twig',
            array('form_edit' => $form_edit->createView(),
                'Account' => $Account
            ));

    }


    public function ManageDistInfoEditAction(Request $request)
    {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $edit_form = $this->createForm(new AccountDistMasterType(), $Account);

        if ($request->isMethod('POST')) {

            $edit_form->handleRequest($request);
            if ($edit_form->isValid()) {

                $em->flush();
                return $this->forward("HelloDiDiDistributorsBundle:Account:ManageDistInfo");


            }
        }
        return $this->render('HelloDiDiDistributorsBundle:Account:ManageDistInfo.html.twig', array('edit_form' => $edit_form->createView(), 'Account' => $Account));
    }

    //items prov
    public function ManageItemsProvAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $prices = $account->getPrices();

        return $this->render('HelloDiDiDistributorsBundle:Account:ManageItemsProv.html.twig', array(
            'Account' => $account,
            'prices' => $prices
        ));
    }

    public function AddItemProvAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $price = new Price();
        $price->setPriceCurrency($account->getAccCurrency());
        $price->setPriceStatus(1);
        $price->setIsFavourite(false);
        $price->setAccount($account);

        $form = $this->createFormBuilder($price)
            ->add('Item', 'entity', array(
                'class' => 'HelloDiDiDistributorsBundle:Item',
                'property' => 'itemName',
                'query_builder' => function (EntityRepository $er) use ($account) {
                    return $er->createQueryBuilder('u')
                        ->where('u.id NOT IN (
                            SELECT DISTINCT ii.id
                            FROM HelloDiDiDistributorsBundle:Item ii
                            JOIN ii.Prices pp
                            JOIN pp.Account aa
                            WHERE aa = :aaid
                        )')
                        ->setParameter('aaid', $account);
                }
            ))
            ->add('price')
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($price);

                $pricehistory = new PriceHistory();
                $pricehistory->setDate(new \DateTime('now'));
                $pricehistory->setPrice($price->getPrice());
                $pricehistory->setPrices($price);
                $em->persist($pricehistory);

                $em->flush();
                return $this->forward('HelloDiDiDistributorsBundle:Account:ManageItemsProv', array(
                    'id' => $price->getAccount()->getId()
                ));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Account:AddItemProv.html.twig', array(
            'Account' => $account,
            'form' => $form->createView()
        ));
    }

    public function EditItemProvAction(Request $request, $itemid)
    {
        $em = $this->getDoctrine()->getManager();
        $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->find($itemid);
        $oldprice = $price->getPrice();

        $form = $this->createForm(new PriceEditType(), $price);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                if ($price->getPrice() != $oldprice) {
                    $pricehistory = new PriceHistory();
                    $pricehistory->setDate(new \DateTime('now'));
                    $pricehistory->setPrice($price->getPrice());
                    $pricehistory->setPrices($price);
                    $em->persist($pricehistory);
                }
                $em->flush();

                return $this->forward('HelloDiDiDistributorsBundle:Account:ManageItemsProv', array(
                    'id' => $price->getAccount()->getId()
                ));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Account:EditItemProv.html.twig', array(
            'Account' => $price->getAccount(),
            'price' => $price,
            'form' => $form->createView()
        ));
    }

    //items dist
    public function ManageItemsDistAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $prices = $account->getPrices();

        return $this->render('HelloDiDiDistributorsBundle:Account:ManageItemsDist.html.twig', array(
            'Account' => $account,
            'prices' => $prices
        ));
    }

    public function AddItemDistAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $price = new Price();
        $price->setPriceCurrency($account->getAccCurrency());
        $price->setPriceStatus(1);
        $price->setIsFavourite(false);
        $price->setAccount($account);

        $form = $this->createFormBuilder($price)
            ->add('Item', 'entity', array(
                'class' => 'HelloDiDiDistributorsBundle:Item',
                'property' => 'itemName',
                'query_builder' => function (EntityRepository $er) use ($account) {
                    return $er->createQueryBuilder('u')
                        ->where('u.id NOT IN (
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
                            WHERE aaa.accType = 1
                        )')
                        ->setParameter('aaid', $account);
                }
            ))
            ->add('price')
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($price);

                $pricehistory = new PriceHistory();
                $pricehistory->setDate(new \DateTime('now'));
                $pricehistory->setPrice($price->getPrice());
                $pricehistory->setPrices($price);
                $em->persist($pricehistory);

                $em->flush();
                return $this->forward('HelloDiDiDistributorsBundle:Account:ManageItemsDist', array(
                    'id' => $price->getAccount()->getId()
                ));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Account:AddItemDist.html.twig', array(
            'Account' => $account,
            'form' => $form->createView()
        ));
    }

    public function EditItemDistAction(Request $request, $id, $itemid)
    {
        $em = $this->getDoctrine()->getManager();
        $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->find($itemid);
        $oldprice = $price->getPrice();

        $form = $this->createForm(new PriceEditType(), $price);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                if ($price->getPrice() != $oldprice) {
                    $pricehistory = new PriceHistory();
                    $pricehistory->setDate(new \DateTime('now'));
                    $pricehistory->setPrice($price->getPrice());
                    $pricehistory->setPrices($price);
                    $em->persist($pricehistory);
                }
                if ($price->getPriceStatus() == 0) {
                    $RetAccs = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id)->getChildrens()->toArray();
                    $em->createQueryBuilder()
                        ->update('HelloDiDiDistributorsBundle:Price', 'pr')
                        ->where('pr.Account IN (:retaccs)')->setParameter('retaccs', $RetAccs)
                        ->andWhere('pr.Item = :item')->setParameter('item', $price->getItem())
                        ->set("pr.priceStatus", 0)
                        ->getQuery()
                        ->execute();
                }
                $em->flush();

                return $this->forward('HelloDiDiDistributorsBundle:Account:ManageItemsDist', array(
                    'id' => $price->getAccount()->getId()
                ));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Account:EditItemDist.html.twig', array(
            'Account' => $price->getAccount(),
            'price' => $price,
            'form' => $form->createView()
        ));
    }

    //Inputs prov
    public function ManageInputsProvAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $qb = $em->createQueryBuilder()
            ->select('input')
            ->from('HelloDiDiDistributorsBundle:Input', 'input')
            ->innerJoin('input.Account', 'a')
            ->where('a = :aaid')
            ->setParameter('aaid', $account);

        $form = $this->createFormBuilder()
            ->add('From', 'date', array('required' => false, 'widget' => 'single_text', 'format' => 'yyyy/MM/dd'))
            ->add('To', 'date', array('required' => false, 'widget' => 'single_text', 'format' => 'yyyy/MM/dd'))
            ->add('item', 'entity', array(
                'required' => false,
                'empty_value' => 'All',
                'class' => 'HelloDiDiDistributorsBundle:Item',
                'property' => 'itemName',
                'query_builder' => function (EntityRepository $er) use ($account) {
                    return $er->createQueryBuilder('i')
                        ->innerJoin('i.Prices', 'p')
                        ->innerJoin('p.Account', 'a')
                        ->where('a = :aaid')
                        ->setParameter('aaid', $account);
                }
            ))
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $data = $form->getData();

            $qb->join('input.Item', 'item');

            if ($data['item'] != null)
                $qb = $qb->andWhere($qb->expr()->eq('item', intval($data['item']->getId())));

            if ($data['From'] != "")
                $qb = $qb->andWhere("input.dateInsert >= :fromdate")->setParameter('fromdate', $data['From']);

            if ($data['To'] != "")
                $qb = $qb->andWhere("input.dateInsert <= :todate")->setParameter('todate', $data['To']);

        }

        $inputs = $qb->getQuery()->getResult();

        return $this->render('HelloDiDiDistributorsBundle:Account:ManageInputsProv.html.twig', array(
            'form' => $form->createView(),
            'Account' => $account,
            'inputs' => $inputs
        ));
    }

    public function CalcPriceAction($account, $item, $count)
    {
        $em = $this->getDoctrine()->getManager();
        $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Account' => $account, 'Item' => $item));
        if ($price)
            return new Response($price->getPrice() * $count);
        else
            return new Response('--');
    }

    public function UploadInputProvAction($id, $itemid)
    {
        $em = $this->getDoctrine()->getManager();
        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $Item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($itemid);

        $form = $this->createFormBuilder()
            ->add('File', 'file')
            ->add('Batch', 'text', array('required' => false))
            ->add('ProductionDate', 'date', array('widget' => 'single_text', 'format' => 'yyyy/MM/dd', 'data' => new \DateTime('now')))
            ->add('ExpireDate', 'date', array('widget' => 'single_text', 'format' => 'yyyy/MM/dd', 'data' => new \DateTime('now')))
            ->add('delimiter', 'choice', array('choices' => (array(';' => ';', ',' => ',', ' ' => 'Space', '-' => '-'))))
            ->add('SerialNumber', 'text', array('data' => '1', 'label' => 'Column Number Pin'))
            ->add('PinCode', 'text', array('data' => '4', 'label' => 'Column Number SN'))
            ->getForm();

        return $this->render('HelloDiDiDistributorsBundle:Account:UploadInputProv.html.twig', array(
            'item' => $Item,
            'Account' => $Account,
            'form' => $form->createView()
        ));
    }

    public function UploadInputProvSubmitAction(Request $request,$id, $itemid)
    {
        $em = $this->getDoctrine()->getManager();

        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $Item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($itemid);

        $form = $this->createFormBuilder()
            ->add('File', 'file')
            ->add('Batch', 'text', array('required' => false))
            ->add('ProductionDate', 'date', array('widget' => 'single_text', 'format' => 'yyyy/MM/dd'))
            ->add('ExpireDate', 'date', array('widget' => 'single_text', 'format' => 'yyyy/MM/dd'))
            ->add('delimiter', 'choice', array('choices' => (array(';' => ';', ',' => ',', ' ' => 'Space', '-' => '-'))))
            ->add('SerialNumber', 'text', array('label' => 'Column Number Pin'))
            ->add('PinCode', 'text', array('label' => 'Column Number SN'))
            ->getForm();

        $form->handleRequest($request);
        $data = $form->getData();

        $haserror = false;
        if (!is_numeric($data['Batch']))
        {
            $haserror = true;
            $this->get('session')->getFlashBag()->add('error', 'Batch is not valid.');
        }
        if (!is_numeric($data['SerialNumber']))
        {
            $haserror = true;
            $this->get('session')->getFlashBag()->add('error', 'SerialNumber is not valid.');
        }
        if (!is_numeric($data['PinCode']))
        {
            $haserror = true;
            $this->get('session')->getFlashBag()->add('error', 'PinCode is not valid.');
        }

        if (!$haserror) {
            $em = $this->getDoctrine()->getManager();

            $input = new Input();
            $input->setFile($data['File']);
            $input->upload();
            $input->setBatch($data['Batch']);
            $input->setItem($Item);
            $input->setDateProduction($data['ProductionDate']);
            $input->setDateExpiry($data['ExpireDate']);

            $fileName = $input->getFileName();
            $inputfind = $em->getRepository('HelloDiDiDistributorsBundle:Input')->findOneBy(array('fileName' => $fileName));
//          $f= fopen("d:\\a.txt","w+");
            if (!$inputfind) {
                try {
                    $file = fopen($input->getAbsolutePath(), 'r+');

                    if ($line = fgets($file)) {
                        $ok = true;
                        $count = 1;
                        while ($line = fgets($file)) {
                            $count++;
                            $lineArray = explode($data['delimiter'], $line);
//                        fwrite($f,$count.','.$lineArray[$data['SerialNumber'] - 1].'\n');
                            $codefind = $em->getRepository('HelloDiDiDistributorsBundle:Code')->findOneBy(
                                array('serialNumber' => $lineArray[$data['SerialNumber'] - 1])
                            );
                            if ($codefind) {
                                $this->get('session')->getFlashBag()->add('error', 'Codes are duplicate.');
                                $ok = false;
                                break;
                            }
                        }
                        if ($ok) {
                            $request->getSession()->set('upload_Name', $input->getFileName());
                            $request->getSession()->set('upload_Itemid', $input->getItem()->getId());
                            $request->getSession()->set('upload_Batch', $data['Batch']);
                            $request->getSession()->set('upload_Production', $data['ProductionDate']);
                            $request->getSession()->set('upload_Expiry', $data['ExpireDate']);
                            $request->getSession()->set('upload_delimiter', $data['delimiter']);
                            $request->getSession()->set('upload_SerialNumber', $data['SerialNumber']);
                            $request->getSession()->set('upload_PinCode', $data['PinCode']);
                            $request->getSession()->set('upload_accountid', $id);

                            return $this->render(
                                'HelloDiDiDistributorsBundle:Account:UploadInputProvSubmit.html.twig',
                                array(
                                    'Account' => $Account,
                                    'count' => $count,
                                    'input' => $input
                                )
                            );
                        }
                    } else {
                        $this->get('session')->getFlashBag()->add('error', 'File is empty.');
                    }
                } catch (\Exception $ex) {
                    $this->get('session')->getFlashBag()->add('error', 'Error in Reading File.');
                }
            } else {
                $this->get('session')->getFlashBag()->add('error', 'File is duplicate.');
            }
        }
        return $this->forward('HelloDiDiDistributorsBundle:Account:UploadInputProv', array(
            'id' => $id,
            'itemid' => $itemid
        ));
    }

    public function UploadInputProvSubmitAcceptedAction(Request $request)
    {
        $filename = $request->getSession()->get('upload_Name');
        $itemid = $request->getSession()->get('upload_Itemid');
        $batch = $request->getSession()->get('upload_Batch');
        $production = $request->getSession()->get('upload_Production');
        $expiry = $request->getSession()->get('upload_Expiry');
        $delimiter = $request->getSession()->get('upload_delimiter');
        $SerialNumber = $request->getSession()->get('upload_SerialNumber');
        $PinCode = $request->getSession()->get('upload_PinCode');
        $accountid = $request->getSession()->get('upload_accountid');

        $em = $this->getDoctrine()->getManager();
        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($accountid);
        $Item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($itemid);

        $user = $this->get('security.context')->getToken()->getUser();

        $input = new Input();
        $input->setFileName($filename);
        $input->setItem($Item);
        $input->setBatch($batch);
        $input->setDateProduction($production);
        $input->setDateExpiry($expiry);
        $input->setDateInsert(new \DateTime('now'));
        $input->setAccount($Account);
        $input->setUser($user);
        $em->persist($input);
//        $f= fopen("d:\\b.txt","w+");
        $file = fopen($input->getAbsolutePath(), 'r+');
//        $count = 0;
        while ($line = fgets($file)) {
//            $count++;
            $lineArray = explode($delimiter, $line);
//            fwrite($f,$count.','.$lineArray[$SerialNumber - 1].'\n');
            $code = new Code();
            $code->setSerialNumber($lineArray[$SerialNumber - 1]);
            $code->setPin($lineArray[$PinCode - 1]);
            $code->setStatus(1);
            $code->setItem($input->getItem());
            $code->setInput($input);
            $em->persist($code);

            $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Item'=>$Item,'Account'=>$Account))->getPrice();

            $transaction = new Transaction();
            $transaction->setCode($code);
            $transaction->setAccount($Account);
            $transaction->setTranAmount($price);
            $transaction->setUser($user);
            $transaction->setTranDate(new \DateTime('now'));
            $transaction->setTranInsert(new \DateTime('now'));
            $transaction->setTranAction('add');
            $transaction->setTranCurrency($Account->getAccCurrency());
            $transaction->setTranFees(0);
            $transaction->setTranType(1);
            $transaction->setTranBookingValue(null);
            $transaction->setTranBalance($Account->getAccBalance());
            $transaction->setTranDescription('add new code with serial '.$code->getSerialNumber().' to system.');
            $em->persist($transaction);

        }
        $em->flush();

        return $this->forward('HelloDiDiDistributorsBundle:Account:UploadInputProvSubmitCanceled');
    }

    public function UploadInputProvSubmitCanceledAction(Request $request)
    {
        $accountid = $request->getSession()->get('upload_accountid');
        $itemid = $request->getSession()->get('upload_Itemid');

        $request->getSession()->remove('upload_Name');
        $request->getSession()->remove('upload_Itemid');
        $request->getSession()->remove('upload_Batch');
        $request->getSession()->remove('upload_Production');
        $request->getSession()->remove('upload_Expiry');
        $request->getSession()->remove('upload_delimiter');
        $request->getSession()->remove('upload_SerialNumber');
        $request->getSession()->remove('upload_PinCode');
        $request->getSession()->remove('upload_accountid');

        return $this->redirect($this->generateUrl('ManageInputsProv', array(
            'id' => $accountid,
            'itemid' => $itemid
        )));
    }

    // kamal Prov Start


    public function MasterProvTransactionAction(Request $request, $id)
    {

        $datetype = 0;
        $em = $this->getDoctrine()->getEntityManager();
        $paginator = $this->get('knp_paginator');
        $form = $this->createFormBuilder()
            ->add('TypeDate', 'choice', array(
                'expanded' => true,
                'choices' => array(
                    0 => 'Trade Date',
                    1 => 'Looking Date',
                )))
            ->add('FromDate', 'text', array('disabled' => false, 'required' => false))
            ->add('ToDate', 'text', array('disabled' => false, 'required' => false))
            ->add('type', 'choice', array('label' => 'Type:',
                'choices' => array(
                    2 => 'All',
                    1 => 'Credit',
                    0 => 'Debit'
                )))

            ->add('Action', 'choice', array('label' => 'Action:',
                'choices' => array(
                    'All' => 'All',
                    'add' => 'add new codes to system',
                    'pmt' => 'credit provider,s account',
                    'amdt' => 'an amount is credited to correct the price of a code',
                    'amdt' => 'an amount is debited to correct the price of a code',
                    'rmv' => 'remove codes from to system',
                    'amdt' => 'debit provider,s account',
                    'tran' => 'transfer credit from provider,s account to a distributor,s account',

                )))->getForm();

        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $qb = array();

        if ($request->isMethod('post')) {

            $form->handleRequest($request);
            $data = $form->getData();
            $qb = $em->createQueryBuilder();

            $qb->select('Tr')
                ->from('HelloDiDiDistributorsBundle:Transaction', 'Tr')
                ->where('Tr.Account = :Acc')->setParameter('Acc', $Account);
            if ($data['TypeDate'] == 0) {
                if ($data['FromDate'] != '')
                    $qb->andwhere('Tr.tranDate >= :transdateFrom')->setParameter('transdateFrom', $data['FromDate']);
                if ($data['ToDate'] != '')
                    $qb->andwhere('Tr.tranDate <= :transdateTo')->setParameter('transdateTo', $data['ToDate']);
            } elseif ($data['TypeDate'] == 1) {
                $datetype = 1;
                if ($data['FromDate'] != '')
                    $qb->andwhere('Tr.tranInsert >= :transdateFrom')->setParameter('transdateFrom', $data['FromDate']);
                if ($data['ToDate'] != '')
                    $qb->andwhere('Tr.tranInsert <= :transdateTo')->setParameter('transdateTo', $data['ToDate']);
            }

            if ($data['type'] != 2)
                $qb->andWhere($qb->expr()->eq('Tr.tranType', $data['type']));


            if ($data['Action'] != 'All')
                $qb->andWhere($qb->expr()->like('Tr.tranAction', $qb->expr()->literal($data['Action'])));

            $qb->addOrderBy('Tr.tranInsert', 'desc')->addOrderBy('Tr.id','desc');

            $qb = $qb->getQuery();

            $count = count($qb->getResult());
            $qb->setHint('knp_paginator.count', $count);

        }


//die('sas'.$request->get('page'));

        $pagination = $paginator->paginate(
            $qb,
            $request->get('page', 1), /*page number*/
            10/*limit per page*/
        );


        return $this->render('HelloDiDiDistributorsBundle:Account:ProvTransactionMaster.html.twig',
            array(
                'Trans' => $pagination,
                'Account' => $Account,
                'form' => $form->createView(),
                'datetype' => $datetype
            ));

    }

#kazem alan



    public function MasterProvTransactionDeleteAction($tranid)
    {
        $em = $this->getDoctrine()->getManager();


        $tran = $em->getRepository('HelloDiDiDistributorsBundle:Transaction')->find($tranid);

        $em->remove($tran);
        $em->flush();

        return $this->redirect($this->generateUrl('MasterProvTransaction', array('id' => $tran->getAccount()->getId())));


    }

#end alan


    public function MasterProvRemovedAction(Request $request, $id)
    {


        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $searchForm = $this->createFormBuilder()
            ->add('FromDate', 'date', array('required' => false, 'format' => 'yyyy/MM/dd', 'widget' => 'single_text'))
            ->add('ToDate', 'date', array('required' => false, 'format' => 'yyyy/MM/dd', 'widget' => 'single_text'))
            ->add('item', 'entity', array(
                'required' => false,
                'empty_value' => 'All',
                'class' => 'HelloDiDiDistributorsBundle:Item',
                'property' => 'itemName',
                'query_builder' => function (EntityRepository $er) use ($account) {
                    return $er->createQueryBuilder('i')
                        ->innerJoin('i.Prices', 'p')
                        ->innerJoin('p.Account', 'a')
                        ->where('a = :aaid')
                        ->setParameter('aaid', $account);
                }
            ))
            ->getForm();

        $qb = $em->createQueryBuilder()
            ->select('trans')
            ->from('HelloDiDiDistributorsBundle:Transaction', 'trans')
            ->innerJoin('trans.Code', 'code')
            ->innerJoin('code.Input', 'input')
            ->innerJoin('input.Account', 'acc')
            ->where('trans.tranAction =:check')
            ->setParameter('check', 'removed')
            ->andwhere('acc = :check2')
            ->setParameter('check2', $account);


        if ($request->isMethod('POST')) {

            $searchForm->handleRequest($request);
            $data = $searchForm->getData();

            if ($data['FromDate'] != "")
                $qb = $qb->andWhere("trans.tranDate >= :transdateFrom")->setParameter('transdateFrom', $data['FromDate']);

            if ($data['ToDate'] != "")
                $qb = $qb->andWhere("trans.tranDate <= :transdateTo")->setParameter('transdateTo', $data['ToDate']);

            if ($data['item'] != "")
                $qb = $qb->andWhere('code.Item = :item')->setParameter('item', $data['item']);

        }
        $qb = $qb->getQuery();
        $accProv = $qb->getResult();
        $paginator = $this->get('knp_paginator');

        $accProv = $paginator->paginate(
            $accProv,
            $this->get('request')->query->get('page', 1) /*page number*/,
            20/*limit per page*/
        );
        return $this->render('HelloDiDiDistributorsBundle:Account:MasterProvRemoved.html.twig', array('id' => $id, 'Account' => $account, 'accProv' => $accProv, 'form' => $searchForm->createView()));
    }

    // kamal Prov End


    #start kazem


    public function DistUserAddAction(Request $req, $id)
    {


        $user = new User();
        $em = $this->getDoctrine()->getManager();

        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $form = $this->createForm(new NewUserType('HelloDiDiDistributorsBundle\Entity\User', 0), $user, array('cascade_validation' => true));


        if ($req->isMethod('POST')) {
            $form->handleRequest($req);
            if ($form->isValid()) {
                $user->setEntiti($Account->getEntiti());
                $user->setAccount($Account);
                $user->setEnabled(1);
                $em->persist($user);
                $em->flush();
                return $this->redirect($this->generateUrl('ManageDistUser', array('id' => $Account->getId())));

            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Account:DistUserAdd.html.twig', array(
                'Entiti' => $Account->getEntiti(),
                'Account' => $Account,
                'form' => $form->createView(),
            )
        );
    }


    public function DistUsereditAction(Request $req, $userid)
    {

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('HelloDiDiDistributorsBundle:User')->find($userid);
        $form = $this->createForm(new NewUserType('HelloDiDiDistributorsBundle\Entity\User', 0), $user, array('cascade_validation' => true));

        if ($req->isMethod('POST')) {
            $form->handleRequest($req);
            if ($form->isValid()) {
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'this operation done success !');

            }

        }

        return $this->render('HelloDiDiDistributorsBundle:Account:DistUserEdit.html.twig', array(
            'Account' => $user->getAccount(),
            'Entiti' => $user->getEntiti(),
            'User' => $user,
            'form' => $form->createView()));
    }


    public function  MasterProvEntitiAction($id)
    {

        $em = $this->getDoctrine()->getEntityManager();

        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $Entiti = $Account->getEntiti();

        return $this->render('HelloDiDiDistributorsBundle:Account:ManageProvEntiti.html.twig', array(
            'Account' => $Account,
            'entiti' => $Entiti
        ));

    }

    public function  MasterDistEntitiAction($id)
    {

        $em = $this->getDoctrine()->getEntityManager();

        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $Entiti = $Account->getEntiti();

        return $this->render('HelloDiDiDistributorsBundle:Account:ManageDistEntiti.html.twig', array(
            'Account' => $Account,
            'entiti' => $Entiti
        ));

    }


    public function  LoadActionProvAction(Request $req)
    {
        $id = $req->get('id', 0);
        $value = '';
        $value .= '<option value="All">' . 'All' . '</option>';

        switch ($id) {
            case 0:

                $value .= '<option value="rmv">' . 'remove codes from the system' . '</option>';
                $value .= '<option value="amdt">' . 'debit provider,s account' . '</option>';
                $value .= '<option value="amdt">' . 'an amount is debited to correct the price of a code' . '</option>';
                $value .= '<option value="tran">' . 'transfer credit from provider,s account to a distributor,s account' . '</option>';

                break;

            case 1:

                $value .= '<option value="add">' . 'add new codes to system' . '</option>';
                $value .= '<option value="pmt">' . 'credit provider,s account' . '</option>';
                $value .= '<option value="amdt">' . 'an amount is credited to correct the price of a code' . '</option>';

                break;

            case 2:
                $value .= '<option value="rmv">' . 'remove codes from the system' . '</option>';
                $value .= '<option value="amdt">' . 'debit provider,s account' . '</option>';
                $value .= '<option value="amdt">' . 'an amount is debited to correct the price of a code' . '</option>';
                $value .= '<option value="tran">' . 'transfer credit from provider,s account to a distributor,s account' . '</option>';

                $value .= '<option value="add">' . 'add new codes to system' . '</option>';
                $value .= '<option value="pmt">' . 'credit provider,s account' . '</option>';
                $value .= '<option value="amdt">' . 'an amount is credited to correct the price of a code' . '</option>';
                break;
        }
        return new Response($value);
    }


    public function  LoadActionDistAction(Request $req)
    {
        $id = $req->get('id', 0);
        $value = '';
        $value .= '<option value="All">' . 'All' . '</option>';

        switch ($id) {
            case 0:

                $value .= '<option value="amdt">' . 'debit distributor,s account' . '</option>';
                $value .= '<option value="tran">' . 'transfer credit from distributor,s account to a retailer,s account' . '</option>';
                $value .= '<option value="crnt">' . 'issue a credit note for a sold code' . '</option>';
                $value .= '<option value="com_pmt">' . 'debit distributor,s account for the commisson payments' . '</option>';

                break;

            case 1:

                $value .= '<option value="crlt">' . 'inscrease retailer,s credit limit' . '</option>';
                $value .= '<option value="pmt">' . 'credit distributor,s account' . '</option>';
                $value .= '<option value="tran">' . 'transfer credit from provider,s account to a distributor,s account' . '</option>';
                $value .= '<option value="pmt">' . 'ogone payment on its own account' . '</option>';
                $value .= '<option value="com">' . 'credit commissons when a retailer sells a code' . '</option>';


                break;

            case 2:
                $value .= '<option value="amdt">' . 'debit distributor,s account' . '</option>';
                $value .= '<option value="tran">' . 'transfer credit from distributor,s account to a retailer,s account' . '</option>';
                $value .= '<option value="crnt">' . 'issue a credit note for a sold code' . '</option>';
                $value .= '<option value="com_pmt">' . 'debit distributor,s account for the commisson payments' . '</option>';

                $value .= '<option value="crlt">' . 'inscrease retailer,s credit limit' . '</option>';
                $value .= '<option value="pmt">' . 'credit distributor,s account' . '</option>';
                $value .= '<option value="tran">' . 'transfer credit from provider,s account to a distributor,s account' . '</option>';
                $value .= '<option value="pmt">' . 'ogone payment on its own account' . '</option>';
                $value .= '<option value="com">' . 'credit commissons when a retailer sells a code' . '</option>';
                break;
        }
        return new Response($value);
    }






//    Master_Retailer




    public function MasterRetailerUserAction(Request $req,$id)
    {
//        $this->check_ChildAccount($id);

        $em = $this->getDoctrine()->getManager();
        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $users = $Account->getUsers();



        return $this->render('HelloDiDiDistributorsBundle:Master_Ratailer:RetailerUser.html.twig', array(
            'Entiti' => $Account->getEntiti(),
            'Account' => $Account->getParent(),
            'retailerAccount' => $Account,
            'Users' => $users
        ));

    }

    public function MasterRetailerUserEditAction(Request $request, $userid)
    {
//        $this->check_ChildUser($userid);
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('HelloDiDiDistributorsBundle:User')->find($userid);
        $form = $this->createForm(new \HelloDi\DiDistributorsBundle\Form\User\NewUserType('HelloDiDiDistributorsBundle\Entity\User',2), $user, array('cascade_validation' => true));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->flush();
                $this->get('session')->getFlashBag()->add('success','this operation done success !');
            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Master_Ratailer:RetailerUserEdit.html.twig', array(
            'retailerAccount' => $user->getAccount(),
            'Entiti' => $user->getEntiti(),
            'userid' => $userid,
            'Account'=>$user->getAccount()->getParent(),
            'form' => $form->createView()
        ));

    }

    public function MasterRetailerUserAddAction(Request $request, $id)
    {
//        $this->check_ChildAccount($id);
        $em = $this->getDoctrine()->getManager();
        $AccountRetailer =  $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $user = new User();


        $Entiti = $AccountRetailer->getEntiti();

        $form = $this->createForm(new \HelloDi\DiDistributorsBundle\Form\User\NewUserType('HelloDiDiDistributorsBundle\Entity\User',2),$user);


        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $user->setAccount($AccountRetailer);
            $user->setEntiti($Entiti);
            if ($form->isValid())
            {
                $em->persist($user);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success','this operation done success !');
                return $this->redirect($this->generateUrl('Master_RetailerUser', array('id' => $AccountRetailer->getId())));
            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Master_Ratailer:RetailerUserAdd.html.twig', array(
            'Entiti' => $Entiti,
            'retailerAccount' =>$AccountRetailer,
            'form' => $form->createView(),
            'Account'=>$AccountRetailer->getParent(),
        ));

    }


    public function MasterNewRetailerAction(Request $request)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $Account = $user->getAccount();

        $em = $this->getDoctrine()->getManager();
        $user= $this->get('security.context')->getToken()->getUser();
        $currency=$user->getAccount()->getAccCurrency();
        $user = new User();
        $AdrsDetai = new DetailHistory();
        $Entiti = new Entiti();
        $Account = new Account();

        $Account->setAccCreditLimit(0);
        $Account->setAccCreationDate(new \DateTime('now'));
        $Account->setAccTimeZone(null);
        $Account->setAccType(2);
        $Account->setAccBalance(0);


        $Account->setAccCurrency($currency);
        $Account->setParent($user->getAccount());


        $Account->setEntiti($Entiti);
        $Entiti->addAccount($Account);

        $user->setEntiti($Entiti);
        $Entiti->addUser($user);



        $user->setAccount($Account);
        $user->setStatus(1);


        $form = $this->createForm(new NewRetailersType(), $Entiti, array('cascade_validation' => true));

        if ($request->isMethod('POST')) {

            $form->handleRequest($request);

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
            $this->get('session')->getFlashBag()->add('success','this operation done success !');

            return $this->redirect($this->generateUrl('retailer_show',array('id',$user->getAccount()->getId())));



        }

        return $this->render('HelloDiDiDistributorsBundle:Distributors:NewRetailer.html.twig', array(
            'form_Relaited_New' => $form->createView(),
            'Account' => $Account
        ));

    }

    public function MasterRetailersTransactionAction(Request $req,$id)
    {
        $em=$this->getDoctrine()->getManager();

        $paginator = $this->get('knp_paginator');

//        $this->check_ChildAccount($id);

        $AccountRetailer=$em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $typedate=0;
        $qb=array();

        $form=$this->createFormBuilder()
            ->add('TypeDate','choice', array(
                'expanded'   => true,
                'choices'    => array(
                    0 => 'Trade Date',
                    1 => 'Looking Date',
                )

            ))
            ->add('DateStart','text',array('required'=>false,'label'=>'From:'))
            ->add('DateEnd','text',array('required'=>false,'label'=>'To:'))

            ->add('Type','choice',
                array('choices'=>
                array(
                    2=>'All',
                    0=>'Debit',
                    1=>'Credit',
                )))

            ->add('Action', 'choice', array('label'=>'Action:',
                'choices' =>
                array(
                    'All'=>'All',
                    'sale'=>'debit balance when the retailer sell a code',
                    'crnt'=>'issue a credit note for a sold code',
                    'tran'=>'transfer credit from distributor,s account to a retailer,s account',
                    'ogn_pmt'=>'ogone payment on its own account'
                )))

            ->getForm();


        if($req->isMethod('POST'))
        {
            $form->handleRequest($req);
            $data=$form->getData();
            $qb=$em->createQueryBuilder();
            $qb->select('Tran')
                ->from('HelloDiDiDistributorsBundle:Transaction','Tran')
                ->where('Tran.Account = :Acc')->setParameter('Acc',$AccountRetailer);
            if($data['TypeDate']==0)
            {
                if($data['DateStart']!='')
                    $qb->andwhere('Tran.tranDate >= :DateStart')->setParameter('DateStart',$data['DateStart']);
                if($data['DateEnd']!='')
                    $qb->andwhere('Tran.tranDate <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);

            }

            if($data['TypeDate']==1)
            {$typedate=1;
                if($data['DateStart']!='')
                    $qb->where('Tran.tranInsert >= :DateStart')->setParameter('DateStart',$data['DateStart']);
                if($data['DateEnd']!='')
                    $qb->andwhere('Tran.tranInsert <= :DateEnd')->setParameter('DateEnd',$data['DateEnd']);

            }

            if ($data['Type'] != 2)
                $qb->andWhere($qb->expr()->eq('Tran.tranType',$data['Type']));

            if($data['Action']!='All')
                $qb->andWhere($qb->expr()->like('Tran.tranAction',$qb->expr()->literal($data['Action'])));

            $qb->addOrderBy('Tran.tranInsert','desc')->addOrderBy('Tran.id','desc');;

            $qb=$qb->getQuery();
            $count = count($qb->getResult());
            $qb->setHint('knp_paginator.count', $count);
        }

        $pagination = $paginator->paginate(
            $qb,
            $req->get('page',1) /*page number*/,
            10/*limit per page*/
        );

        return $this->render('HelloDiDiDistributorsBundle:Master_Ratailer:RetailersTransaction.html.twig',
            array(
                'pagination'=>$pagination,
                'form'=>$form->createView(),
                'Account'=>$AccountRetailer->getParent(),
                'retailerAccount' => $AccountRetailer,
                'typedate'=>$typedate
            ));


    }




    public function MasterRetailerSettingAction(Request $req, $id)
    {
//        $this->check_ChildAccount($id);

        $em = $this->getDoctrine()->getManager();

        $RetailerAccount= $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $form = $this->createForm(new EditRetailerType(),$RetailerAccount);
        if ($req->isMethod('POST')) {
            $form->handleRequest($req);
            if ($form->isValid()) {
                $em->flush();
                $this->get('session')->getFlashBag()->add('success','this operation done success !');
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Master_Ratailer:RetailerSetting.html.twig', array(
            'Entiti' => $RetailerAccount->getEntiti(),
            'retailerAccount' => $RetailerAccount,
            'Account'=>$RetailerAccount->getParent(),
            'form' => $form->createView()
        ));
    }

    public function MasterRetailerDetailsAction(Request $req,$id)
    {
//        $this->check_ChildAccount($id);
        $em = $this->getDoctrine()->getManager();

        $RetailerAccount = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $entity = $RetailerAccount->getEntiti();

        $editForm = $this->createForm(new EditEntitiRetailerType(),$entity);

        if($req->isMethod('post'))
        {
            $editForm->handleRequest($req);
            if($editForm->isValid())
            {
                $em->flush();
                $this->get('session')->getFlashBag()->add('success','this operation done success !');
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Master_Ratailer:RetailersDetails.html.twig', array(

            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'retailerAccount' => $RetailerAccount,
            'Account'=>$RetailerAccount->getParent(),
        ));
    }


    public function  MasterRetailerFundingAction($id)
    {
//        $this->check_ChildAccount($id);

        $em=$this->getDoctrine()->getManager();

        $AccountRetailer=$em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $formapplay=$this->createFormBuilder()
            ->add('Amount',null,array())
            ->add('Communications','textarea',array('required'=>true))
            ->add('Description','textarea',array('required'=>true))
            ->getForm();

        $formupdate=$this->createFormBuilder()
            ->add('Amount','text',array())
            ->add('As','choice',array(
                'preferred_choices'=>array('Credit'),
                'choices'=>
                array(
                    1=>'Increase',
                    0=>'Decrease')
            ))->getForm();

        return $this->render('HelloDiDiDistributorsBundle:Master_Ratailer:Funding.html.twig',
            array(

                'Account'=>$AccountRetailer->getParent(),
                'retailerAccount'=>$AccountRetailer,
                'formapplay'=>$formapplay->createView(),
                'formupdate'=>$formupdate->createView(),

            ));
    }


    public function  MasterRetailerFundingTransferAction(Request $req,$id)
    {

//        $this->check_ChildAccount($id);


        $balancechecker=$this->get('hello_di_di_distributors.balancechecker');

        $User= $this->get('security.context')->getToken()->getUser();
        $em=$this->getDoctrine()->getManager();
        $AccountRetailer=$em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $formtransfer=$this->createFormBuilder()
            ->add('Amount')
            ->add('Communications','textarea',array('required'=>false))
            ->add('Description','textarea',array('required'=>false))
            ->getForm();

        if($req->isMethod('post'))
        {

            $trandist=new Transaction();
            $tranretailer=new Transaction();

            $formtransfer->handleRequest($req);

            $data=$formtransfer->getData();

            #transaction for dist#

            $trandist->setTranDate(new \DateTime('now'));
            $trandist->setTranCurrency($AccountRetailer->getParent()->getAccCurrency());
            $trandist->setTranInsert(new \DateTime('now'));
            $trandist->setAccount($AccountRetailer->getParent());
            $trandist->setUser($User);
            $trandist->setTranFees(0);
            $trandist->setTranAction('tran');
            $trandist->setTranType(0);
            $trandist->setTranBalance($AccountRetailer->getParent()->getAccBalance());
            $trandist->setTranDescription($data['Description']);





            #transaction for retailer#

            $tranretailer->setTranDate(new \DateTime('now'));
            $tranretailer->setTranCurrency($AccountRetailer->getAccCurrency());
            $tranretailer->setTranInsert(new \DateTime('now'));
            $tranretailer->setAccount($AccountRetailer);
            $tranretailer->setUser($User);
            $tranretailer->setTranFees(0);
            $tranretailer->setTranAction('tran');
            $tranretailer->setTranType(1);
            $tranretailer->setTranBalance($AccountRetailer->getAccBalance());
            $tranretailer->setTranDescription($data['Communications']);

            if($data['Amount']>0)
            {
                if($balancechecker->isBalanceEnoughForMoney($AccountRetailer->getParent(),$data['Amount']))
                {
                    $tranretailer->setTranAmount(+$data['Amount']);
                    $trandist->setTranAmount(-$data['Amount']);
                    $em->persist($trandist);
                    $em->persist($tranretailer);
                    $em->flush();
                    $this->get('session')->getFlashBag()->add('success','this operation done success !');
                }

            }
            else
                $this->get('session')->getFlashBag()->add('error','zero isn,t accept!');

        }

        return $this->redirect($this->generateUrl('Master_RetailerFunding',array('id'=>$id)));

    }

    public function  MasterRetailerFundingUpdateAction(Request $req,$id)
    {
//        $this->check_ChildAccount($id);
        $balancechecker=$this->get('hello_di_di_distributors.balancechecker');

        $User= $this->get('security.context')->getToken()->getUser();

        $em=$this->getDoctrine()->getManager();

        $AccountRetailer=$em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $formupdate=$this->createFormBuilder()
            ->add('Amount','text')
            ->add('As','choice',array('preferred_choices'=>array('Credit'),
                'choices'=>array(
                    1=>'Increase',
                    0=>'Decrease')
            ))->getForm();

        if($req->isMethod('POST'))
        {
            $formupdate->handleRequest($req);
            $data=$formupdate->getData();

            $trandist=new Transaction();

            $trandist->setTranDate(new \DateTime('now'));
            $trandist->setTranCurrency($AccountRetailer->getParent()->getAccCurrency());

            $trandist->setTranInsert(new \DateTime('now'));

            $trandist->setUser($User);
            $trandist->setTranFees(0);
            $trandist->setTranAction('crtl');
            $trandist->setTranBalance($AccountRetailer->getParent()->getAccBalance());
            $trandist->setTranType(0);
            $trandist->setAccount($AccountRetailer->getParent());
            if($data['Amount']>0)
            {

                if($data['As']==1)
                {
                    if($balancechecker->isBalanceEnoughForMoney($AccountRetailer->getParent(),$data['Amount']))
                    {
                        $trandist->setTranDescription('increase retailer,s credit limit ');
                        $trandist->setTranAmount(-$data['Amount']);
                        $AccountRetailer->setAccCreditLimit($AccountRetailer->getAccCreditLimit()+$data['Amount']);
                        $em->persist($trandist);
                        $em->flush();
                        $this->get('session')->getFlashBag()->add('success','this operation done success !');
                    }
                }

                elseif($data['As']==0)
                {

                    if($balancechecker->isAccCreditLimitPlus($AccountRetailer,$data['Amount']))
                    {
                        $AccountRetailer->setAccCreditLimit($AccountRetailer->getAccCreditLimit()- $data['Amount']);
                        $em->flush();
                        $this->get('session')->getFlashBag()->add('success','this operation done success !');
                    }
                }

            }
            else
                $this->get('session')->getFlashBag()->add('error','zero isn,t accept!');

        }
        return $this->redirect($this->generateUrl('Master_RetailerFunding',array('id'=>$id)));
    }


    public function MasterLoadActionRetailerAction(Request $req)
    {
        $id=$req->get('id',0);
        $value='';


        switch($id)
        {
            case 0:

                $value.='<option value="sale">'.'debit balance when the retailer sell a code'.'</option>';


                break;

            case 1:
                $value.='<option value="All">'.'All'.'</option>';
                $value.='<option value="crnt">'.'issue a credit note for a sold code'.'</option>';
                $value.='<option value="tran">'.'transfer credit from distributor,s account to a retailer,s account'.'</option>';
                $value.='<option value="ogn_pmt">'.'ogone payment on its own account'.'</option>';


                break;

            case 2:
                $value.='<option value="All">'.'All'.'</option>';
                $value.='<option value="sale">'.'debit balance when the retailer sell a code'.'</option>';
                $value.='<option value="crnt">'.'issue a credit note for a sold code'.'</option>';
                $value.='<option value="tran">'.'transfer credit from distributor,s account to a retailer,s account'.'</option>';
                $value.='<option value="ogn_pmt">'.'ogone payment on its own account'.'</option>';
                break;
        }
        return new Response($value);
    }





//    End Retailer
}
