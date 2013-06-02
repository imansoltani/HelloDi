<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Entity\Code;
use HelloDi\DiDistributorsBundle\Entity\DetailHistory;
use HelloDi\DiDistributorsBundle\Entity\Entiti;
use HelloDi\DiDistributorsBundle\Entity\Input;
use HelloDi\DiDistributorsBundle\Entity\Price;
use HelloDi\DiDistributorsBundle\Entity\PriceHistory;
use HelloDi\DiDistributorsBundle\Entity\Transaction;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistChildSearchType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistChildType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistMasterType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountProvType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountSearchDistType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountSearchProvType;
use HelloDi\DiDistributorsBundle\Form\Account\EntitiAccountprovType;
use HelloDi\DiDistributorsBundle\Form\PriceEditType;
use HelloDi\DiDistributorsBundle\Form\User\UserDistSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HelloDi\DiDistributorsBundle\Entity\Account;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

class AccountController extends Controller
{
    public function ShowMyAccountAction(Request $request)
    {
        $form_searchprov = $this->createForm(new AccountSearchProvType());
        $formsearch = $this->createForm(new AccountSearchProvType());

        $em = $this->getDoctrine()->getManager();

        $query = $em->getRepository('HelloDiDiDistributorsBundle:Account')->findBy(array('accType' => 1));

        if ($request->isMethod('POST')) {
            $formsearch->bind($request);
            $dataform = $formsearch->getData();

            $em = $this->getDoctrine()->getManager();

            $qb = $em->createQueryBuilder();

            $qb->select(array('Acc', 'Ent'))
                ->from('HelloDiDiDistributorsBundle:Account', 'Acc')
                ->innerJoin('Acc.Entiti', 'Ent')
                ->andwhere($qb->expr()->eq('Acc.accProv', 1));
            if ($dataform['accName'] != '')
                $qb->andwhere($qb->expr()->like('Acc.accName', $qb->expr()->literal($dataform['accName'] . '%')));
            if ($dataform['entName'])
                $qb->andwhere($qb->expr()->like('Ent.entName', $qb->expr()->literal($dataform['entName'] . '%')));
            if ($dataform['accBalance'] == 1)
                if ($dataform['accBalanceValue'])
                    $qb->andwhere($qb->expr()->gte('Acc.accBalance', $dataform['accBalanceValue']));
            if ($dataform['accBalance'] == 0)
                if ($dataform['accBalanceValue'])
                    $qb->andwhere($qb->expr()->lte('Acc.accBalance', $dataform['accBalanceValue']));
            if ($dataform['id'] != '')
                $qb->andwhere($qb->expr()->eq('Acc.id', $dataform['id']));
            $query = $qb->getQuery();

        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1) /*page number*/,
            5/*limit per page*/
        );
            return $this->render('HelloDiDiDistributorsBundle:Account:ShowMyAccount.html.twig', array
            ('pagination' => $pagination, 'form_searchprov' => $form_searchprov->createView()));

    }

//Master

    public function EditChildAccountAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $idp = $account->getparent()->getId();

        if (!$account) {
            throw $this->createNotFoundException('Unable to find Account entity.');
        }

        $editForm = $this->createForm(new AccountDistChildType(), $account);
        if ($request->isMethod('POST')) {

            $editForm->bind($request);
            if ($editForm->isValid()) {
                $em->flush();

                return $this->redirect($this->generateUrl('ShowChildAccount', array('id' => $idp)));

            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Account:EditChildAccount.html.twig', array(
            'account' => $account,
            'edit_form' => $editForm->createView(),
        ));

    }

    public function EditAccountAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $edit_form = $this->createForm(new AccountDistMasterType(), $account);
        if ($request->isMethod('POST')) {
            $edit_form->bind($request);
            if ($edit_form->isValid()) {

                $em->flush($account);
                return $this->redirect($this->generateUrl('ShowMyAccount'));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Account:EditAccount.html.twig', array(
            'account' => $account,
            'edit_form' => $edit_form->createView()
        ));
    }

    public function AddAccountProvMasterAction()
    {

        $em = $this->getDoctrine()->getManager();

        $entities =$em->getRepository('HelloDiDiDistributorsBundle:Entiti')->findAll();
        $paginator = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
            $entities,
            $this->get('request')->query->get('page', 1) /*page number*/,
            5/*limit per page*/
        );
        return $this->render('HelloDiDiDistributorsBundle:Account:AddAccountProvMaster.html.twig', array('pagination' => $pagination));


    }

    public function AddAccountProvMasterOkAction(Request $request, $id)
    {

        $Account = new Account();
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);
        $Form = $this->createForm(new AccountProvType(), $Account);

        $Form->bind($request);

        if ($Form->isValid()) {
            $Account->setEntiti($entity);
            $Account->setAccCreationDate(new \DateTime('now'));
            $Account->setAccTimeZone(null);
            $Account->setAccType(1);
            $Account->setAccBalance(0);
            $Account->setAccCreditLimit(0);
            $Account->setAccDefaultLanguage(null);
            $Account->setParent(null);
            $Account->setAccTerms(0);
            $Account->setAccTimeZone(null);
            $em->persist($Account);
            $em->flush();
            return $this->redirect($this->generateUrl('ShowMyAccount'));

        }

        return $this->render('HelloDiDiDistributorsBundle:Account:AddAccountProvMasterOk.html.twig', array(
            'entity' => $entity,
            'form' => $Form->createView(),
        ));


    }

    public function AddAccountProveMaster2StepAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $AdrsDetai=new DetailHistory();
        $Entiti = new Entiti();
        $Account = new Account();



        $Account->setAccCreationDate(new \DateTime('now'));
        $Account->setAccTimeZone(null);
        $Account->setAccType(1);
        $Account->setAccBalance(0);
        $Account->setAccCreditLimit(0);



        $Account->setEntiti($Entiti);
        $Entiti->addAccount($Account);

//        die('sdsd');
        $form2step = $this->createForm(new EntitiAccountprovType(), $Entiti, array('cascade_validation' => true));

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
                return $this->redirect($this->generateUrl('ShowMyAccount'));
            }

        }

        return $this->render('HelloDiDiDistributorsBundle:Account:AddAccountProvMaster2Step.html.twig', array('form2step' => $form2step->createView()));
    }

//SearchAccountDist

    public function ManageProvAction($id)
    {
        return $this->render('HelloDiDiDistributorsBundle:Account:ManageProv.html.twig', array('id' => $id));
    }

    public function EditAccountProvAction(Request $request)
    {

        $id = $request->get('accountid');
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $edit_form = $this->createForm(new AccountProvType(), $account);
        return $this->render('HelloDiDiDistributorsBundle:Account:EditAccountProv.html.twig', array(
                'edit_form' => $edit_form->createView(),
                'Account' => $account,
                'id' => $id)
        );
    }

    public function EditAccountProvSubmitAction(Request $request)
    {
        $id = $request->get('accountid');
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $edit_form = $this->createForm(new AccountProvType(), $account);
        $edit_form->bind($request);
        if ($edit_form->isValid()) {
            $account->setAccTimeZone(null);
            $em->flush($account);
        }
        return $this->forward("HelloDiDiDistributorsBundle:Account:EditAccountProv");
    }

    public function ManageProvEntitiAction(Request $request)
    {
        $id = $request->get('accountid');
        $em = $this->getDoctrine()->getManager();
        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        return $this->render('HelloDiDiDistributorsBundle:Account:ManageProvEntiti.html.twig', array(
            'entiti' => $Account->getEntiti(),
            'Account' => $Account
        ));

    }

//dist

    public function AddAccountDistMasterAction(Request $request)
    {
        $entitimaster = $this->get('security.context')->getToken()->getUser()->getEntiti();

        if (!$entitimaster) throw $this->createNotFoundException('Unable to find Entiti entity.');

        $Account = new Account();

        $Userprivilege = new Userprivilege();
        $form = $this->createForm(new AccountDistMasterType(), $Account);

        if ($request->isMethod('POST')) {

            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $Account->setEntiti($entitimaster);
                $Account->setAccCreationDate(new \DateTime('now'));
                $Account->setAccBalance(0);
                $Account->setAccStatus(1);
                $Account->setAccType(1);
                $em->persist($Account);
                $em->flush();
                return $this->redirect($this->generateUrl('ShowMyAccountDist'));
            }

        }

        return $this->render('HelloDiDiDistributorsBundle:Account:AddAccountDistMaster.html.twig', array('form' => $form->createView()));
    }

    public function ShowMyAccountDistAction(Request $request)
    {

        $form_searchdist = $this->createForm(new AccountSearchDistType());

        $em = $this->getDoctrine()->getManager();
        $query =$em->getRepository('HelloDiDiDistributorsBundle:Account')->findBy(array('accType'=>0));

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1) /*page number*/,
            5/*limit per page*/
        );
        if ($request->isMethod('POST')) {
            $form_searchdist->bind($request);
            $dataform = $form_searchdist->getData();


            $em = $this->getDoctrine()->getManager();
            $qb = $em->createQueryBuilder();

            $qb->select('Acc')
                ->from('HelloDiDiDistributorsBundle:Account', 'Acc')
                ->innerJoin('Acc.Entiti', 'AccEnt')

                ->where($qb->expr()->eq('Acc.accType', 0));
            if ($dataform['accName'] != '')
                $qb->andwhere($qb->expr()->like('Acc.accName', $qb->expr()->literal($dataform['accName'] . '%')));
            if ($dataform['entName'] != '')
                $qb->andwhere($qb->expr()->like('AccEnt.entName', $qb->expr()->literal($dataform['entName'] . '%')));
            if ($dataform['accCurrency'] != 2)
                $qb->andwhere($qb->expr()->eq('Acc.accCurrency', $dataform['accCurrency']));
            if ($dataform['accBalance'] == 1)
                if ($dataform['accBalanceValue'] != '')
                    $qb->andwhere($qb->expr()->gte('Acc.accBalance', $dataform['accBalanceValue']));
            if ($dataform['accBalance'] == 0)
                if ($dataform['accBalanceValue'] != '')
                    $qb->andwhere($qb->expr()->lte('Acc.accBalance', $dataform['accBalanceValue']));
            if ($dataform['accCreditLimit'] != 2)
                $qb->andwhere($qb->expr()->eq('Acc.accCreditLimit', $dataform['accCreditLimit']));

            $query = $qb->getQuery();

            $pagination = $paginator->paginate(
                $query,
                $this->get('request')->query->get('page', 1) /*page number*/,
                5/*limit per page*/
            );


        }

            return $this->render('HelloDiDiDistributorsBundle:Account:ShowMyAccountDist.html.twig', array
            ('pagination' => $pagination, 'form_searchdist' => $form_searchdist->createView()));


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

/////////////
    public function ManageDistChildrenAction(Request $request)
    {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $form_searchdistchild = $this->createForm(new AccountDistChildSearchType());
        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $ChildAccount = $Account->getChildrens();
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $ChildAccount,
            $this->get('request')->query->get('page', 1) /*page number*/,
            10/*limit per page*/
        );

        return $this->render('HelloDiDiDistributorsBundle:Account:ManageDistChildren.html.twig', array('form_searchdistchild' => $form_searchdistchild->createView(), 'pagination' => $pagination, 'id' => $id, 'Account' => $Account));

    }

///////////////
    public function AccountDistChildrenSearchAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();
        $AccountParent = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $form_searchdistchild = $this->createForm(new AccountDistChildSearchType());

        $form_searchdistchild->bind($request);
        $searchdistchilddata = $form_searchdistchild->getData();

        $qb = $em->createQueryBuilder();
        $qb->select('Acc')
            ->from('HelloDiDiDistributorsBundle:Account', 'Acc')
            ->Where('Acc.Parent=:AccountParent')
            ->setParameter('AccountParent', $AccountParent);
        if ($searchdistchilddata['accName'] != null)
            $qb->andWhere($qb->expr()->like('Acc.accName', $qb->expr()->literal($searchdistchilddata['accName'] . '%')));

        if ($searchdistchilddata['accCreditLimit'] == 0)
            $qb->andwhere($qb->expr()->eq('Acc.accCreditLimit', 0));

        if ($searchdistchilddata['accCreditLimit'] == 1)
            $qb->andwhere($qb->expr()->gt('Acc.accCreditLimit', 0));


        if ($searchdistchilddata['accBalance'] == 1)
            if ($searchdistchilddata['accBalanceValue'] != '')
                $qb->andwhere($qb->expr()->gte('Acc.accBalance', $searchdistchilddata['accBalanceValue']));

        if ($searchdistchilddata['accBalance'] == 0)
            if ($searchdistchilddata['accBalanceValue'] != '')
                $qb->andwhere($qb->expr()->lte('Acc.accBalance', $searchdistchilddata['accBalanceValue']));

        $query = $qb->getQuery();

        $count = count($query->getResult());
        $query = $query->setHint('knp_paginator.count', $count);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1) /*page number*/,
            10/*limit per page*/
        );

        return $this->render('HelloDiDiDistributorsBundle:Account:ManageDistChildren.html.twig', array('form_searchdistchild' => $form_searchdistchild->createView(), 'pagination' => $pagination, 'id' => $id, 'Account' => $AccountParent));
    }

    public function ManageDistUserAction(Request $request)
    {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();

        $form_searchdistuser = $this->createForm(new UserDistSearchType());

        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $usp = $Account->getUsers();

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $usp,
            $this->get('request')->query->get('page', 1) /*page number*/,
            10/*limit per page*/
        );

        return $this->render('HelloDiDiDistributorsBundle:Account:ManageDistUser.html.twig', array('pagination' => $pagination, 'form_searchdistuser' => $form_searchdistuser->createView(), 'Account' => $Account));

    }

    public function ManageDistUserSearchAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();
        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $form_searchdistuser = $this->createForm(new UserDistSearchType());

        $form_searchdistuser->bind($request);
        $searchdistuserdata = $form_searchdistuser->getData();

        $qb = $em->createQueryBuilder();
        $qb->select('U')
            ->from('HelloDiDiDistributorsBundle:User', 'U')
             ->where('U.Account =:MyAccount');
              if($searchdistuserdata['username']!='')
               $qb->andWhere($qb->expr()->like('U.username', $qb->expr()->literal($searchdistuserdata['username'] . '%')));
              $qb->setParameter('MyAccount',$Account);

        $query = $qb->getQuery();
        $count = count($query->getResult());
        $query = $query->setHint('knp_paginator.count', $count);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1) /*page number*/,
            10/*limit per page*/
        );


        return $this->render('HelloDiDiDistributorsBundle:Account:ManageDistUser.html.twig', array('pagination' => $pagination, 'form_searchdistuser' => $form_searchdistuser->createView(), 'Account' => $Account));


        //return  $this->forward('HelloDiDiDistributorsBundle:Account:ManageDistUser',array('resultuser'=>$resultsearch,'id'=>$id));

    }

    public function ManageDistUserSearchResetAction($id)
    {
        return $this->forward('HelloDiDiDistributorsBundle:Account:ManageDistUser', array('id' => $id));
    }

    public function ManageDistSettingsAction(Request $request)
    {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $form_edit = $this->createForm(new AccountDistChildType(), $Account);

        $myEntity = $this->get('security.context')->getToken()->getUser()->getEntiti();
            return $this->render('HelloDiDiDistributorsBundle:Account:ManageDistSettingsChild.html.twig', array('form_edit' => $form_edit->createView(), 'Account' => $Account));

    }

    public function ManageDistSettingsSubmitAction(Request $request)
    {


        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $form_edit = $this->createForm(new AccountDistChildType(), $Account);

        if ($request->isMethod('POST')) {
            $form_edit->bind($request);


            if ($form_edit->isValid()) {
                $em->flush();
            }


        }
        return $this->render('HelloDiDiDistributorsBundle:Account:ManageDistSettingsChild.html.twig', array('form_edit' => $form_edit->createView(), 'Account' => $Account));
    }

    public function DistUserPrivilegeAction(Request $request, $idacc, $iduser)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('HelloDiDiDistributorsBundle:User')->find($iduser);
        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($idacc);

//$form_distuserprivilege=$this->createForm(new DistUserPrivilegeType());
        $userprivilege = $em->getRepository('HelloDiDiDistributorsBundle:Userprivilege')->findOneBy(array('Account' => $account, 'User' => $user));
        $valueprivilege = $userprivilege->getPrivileges();
//
// if($request->isMethod('POST'))
//{
//$form_distuserprivilege->bind($request);
//$distuserprivilegedata=$form_distuserprivilege->getData();

        $userprivilege->setPrivileges(1 - $valueprivilege);
        $em->flush();


        return $this->forward('HelloDiDiDistributorsBundle:Account:ManageDistUser', array('id' => $idacc, 'resultuser' => null));

//}

//return $this->render('HelloDiDiDistributorsBundle:Account:DistUserPrivilege.html.twig',array('privilege'=>$valueprivilege,'Account'=>$account,'User'=>$user,'form_distuserprivilege'=>$form_distuserprivilege->createView()));


    }


    public function ManageDistInfoEditAction(Request $request)
    {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $edit_form = $this->createForm(new AccountDistMasterType(), $Account);

        if ($request->isMethod('POST')) {

            $edit_form->bind($request);
            if ($edit_form->isValid()) {

                $em->flush();
                return $this->forward("HelloDiDiDistributorsBundle:Account:ManageDistInfo");
                //return $this->redirect($this->generateUrl('ManageDist',array('id'=>$id)));

            }
        }
        return $this->render('HelloDiDiDistributorsBundle:Account:ManageDistInfo.html.twig', array('edit_form' => $edit_form->createView(), 'Account' => $Account));
    }

    //items prov
    public function ManageItemsProvAction(Request $request)
    {
        $id = $request->get('accountid');
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $prices = $account->getPrices();

        return $this->render('HelloDiDiDistributorsBundle:Account:ManageItemsProv.html.twig', array(
            'Account' => $account,
            'prices' => $prices
        ));
    }

    public function AddItemProvAction(Request $request)
    {
        $id = $request->get('accountid');
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $price = new Price();
        $price->setPriceCurrency($account->getAccCurrency());
        $price->setPriceStatus(1);
        $price->setAccount($account);

        $form = $this->createFormBuilder($price)
            ->add('Item', 'entity', array(
                'class' => 'HelloDiDiDistributorsBundle:Item',
                'property' => 'itemName',
                'query_builder' => function(EntityRepository $er) use ($account) {
                    return $er->createQueryBuilder('u')
                        ->where ('u.id NOT IN (
                            SELECT DISTINCT ii.id
                            FROM HelloDiDiDistributorsBundle:Item ii
                            JOIN ii.Prices pp
                            JOIN pp.Account aa
                            WHERE aa = :aaid
                        )')
                        ->setParameter('aaid',$account);
                }
            ))
            ->add('price')
            ->getForm();

        return $this->render('HelloDiDiDistributorsBundle:Account:AddItemProv.html.twig', array(
            'Account' => $account,
            'form' => $form->createView()
        ));
    }

    public function AddItemProvSubmitAction(Request $request)
    {
        $id = $request->get('accountid');
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $price = new Price();
        $price->setPriceCurrency($account->getAccCurrency());
        $price->setPriceStatus(1);
        $price->setAccount($account);

        $form = $this->createFormBuilder($price)
            ->add('Item', 'entity', array('class' => 'HelloDiDiDistributorsBundle:Item', 'property' => 'itemName'))
            ->add('price')
            ->getForm();
        $form->bind($request);
        if ($form->isValid()) {
            $em->persist($price);

            $pricehistory = new PriceHistory();
            $pricehistory->setDate(new \DateTime('now'));
            $pricehistory->setPrice($price->getPrice());
            $pricehistory->setPrices($price);
            $em->persist($pricehistory);

            $em->flush();
            return $this->forward('HelloDiDiDistributorsBundle:Account:ManageItemsProv', array(
                'accountid' => $price->getAccount()->getId()
            ));
        }
        return $this->render('HelloDiDiDistributorsBundle:Account:AddItemProv.html.twig', array(
            'Account' => $account,
            'form' => $form->createView()
        ));
    }

    public function EditItemProvAction(Request $request)
    {
        $id = $request->get('priceid');
        $em = $this->getDoctrine()->getManager();
        $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->find($id);
        $form = $this->createForm(new PriceEditType(), $price);

        return $this->render('HelloDiDiDistributorsBundle:Account:EditItemProv.html.twig', array(
            'Account' => $price->getAccount(),
            'price' => $price,
            'form' => $form->createView()
        ));
    }

    public function EditItemProvSubmitAction(Request $request)
    {
        $id = $request->get('priceid');
        $em = $this->getDoctrine()->getManager();
        $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->find($id);
        $oldprice = $price->getPrice();

        $form = $this->createForm(new PriceEditType(), $price);

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

            return $this->forward('HelloDiDiDistributorsBundle:Account:ManageItemsProv', array(
                'accountid' => $price->getAccount()->getId()
            ));
        }

        return $this->render('HelloDiDiDistributorsBundle:Account:EditItemProv.html.twig', array(
            'Account' => $price->getAccount(),
            'price' => $price,
            'form' => $form->createView()
        ));
    }

    //items dist
    public function ManageItemsDistAction(Request $request)
    {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
        $prices = $account->getPrices();

        return $this->render('HelloDiDiDistributorsBundle:Account:ManageItemsDist.html.twig', array(
            'Account' => $account,
            'prices' => $prices
        ));
    }

    public function AddItemDistAction(Request $request)
    {
        $id = $request->get('accountid');
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $price = new Price();
        $price->setPriceCurrency($account->getAccCurrency());
        $price->setPriceStatus(1);
        $price->setAccount($account);

        $form = $this->createFormBuilder($price)
            ->add('Item', 'entity', array(
                'class' => 'HelloDiDiDistributorsBundle:Item',
                'property' => 'itemName',
                'query_builder' => function(EntityRepository $er) use ($account) {
                    return $er->createQueryBuilder('u')
                        ->where ('u.id NOT IN (
                            SELECT DISTINCT ii.id
                            FROM HelloDiDiDistributorsBundle:Item ii
                            JOIN ii.Prices pp
                            JOIN pp.Account aa
                            WHERE aa = :aaid
                        )')
                        ->setParameter('aaid',$account);
                }
            ))
            ->add('price')
            ->getForm();

        return $this->render('HelloDiDiDistributorsBundle:Account:AddItemDist.html.twig', array(
            'Account' => $account,
            'form' => $form->createView()
        ));
    }

    public function AddItemDistSubmitAction(Request $request)
    {
        $id = $request->get('accountid');
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);

        $price = new Price();
        $price->setPriceCurrency($account->getAccCurrency());
        $price->setPriceStatus(1);
        $price->setAccount($account);

        $form = $this->createFormBuilder($price)
            ->add('Item', 'entity', array('class' => 'HelloDiDiDistributorsBundle:Item', 'property' => 'itemName'))
            ->add('price')
            ->getForm();

        $form->bind($request);
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
        return $this->render('HelloDiDiDistributorsBundle:Account:AddItemDist.html.twig', array(
            'Account' => $account,
            'form' => $form->createView()
        ));
    }

    public function EditItemDistAction(Request $request)
    {
        $id = $request->get('priceid');
        $em = $this->getDoctrine()->getManager();
        $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->find($id);
        $form = $this->createForm(new PriceEditType(), $price);

        return $this->render('HelloDiDiDistributorsBundle:Account:EditItemDist.html.twig', array(
            'Account' => $price->getAccount(),
            'price' => $price,
            'form' => $form->createView()
        ));
    }

    public function EditItemDistSubmitAction(Request $request)
    {
        $id = $request->get('priceid');
        $em = $this->getDoctrine()->getManager();
        $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->find($id);
        $oldprice = $price->getPrice();

        $form = $this->createForm(new PriceEditType(), $price);

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

            return $this->forward('HelloDiDiDistributorsBundle:Account:ManageItemsDist', array(
                'id' => $price->getAccount()->getId()
            ));
        }

        return $this->render('HelloDiDiDistributorsBundle:Account:EditItemDist.html.twig', array(
            'Account' => $price->getAccount(),
            'price' => $price,
            'form' => $form->createView()
        ));
    }

    //Inputs prov
    public function ManageInputsProvAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $accountid = $request->get('accountid');
        $account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($accountid);

        $qb = $em->createQueryBuilder()
            ->select('input')
            ->from('HelloDiDiDistributorsBundle:Input','input')
            ->innerJoin('input.Account','a')
            ->where('a = :aaid')
            ->setParameter('aaid',$account);

        $form = $this->createFormBuilder()
            ->add('From', 'date', array('required'=> false,'widget' => 'single_text','format' => 'yyyy/MM/dd'))
            ->add('To', 'date', array('required'=> false,'widget' => 'single_text','format' => 'yyyy/MM/dd'))
            ->add('item','entity',array(
                'required'=> false,
                'empty_value' => 'All',
                'class' => 'HelloDiDiDistributorsBundle:Item',
                'property' => 'itemName',
                'query_builder' => function(EntityRepository $er) use ($account) {
                    return $er->createQueryBuilder('i')
                        ->innerJoin('i.Prices','p')
                        ->innerJoin('p.Account','a')
                        ->where('a = :aaid')
                        ->setParameter('aaid',$account);
                }
            ))
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->bind($request);
            $data = $form->getData();

            $qb ->join('input.Item','item');

            if($data['item']!=null)
                $qb = $qb->andWhere($qb->expr()->eq('item',intval($data['item']->getId() )));

            if($data['From']!="")
                $qb = $qb->andWhere("input.dateInsert >= :fromdate")->setParameter('fromdate', $data['From']);

            if($data['To']!="")
                $qb = $qb->andWhere("input.dateInsert <= :todate")->setParameter('todate', $data['To']);

        }

        $inputs= $qb->getQuery()->getResult();

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

    public function UploadInputProvAction(Request $request, $errors = null)
    {
        $em = $this->getDoctrine()->getManager();

        $accountid = $request->get('accountid');
        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($accountid);

        $form = $this->createFormBuilder()
            ->add('File', 'file')
            ->add('Item', 'entity', array(
                'class' => 'HelloDiDiDistributorsBundle:Item',
                'property' => 'itemName',
                'query_builder' => function(EntityRepository $er) use ($Account) {
                    return $er->createQueryBuilder('i')
                        ->innerJoin('i.Prices','p')
                        ->innerJoin('p.Account','a')
                        ->where('a = :aaid')
                        ->setParameter('aaid',$Account)
                        ->andWhere('p.priceStatus = 1');
                }
            ))
            ->add('Batch', 'text',array('required'=>false))
            ->add('ProductionDate', 'date', array('widget' => 'single_text','format' => 'yyyy/MM/dd'))
            ->add('ExpireDate', 'date', array('widget' => 'single_text','format' => 'yyyy/MM/dd'))
            ->add('delimiter', 'choice', array('choices' => (array(';' => ';', ',' => ',', ' ' => 'Space', '-' => '-'))))
            ->add('SerialNumber', 'text', array('data' => '1', 'label' => 'Column Number Pin'))
            ->add('PinCode', 'text', array('data' => '4', 'label' => 'Column Number SN'))
            ->getForm();

        return $this->render('HelloDiDiDistributorsBundle:Account:UploadInputProv.html.twig', array(
            'Account' => $Account,
            'form' => $form->createView(),
            'errors' => $errors
        ));
    }

    public function UploadInputProvSubmitAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $accountid = $request->get('accountid');
        $Account = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($accountid);

        $form = $this->createFormBuilder()
            ->add('File', 'file')
            ->add('Item', 'entity', array('class' => 'HelloDiDiDistributorsBundle:Item', 'property' => 'itemName'))
            ->add('Batch', 'text',array('required'=>false))
            ->add('ProductionDate', 'date', array('widget' => 'single_text','format' => 'yyyy/MM/dd'))
            ->add('ExpireDate', 'date', array('widget' => 'single_text','format' => 'yyyy/MM/dd'))
            ->add('delimiter', 'choice', array('choices' => (array(';' => ';', ',' => ',', ' ' => 'Space', '-' => '-'))))
            ->add('SerialNumber', 'text', array('label' => 'Column Number Pin'))
            ->add('PinCode', 'text', array('label' => 'Column Number SN'))
            ->getForm();

        $form->bind($request);
        $data = $form->getData();

        $errors = null;
        if (!is_numeric($data['Batch'])) $errors[] = 'Batch is not valid';
        if (!is_numeric($data['SerialNumber'])) $errors[] = 'SerialNumber is not valid';
        if (!is_numeric($data['PinCode'])) $errors[] = 'PinCode is not valid';

        if (count($errors) == 0) {
            $em = $this->getDoctrine()->getManager();

            $input = new Input();
            $input->setFile($data['File']);
            $input->upload();
            $input->setBatch($data['Batch']);
            $input->setItem($data['Item']);
            $input->setDateProduction($data['ProductionDate']);
            $input->setDateExpiry($data['ExpireDate']);

            $fileName = $input->getFileName();
            $inputfind = $em->getRepository('HelloDiDiDistributorsBundle:Input')->findOneBy(array('fileName' => $fileName));

            if (!$inputfind) {
                $file = fopen($input->getAbsolutePath(), 'r+');

                if ($line = fgets($file)) {
                    $ok = true;
                    $count = 0;
                    while ($line = fgets($file)) {
                        $count++;
                        $lineArray = explode($data['delimiter'], $line);
                        $codefind = $em->getRepository('HelloDiDiDistributorsBundle:Code')->findOneBy(array('serialNumber' => $lineArray[$data['SerialNumber'] - 1]));
                        if ($codefind) {
                            $errors[] = "Codes are duplicate.";
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
                        $request->getSession()->set('upload_accountid', $accountid);

                        return $this->render('HelloDiDiDistributorsBundle:Account:UploadInputProvSubmit.html.twig', array(
                            'Account' => $Account,
                            'count' => $count,
                            'input' => $input
                        ));
                    }
                } else {
                    $errors[] = "File is empty.";
                }
            } else {
                $errors[] = "File is duplicate.";
            }
        }

        return $this->forward('HelloDiDiDistributorsBundle:Account:UploadInputProv', array(
            'accountid' => $accountid,
            'errors' => $errors
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

        $file = fopen($input->getAbsolutePath(), 'r+');

        while ($line = fgets($file)) {
            $lineArray = explode($delimiter, $line);

            $code = new Code();
            $code->setSerialNumber($lineArray[$SerialNumber - 1]);
            $code->setPin($lineArray[$PinCode - 1]);
            $code->setStatus(1);
            $code->setItem($input->getItem());
            $code->setInput($input);
            $em->persist($code);

            $transaction =  new Transaction();
            $transaction->setCode($code);
            $transaction->setAccount($Account);
            $transaction->setUser($user);
            $transaction->setTranDate(new \DateTime('now'));
            $transaction->setTranInsert(new \DateTime('now'));
            $transaction->setTranAction('Add');
            $transaction->setTranCurrency($Account->getAccCurrency());
            $transaction->setTranFees(0);
            $em->persist($transaction);
        }
        $em->flush();

        return $this->forward('HelloDiDiDistributorsBundle:Account:UploadInputProvSubmitCanceled');
    }

    public function UploadInputProvSubmitCanceledAction(Request $request)
    {
        $accountid = $request->getSession()->get('upload_accountid');

        $request->getSession()->remove('upload_Name');
        $request->getSession()->remove('upload_Itemid');
        $request->getSession()->remove('upload_Batch');
        $request->getSession()->remove('upload_Production');
        $request->getSession()->remove('upload_Expiry');
        $request->getSession()->remove('upload_delimiter');
        $request->getSession()->remove('upload_SerialNumber');
        $request->getSession()->remove('upload_PinCode');
        $request->getSession()->remove('upload_accountid');

        return $this->redirect($this->generateUrl('ManageInputsProv',array(
            'accountid' => $accountid
        )));
    }
}
