<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use HelloDi\DiDistributorsBundle\Entity\User;
use HelloDi\DiDistributorsBundle\Form\User\NewUserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

class SettingController extends Controller
{

public  function staffAction(){

    $User=$this->get('security.context')->getToken()->getUser();
    $Users=$User->getEntiti()->getUsers();
    $paginator = $this->get('knp_paginator');
    $pagination = $paginator->paginate(
        $Users,
        $this->get('request')->query->get('page', 1) /*page number*/,
        6/*limit per page*/
    );
    return $this->render('HelloDiDiDistributorsBundle:Setting:staff.html.twig',array(
    'Entiti'=>$User->getEntiti(),
    'pagination'=>$pagination
    ));

}


public  function staffaddAction(Request $req,$id)
{


    $user = new User();
    $em = $this->getDoctrine()->getManager();
    $Entiti= $em->getRepository('HelloDiDiDistributorsBundle:Entiti')->find($id);


    $form = $this->createForm(new NewUserType('HelloDiDiDistributorsBundle\Entity\User'), $user, array('cascade_validation' => true));
    $formrole = $this->createFormBuilder()
        ->add('roles', 'choice',
            array(
                'choices' =>
                array(
                    'ROLE_MASTER' => 'ROLE_MASTER',
                    'ROLE_MASTER_ADMIN' => 'ROLE_MASTER_ADMIN'
                )))
        ->getForm();

    if ($req->isMethod('POST')) {
        $form->handleRequest($req);
        $formrole->handleRequest($req);
        $data = $formrole->getData();


        $user->setStatus(1);
        if ($form->isValid()) {
            $user->addRole(($data['roles']));
            $user->setEntiti($Entiti);
            $user->setEnabled(1);
            $em->persist($user);
            $em->flush();
            return $this->redirect($this->generateUrl('MasterStaff'));

        }

    }
    return $this->render('HelloDiDiDistributorsBundle:Setting:StaffAdd.html.twig', array(
    'Entiti'=>$Entiti,
    'form' => $form->createView(),
    'formrole' => $formrole->createView())
    );
}





public  function staffeditAction(Request $req,$id)
{

    $em = $this->getDoctrine()->getManager();
    $user = $em->getRepository('HelloDiDiDistributorsBundle:User')->find($id);
    $form = $this->createForm(new NewUserType('HelloDiDiDistributorsBundle\Entity\User'), $user, array('cascade_validation' => true));

    if ($req->isMethod('POST')) {
        $form->bind($req);
        if ($form->isValid()) {
            $em->flush();
            return $this->redirect($this->generateUrl('MasterStaff'));
        }

    }

    return $this->render('HelloDiDiDistributorsBundle:Setting:StaffEdit.html.twig', array(
    'Entiti' => $user->getEntiti(),
    'userid' => $id,
    'form' => $form->createView()));
}


public function changeroleAction($id)
{


    $em = $this->getDoctrine()->getManager();
    $user = $em->getRepository('HelloDiDiDistributorsBundle:User')->find($id);
    $roles = $user->getRoles();
    $role = $roles[0];
    switch ($role) {

        case 'ROLE_MASTER':
            $user->removeRole('ROLE_MASTER');
            $user->addRole('ROLE_MASTER_ADMIN');
            break;

        case 'ROLE_MASTER_ADMIN':
            $user->removeRole('ROLE_MASTER_ADMIN');
            $user->addRole('ROLE_MASTER');
            break;
    }

    $em->flush();
    return $this->redirect($this->generateUrl('MasterStaff'));

}


}