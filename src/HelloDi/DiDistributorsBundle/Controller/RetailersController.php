<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\ORM\EntityRepository;
use \HelloDi\DiDistributorsBundle\Form\Retailers\NewUserRetailersType;
use HelloDi\DiDistributorsBundle\Entity\User;
use HelloDi\DiDistributorsBundle\Form\Distributors\NewUserDistributorsType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HelloDi\DiDistributorsBundle\Entity\Account;
use HelloDi\DiDistributorsBundle\Listener\BalanceChecker;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use HelloDi\DiDistributorsBundle\Entity\Transaction;
use HelloDi\DiDistributorsBundle\Form\TransactionType;


class RetailersController extends Controller
{
    public function dashboardAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $Account = $user->getAccount();
        return $this->render('HelloDiDiDistributorsBundle:Retailers:dashboard.html.twig',array(
            'Account' => $Account
        ));
    }

    //-----startkazem--------//

    public function RetailerProfileAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $Account = $user->getAccount();
        return $this->render('HelloDiDiDistributorsBundle:Retailers:RetailerProfile.html.twig', array('Account' => $Account, 'Entiti' => $Account->getEntiti(), 'User' => $user));
    }

    public function RetailerStaffAction()
    {
        $Account = $this->get('security.context')->getToken()->getUser()->getAccount();
        $users = $Account->getUsers();
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $users,
            $this->get('request')->query->get('page', 1) /*page number*/,
            6/*limit per page*/
        );
        return $this->render('HelloDiDiDistributorsBundle:Retailers:Staff.html.twig', array('Entiti' => $Account->getEntiti(), 'Account' => $Account, 'pagination' => $pagination));
    }

    public function RetailerStaffAddAction(Request $request, $id)
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

                return $this->redirect($this->generateUrl('RetailerStaff', array('id' => $Account->getId())));

            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Retailers:StaffAdd.html.twig', array('Entiti' => $Account->getEntiti(), 'Account' => $Account, 'form' => $form->createView(), 'formrole' => $formrole->createView()));

    }

    public function RetailerStaffEditAction(Request $request, $id)
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
                return $this->redirect($this->generateUrl('RetailerStaff', array('id' => $user->getAccount()->getId())));
            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Retailers:StaffEdit.html.twig', array('Account' => $user->getAccount(), 'Entiti' => $user->getEntiti(), 'userid' => $id, 'form' => $form->createView()));

    }

    public function RetailerChangeRoleAction(Request $req,$id)
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
        return $this->redirect($this->generateUrl('RetailerStaff', array('id' => $user->getAccount()->getId())));

    }

    public function TransactionAction(Request $req)
    {
        $User= $this->get('security.context')->getToken()->getUser();
        $Account=$User->getAccount();
        $em=$this->getDoctrine()->getManager();
        $query=$em->getRepository('HelloDiDiDistributorsBundle:Transaction')->findBy(array('Account'=>$Account,'User'=>$User));

        $form=$this->createFormBuilder()
            ->add('Type','choice',array('choices'=>array('All'=>'All','Sale'=>'Sale','Paym'=>'Payment','Cred'=>'CreditNotes','Tras'=>'Transfer','Add'=>'Add')))
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
                ->from('HelloDiDiDistributorsBundle:Transaction','Tran');
            if($data['TypeDate']==0)
            {

                $qb=$qb->where('Tran.tranDate >= :DateStart')->setParameter('DateStart',$data['DateStart']);
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

        return $this->render('HelloDiDiDistributorsBundle:Retailers:Transaction.html.twig', array('pagination'=>$pagination,'form'=>$form->createView(),'Account' =>$Account, 'Entiti' =>$User->getEntiti()));

    }



    ////function Report/Sale
    public  function SaleAction(Request $req)
    {
        $User= $this->get('security.context')->getToken()->getUser();

        $em=$this->getDoctrine()->getManager();
         $query=null;
        //load first list search

        $qb=$em->createQueryBuilder();
        $qb->select('Co')
            ->from('HelloDiDiDistributorsBundle:Code','Co')
            ->innerjoin('Co.Transactions','CoTr')
            ->where('Co.status=:st')->setParameter('st',0)
            ->andwhere('CoTr.User=:ur')->setParameter('ur',$User);
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
            ));

  $roles = $User->getRoles() ;
  if($roles[0]=='ROLE_RETAILER_ADMIN')
  {
      $form=$form->add('Staff', 'entity',
                array(
                'class' => 'HelloDiDiDistributorsBundle:User',
                'property' => 'username',
                 'empty_data'=>$User->getUsername(),
                'query_builder' => function(EntityRepository $er) use ($User) {
                    return $er->createQueryBuilder('u')
                           ->where('u.Account = :ua')
                           ->orderBy('u.username', 'ASC')
                           ->setParameter('ua',$User->getAccount());
//                    die('as'.count($er));
                }
                ));

  }


  $form=$form->add('DateStart','date',array())
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
                ->innerjoin('CoTr.User','CoTrUs')
                ->where('Co.status= 0')
                ->andwhere('CoTr.tranDate >= :DateStart')->setParameter('DateStart',$data['DateStart'])
                ->andwhere('CoTr.tranDate <= :DateEnd')->setParameter('DateEnd',$data['DateEnd'])
                ->andWhere($qb->expr()->like('CoTrUs.username',$qb->expr()->literal($data['Staff'].'%')));

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

        return $this->render('HelloDiDiDistributorsBundle:Retailers:ReportSales.html.twig',

            array(
            'pagination'=>$pagination,
            'form'=>$form->createView(),
             'User'=>$User,
            'Account' =>$User->getAccount(),
            'Entiti' =>$User->getEntiti()));

    }

//--------endkazem--------//

// Start kamal

    public function DmtuAction(){

        $em = $this->getDoctrine()->getManager();
        $Account = $this->container->get('security.context')->getToken()->getUser()->getAccount();
        $check = $Account->getId();
        $qb = $em->createQueryBuilder()
            ->select('item.itemName','item.id','operator.name','item.itemFaceValue','item.itemCurrency','price.id as pid')
            ->from('HelloDiDiDistributorsBundle:Account','acc')
            ->innerJoin('acc.Prices','price')
            ->innerJoin('price.Item','item')
            ->innerJoin('item.operator','operator')
            ->where('acc.id =:check')
            ->setParameter('check',$check)
            ->andwhere('item.itemType =:check2')
            ->setParameter('check2',0)
            ->OrderBy('item.itemName')


            ->getQuery();

        $item = $qb->getResult();

        $qb = $em->createQueryBuilder()
            ->select('DISTINCT operator.id','operator.name')
            ->from('HelloDiDiDistributorsBundle:Account','acc')
            ->innerJoin('acc.Prices','price')
            ->innerJoin('price.Item','item')
            ->innerJoin('item.operator','operator')
            ->where('acc.id =:check')
            ->setParameter('check',$check)
            ->andwhere('item.itemType =:check2')
            ->setParameter('check2',0)


            ->getQuery();
        $operator = $qb->getResult();

       return $this->render('HelloDiDiDistributorsBundle:Retailers:ShopDmtu.html.twig',array('itemlist' => $item , 'operator'=>$operator ,'account'=>$Account));
    }

    public function PrintCodeAction(Request $request){

//        $codeselector = $this->get('hello_di_di_distributors.codeselector');
//        $code = $codeselector->lookForAvailableCode($account, $price, $price->getItem());

        if($request->isMethod('POST')){
            //$logger = $this->get('logger'); // Log
            try{
                $em = $this->getDoctrine()->getManager();
                $logger = $this->get('logger');
                $logger->info('test1');
                $account = $this->get('security.context')->getToken()->getUser()->getAccount();
                $user = $this->get('security.context')->getToken()->getUser();
                $accountParent = $this->get('security.context')->getToken()->getUser()->getAccount()->getParent();
                $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->find($request->get('price_id'));
                $itemlist = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($request->get('item_id'));
                $codeselector = $this->get('hello_di_di_distributors.codeselector');
                $code = $codeselector->lookForAvailableCode($account, $price,$itemlist,$request->get('numberOfsale'));
                $priceParent = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Account'=>$accountParent));
                $tranProfit = $price->getprice() - $priceParent->getprice();

                foreach($code as $value){

                    $transaction = new Transaction($em);
                    $transaction->setAccount($account);
                    $transaction->setTranCredit($price->getPrice());
                    $transaction->setTranFees(0);
                    $transaction->setTranCurrency($price->getPriceCurrency());
                    $transaction->setTranDate(new \DateTime('now'));
                    $transaction->setCode($value);
                    $transaction->setTranAction('sale');
                    $transaction->setUser($user);
                    $em->persist($transaction);
                    $em->flush();
                    // For Parent
                    $transaction = new Transaction($em);
                    $transaction->setAccount($account);
                    $transaction->setTranCredit($tranProfit);
                    $transaction->setTranFees(0);
                    $transaction->setTranCurrency($price->getPriceCurrency());
                    $transaction->setTranDate(new \DateTime('now'));
                    $transaction->setCode($value);
                    $transaction->setTranAction('Profit');
                    $transaction->setUser($user);
                    $em->persist($transaction);
                    $em->flush();

                }
                return $this->render('HelloDiDiDistributorsBundle:Retailers:CodePrint.html.twig',array('code'=>$code));


            }
            catch(\Exception $e){
                print "A problem";
                return $this->render('HelloDiDiDistributorsBundle:Retailers:CodePrint.html.twig',array('code'=>null));
            }
        }

    }

    public function CallingCardAction() {

        $em = $this->getDoctrine()->getManager();

        $Account = $this->container->get('security.context')->getToken()->getUser()->getAccount();
        $check = $Account->getId();
        $qb = $em->createQueryBuilder()
            ->select('item.itemName','item.id','operator.name','item.itemFaceValue','item.itemCurrency','price.id as pid')
            ->from('HelloDiDiDistributorsBundle:Account','acc')
            ->innerJoin('acc.Prices','price')
            ->innerJoin('price.Item','item')
            ->innerJoin('item.operator','operator')
            ->where('acc.id =:check')
            ->setParameter('check',$check)
            ->andwhere('item.itemType =:check2')
            ->setParameter('check2',2)
            ->OrderBy('item.itemName')
            ->getQuery();
            $item = $qb->getResult();

        $qb = $em->createQueryBuilder()
            ->select('DISTINCT operator.id','operator.name')
            ->from('HelloDiDiDistributorsBundle:Account','acc')
            ->innerJoin('acc.Prices','price')
            ->innerJoin('price.Item','item')
            ->innerJoin('item.operator','operator')
            ->where('acc.id =:check')
            ->andwhere('item.itemType =:check2')
            ->setParameter('check2',2)
            ->setParameter('check',$check)
            ->getQuery();

            $operator = $qb->getResult();
        return $this->render('HelloDiDiDistributorsBundle:Retailers:CallingCard.html.twig',array('itemlist' => $item , 'operator'=>$operator,'account'=>$Account));

    }

    public function FavouritesAction(){
        $em = $this->getDoctrine()->getManager();

        $Account = $this->container->get('security.context')->getToken()->getUser()->getAccount();
        $check = $Account->getId();
        $qb = $em->createQueryBuilder()
            ->select('item.itemName','item.id')
            ->from('HelloDiDiDistributorsBundle:Account','acc')
            ->innerJoin('acc.Prices','price')
            ->innerJoin('price.Item','item')
            ->innerJoin('item.operator','operator')
            ->where('acc.id =:check')
            ->setParameter('check',$check)
            ->andwhere('price.isFavourite =:check2')
            ->setParameter('check2',1)
            ->getQuery();

        $itemFavourite = $qb->getResult();
        return $this->render('HelloDiDiDistributorsBundle:Retailers:favourite.html.twig',array('listFavourite'=>$itemFavourite));

    }

    public  function FavouritesCodeAction($id){

        return $this->render('HelloDiDiDistributorsBundle:Retailers:favouriteCode.html.twig',array('test'=>$id));
    }
 // End kamal

//start mostafa
    public function ShowItemsAction()
    {
        $myaccount = $this->get('security.context')->getToken()->getUser()->getAccount();

        $prices = $myaccount->getPrices();

        return $this->render('HelloDiDiDistributorsBundle:Retailers:items.html.twig', array(
                'prices' => $prices,
                'Account' => $myaccount
            ));
    }

    public function SwitchFavoriteItemAction($priceid)
    {
        $em = $this->getDoctrine()->getManager();
        $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->find($priceid);

        $price->setIsFavourite(!$price->getIsFavourite());
        $em->flush();

        return $this->redirect($this->generateUrl('Retailer_Items_Show'));
    }
//end mostafa
}

