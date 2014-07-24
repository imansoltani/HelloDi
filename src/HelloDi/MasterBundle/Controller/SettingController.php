<?php

namespace HelloDi\MasterBundle\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\CoreBundle\Entity\User;
use HelloDi\DiDistributorsBundle\Form\User\NewUserType;
use HelloDi\MasterBundle\Form\EntityType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SettingController extends Controller
{
    public function profileAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getUser()->getEntity();

        $form = $this->createForm(new EntityType(), $entity)
            ->add('update','submit', array(
                'label'=>'Update','translation_domain'=>'common',
                'attr'=>array('first-button','last-button')
            ))
        ;

        if ($request->isMethod('post')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully', array(), 'message'));
            }
        }

        return $this->render('HelloDiMasterBundle:setting:profile.html.twig', array(
                'form' => $form->createView()
            ));
    }

    public function staffAction()
    {
        $User = $this->get('security.context')->getToken()->getUser();
        $Users = $User->getEntiti()->getUsers();

        return $this->render('HelloDiDiDistributorsBundle:Setting:staff.html.twig', array(
            'Entiti' => $User->getEntiti(),
            'users' => $Users
        ));
    }

    public function staffaddAction(Request $req)
    {
        $user = new User();
        $Entiti = $this->getUser()->getEntiti();

        $form = $this->createForm(new NewUserType('HelloDiDiDistributorsBundle\Entity\User', 1), $user, array('cascade_validation' => true));

        if ($req->isMethod('POST')) {
            $form->handleRequest($req);
            $user->setStatus(1);
            if ($form->isValid()) {
                $user->setEntiti($Entiti);
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully', array(), 'message'));
                return $this->redirect($this->generateUrl('MasterStaff'));
            }
        }
        return $this->render('HelloDiDiDistributorsBundle:Setting:StaffAdd.html.twig', array(
            'Entiti' => $Entiti,
            'form' => $form->createView(),
        ));
    }

    public function staffeditAction(Request $req, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('HelloDiDiDistributorsBundle:User')->find($id);
        $form = $this->createForm(new NewUserType('HelloDiDiDistributorsBundle\Entity\User', 1), $user, array('cascade_validation' => true));

        if ($req->isMethod('POST')) {
            $form->handleRequest($req);
            if ($form->isValid()) {
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully', array(), 'message'));
                return $this->redirect($this->generateUrl('MasterStaff'));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Setting:StaffEdit.html.twig', array(
            'Entiti' => $user->getEntiti(),
            'userid' => $id,
            'form' => $form->createView()));
    }
}