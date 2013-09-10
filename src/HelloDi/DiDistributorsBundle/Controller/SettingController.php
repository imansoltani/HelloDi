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

    return $this->render('HelloDiDiDistributorsBundle:Setting:staff.html.twig',array(
    'Entiti'=>$User->getEntiti(),
    'users'=>$Users
    ));

}


public  function staffaddAction(Request $req)
{


    $user = new User();
    $Entiti= $this->get('security.context')->getToken()->getUser()->getEntiti();


    $form = $this->createForm(new NewUserType('HelloDiDiDistributorsBundle\Entity\User',1), $user, array('cascade_validation' => true));

    if ($req->isMethod('POST')) {
        $form->handleRequest($req);

        $user->setStatus(1);
        if ($form->isValid()) {
            sds;
            $user->setEntiti($Entiti);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
            return $this->redirect($this->generateUrl('MasterStaff'));

        }

    }
    return $this->render('HelloDiDiDistributorsBundle:Setting:StaffAdd.html.twig', array(
    'Entiti'=>$Entiti,
    'form' => $form->createView(),
    )  );
}





public  function staffeditAction(Request $req,$id)
{

    $em = $this->getDoctrine()->getManager();
    $user = $em->getRepository('HelloDiDiDistributorsBundle:User')->find($id);
    $form = $this->createForm(new NewUserType('HelloDiDiDistributorsBundle\Entity\User',1), $user, array('cascade_validation' => true));

    if ($req->isMethod('POST')) {
        $form->handleRequest($req);
        if ($form->isValid()) {
            $em->flush();
            $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
            return $this->redirect($this->generateUrl('MasterStaff'));
        }

    }

    return $this->render('HelloDiDiDistributorsBundle:Setting:StaffEdit.html.twig', array(
    'Entiti' => $user->getEntiti(),
    'userid' => $id,
    'form' => $form->createView()));
}



}