<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use \HelloDi\DiDistributorsBundle\Form\Retailers\NewUserRetailersType;
use HelloDi\DiDistributorsBundle\Entity\User;
use HelloDi\DiDistributorsBundle\Form\Distributors\NewUserDistributorsType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HelloDi\DiDistributorsBundle\Entity\Account;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

class RetailersController extends Controller
{
    public function dashboardAction()
    {
        return $this->render('HelloDiDiDistributorsBundle:Retailers:dashboard.html.twig');
    }





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

public  function TransactionAction(Request $req)
{
    $User= $this->get('security.context')->getToken()->getUser();
    $Account=$User->getAccount();
$em=$this->getDoctrine()->geManager();
$query=$em->getRepository('HelloDiDiDistributorsBundle:Transaction')->findBy(array('Account'=>$Account,'User'=>$User));

$form=$this->createFormBuilder()
     ->add('Type','choice',array('choices'=>array()))
     ->add('DateStart','date')
      ->add('DateEnd','date')
      ->add('TypeDate','choice', array(
        'expanded'   => true,
        'choices'    => array(
            0 => 'Inactive',
            1 => 'Active',
        )
    ))->getForm();

if($req->isMethod('POST'))
{
    $form->bind($req);
$data=$form->getData();

    $qb=$em->createQueryBuilder()
        ->select('Tran')
        ->from('HelloDiDiDistributorsBundle:Transaction');
    if($data['TypeDate']==0)
    {
        $qb->where($qb->expr()->gte('Tran.tranDate',  $data['DateStart']));
        $qb->where($qb->expr()->lte('Acc.tranDate',  $data['DateStart']));
    }
    if($data['TypeDate']==1)
    {
        $qb->where($qb->expr()->gte('Acc.tranInsert',  $data['DateStart']));
        $qb->where($qb->expr()->lte('Acc.tranInsert',  $data['DateStart']));
    }

    if($data['Type']!='All')
    {
    $qb->andWhere('tranAction',$data['Type']);
    }

  $query=$qb->getQuery();

}

    $paginator = $this->get('knp_paginator');
    $pagination = $paginator->paginate(
        $query,
        $this->get('request')->query->get('page', 1) /*page number*/,
        5/*limit per page*/
    );




    return $this->render('HelloDiDiDistributorsBundle:Retailers:Transaction.html.twig', array('pagination'=>$pagination,'form'=>$form,'Account' =>$Account(), 'Entiti' =>$User->getEntiti(), 'form' => $form->createView()));
}


}

