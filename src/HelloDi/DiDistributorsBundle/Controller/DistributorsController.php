<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use HelloDi\DiDistributorsBundle\Entity\Address;
use HelloDi\DiDistributorsBundle\Entity\Code;
use HelloDi\DiDistributorsBundle\Entity\DetailHistory;
use HelloDi\DiDistributorsBundle\Entity\Entiti;
use HelloDi\DiDistributorsBundle\Entity\Input;
use HelloDi\DiDistributorsBundle\Entity\Item;
use HelloDi\DiDistributorsBundle\Entity\Price;
use HelloDi\DiDistributorsBundle\Entity\PriceHistory;
use HelloDi\DiDistributorsBundle\Entity\Userprivilege;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistChildSearchType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistChildType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistMasterType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountProvType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountSearchDistType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountSearchProvType;
use HelloDi\DiDistributorsBundle\Form\Account\EntitiAccountprovType;
use HelloDi\DiDistributorsBundle\Entity\User;
use HelloDi\DiDistributorsBundle\Form\PriceEditType;
use HelloDi\DiDistributorsBundle\Form\PriceType;
use HelloDi\DiDistributorsBundle\Form\User\DistUserPrivilegeType;
use HelloDi\DiDistributorsBundle\Form\Distributors\NewUserDistributorsType;
use HelloDi\DiDistributorsBundle\Form\User\UserDistSearchType;
use HelloDi\DiDistributorsBundle\Form\User\UserRegistrationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HelloDi\DiDistributorsBundle\Entity\Account;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

class DistributorsController extends Controller
{
  #Retailers#

public  function ListUsersInRetailersAction(Request $req,$id)
{

 $paginator = $this->get('knp_paginator');
$em=$this->getDoctrine()->getManager();
$AccountRetailer=$em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
$listusers=$AccountRetailer->getUsers();
    $pagination = $paginator->paginate(
        $listusers,
        $this->get('request')->query->get('page', 1) /*page number*/,
        5/*limit per page*/
    );
    return $this->render('HelloDiDiDistributorsBundle:Distributors:users.html.twig', array('pagination'=>$pagination,'Account'=>$AccountRetailer,'Entity'=>$$AccountRetailer->getEntiti()));
}

public  function ProfileAction($id)
{
  $em=$this->getDoctrine()->getManager();
  $user=$em->getRepository('HelloDiDiDistributorsBundle:User')->find($id);
  $Account=$user->getAccount();
  return $this->render('HelloDiDiDistributorsBundle:Distributors:Profile.html.twig', array('Account'=>$Account,'Entiti'=>$Account->getEntiti(),'User'=>$user));
}
    public  function StaffAction($id)
    {
        $em=$this->getDoctrine()->getManager();
        $user=$em->getRepository('HelloDiDiDistributorsBundle:User')->find($id);
        $Account=$user->getAccount();
        $users=$Account->getUsers();
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $users,
            $this->get('request')->query->get('page', 1) /*page number*/,
            6/*limit per page*/
        );
        return $this->render('HelloDiDiDistributorsBundle:Distributors:Staff.html.twig', array('Account'=>$Account,'pagination'=>$pagination));
    }
public function StaffAddAction(Request $request,$id)
{
    $user=new User();
    $em = $this->getDoctrine()->getEntityManager();
    $Account=$em->getRepository('HelloDiDiDistributorsBundle:Account')->find($id);
    $Entiti=$Account->getEntiti();

        $form = $this->createForm(new NewUserDistributorsType('HelloDiDiDistributorsBundle\Entity\User'), $user, array('cascade_validation' => true));
        $formrole=$this->createFormBuilder()
            ->add('roles','choice',array('choices'=>array('ROLE_DISTRIBUTOR'=>'ROLE_DISTRIBUTOR','ROLE_DISTRIBUTOR_ADMIN'=>'ROLE_DISTRIBUTOR_ADMIN')))->getForm();

      if ($request->isMethod('POST')) {
        $form->bind($request);
        $formrole->bind($request);
        $data=$formrole->getData();
            $user->addRole(($data['roles']));
            $user->setAccount($Account);
            $user->setEntiti($Entiti);
            $user->setStatus(1);
          if ($form->isValid()) {
            $em->persist($user);
            $em->flush();

       return      $this->redirect($this->generateUrl('Staff',array('id'=>10))) ;

        }

    }
    return $this->render('HelloDiDiDistributorsBundle:Distributors:StaffAdd.html.twig', array('Account'=>$Account,'form' => $form->createView(),'formrole'=> $formrole->createView()));

}

public function  StaffEditAction(Request $request,$id)
{


    $user=new User();
    $em = $this->getDoctrine()->getManager();
    $user=$em->getRepository('HelloDiDiDistributorsBundle:User')->find($id);
    $form = $this->createForm(new NewUserDistributorsType('HelloDiDiDistributorsBundle\Entity\User'), $user, array('cascade_validation' => true));

    if ($request->isMethod('POST')) {
        $form->bind($request);
        if ($form->isValid()) {
            if($user->getStatus()==0)
                $user->setStatus(0);
                else
                    $user->setStatus(1);
                    $em->flush();
            return $this->redirect($this->generateUrl('Staff',array('id'=>10)));
        }

    }
    return $this->render('HelloDiDiDistributorsBundle:Distributors:StaffEdit.html.twig', array('userid'=>$id,'form' => $form->createView()));

}



    public function  ChangeRoleAction($id)
    {

        $em=$this->getDoctrine()->getManager();
        $user=$em->getRepository('HelloDiDiDistributorsBundle:User')->find($id);
        $role=$user->getRoles()[0];
        switch($role){

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
        return $this->redirect($this->generateUrl('Staff',array('id'=>10)));

    }

}

