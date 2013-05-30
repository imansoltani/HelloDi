<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use HelloDi\DiDistributorsBundle\Entity\DetailHistory;
use HelloDi\DiDistributorsBundle\Entity\Entiti;
use HelloDi\DiDistributorsBundle\Form\Distributors\NewRetailersType;
use HelloDi\DiDistributorsBundle\Entity\User;
use HelloDi\DiDistributorsBundle\Form\Distributors\NewUserRetailersType;
use HelloDi\DiDistributorsBundle\Form\Distributors\NewUserDistributorsType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HelloDi\DiDistributorsBundle\Entity\Account;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

class DistributorsController extends Controller
{
    public function dashboardAction()
    {
        return $this->render('HelloDiDiDistributorsBundle:Distributors:dashboard.html.twig');
    }

    //Retailers
    public function ProfileAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $Account = $user->getAccount();
        return $this->render('HelloDiDiDistributorsBundle:Distributors:Profile.html.twig', array('Account' => $Account, 'Entiti' => $Account->getEntiti(), 'User' => $user));
    }

    public function StaffAction()
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

    public function StaffAddAction(Request $request, $id)
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

                return $this->redirect($this->generateUrl('Staff', array('id' => $Account->getId())));

            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Distributors:StaffAdd.html.twig', array('Entiti' => $Account->getEntiti(), 'Account' => $Account, 'form' => $form->createView(), 'formrole' => $formrole->createView()));

    }

    public function StaffEditAction(Request $request, $id)
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
                return $this->redirect($this->generateUrl('Staff', array('id' => $user->getAccount()->getId())));
            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Distributors:StaffEdit.html.twig', array('Account' => $user->getAccount(), 'Entiti' => $user->getEntiti(), 'userid' => $id, 'form' => $form->createView()));

    }

    public function ChangeRoleAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('HelloDiDiDistributorsBundle:User')->find($id);
        $role = $user->getRoles()[0];
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
        return $this->redirect($this->generateUrl('Staff', array('id' => $user->getAccount()->getId())));

    }

//---------click pn open list Retailers----------
    public function RetailerUserAction($id) //id Account
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

    public function RetailerUserEditAction(Request $request, $id)
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
                return $this->redirect($this->generateUrl('RetailerUser', array('id' => $user->getAccount()->getId())));
            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Distributors:RetailerUserEdit.html.twig', array('Account' => $user->getAccount(), 'Entiti' => $user->getEntiti(), 'userid' => $id, 'form' => $form->createView()));

    }

    public function RetailerUserAddAction(Request $request, $id)
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

                return $this->redirect($this->generateUrl('RetailerUser', array('id' => $Account->getId())));

            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Distributors:RetailerUserAdd.html.twig', array('Entiti' => $Account
            ->getEntiti(), 'Account' => $Account, 'form' => $form->createView(), 'formrole' => $formrole->createView()));

    }

    public function RetailerUserChangeRoleAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('HelloDiDiDistributorsBundle:User')->find($id);
        $role = $user->getRoles()[0];
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
        return $this->redirect($this->generateUrl('RetailerUser', array('id' => $user->getAccount()->getId())));

    }

    public function NewretailerAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $user = new User();
        $AdrsDetai = new DetailHistory();
        $Entiti = new Entiti();
        $Account = new Account();


        $Account->setAccCreationDate(new \DateTime('now'));
        $Account->setAccTimeZone(null);
        $Account->setAccType(2);
        $Account->setAccBalance(0);
        $Account->setAccCreditLimit(0);


        $Account->setEntiti($Entiti);
        $Entiti->addAccount($Account);

        $user->setEntiti($Entiti);
        $Entiti->addUser($user);


        $user->setAccount($Account);


        $form2step = $this->createForm(new NewRetailersType(), $Entiti, array('cascade_validation' => true));

        if ($request->isMethod('POST')) {
            $form2step->bind($request);

            if ($form2step->isValid()) {

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
                return new Response('dffd'); //  return $this->redirect($this->generateUrl('ShowMyAccount'));
            }

        }

        return $this->render('HelloDiDiDistributorsBundle:Distributors:NewRetailer.html.twig', array('form2step' => $form2step->createView()));

    }
}

