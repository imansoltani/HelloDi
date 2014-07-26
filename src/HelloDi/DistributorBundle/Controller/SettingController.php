<?php
namespace HelloDi\DistributorBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SettingController extends Controller
{
    public function profileAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $distributor = $em->getRepository('HelloDiDistributorBundle:Distributor')->findOneBy(array(
                'account' => $this->getUser()->getAccount()
            ));

        return $this->render('HelloDiDistributorBundle:setting:profile.html.twig', array(
                'distributor' => $distributor
            ));
    }

    public function staffAction()
    {
        $users = $this->getUser()->getAccount()->getUsers();

        return $this->render('HelloDiDiDistributorsBundle:Distributors:Staff.html.twig', array(
                'users' => $users
            ));
    }

    public function staffAddAction(Request $request)
    {
        $user = new User();
        $em = $this->getDoctrine()->getManager();
        $Account = $this->get('security.context')->getToken()->getUser()->getAccount();
        $Entiti = $Account->getEntiti();

        $form = $this->createForm(new \HelloDi\DiDistributorsBundle\Form\User\NewUserType('HelloDiDiDistributorsBundle\Entity\User'), $user);
        $formrole = $this->createFormBuilder()
            ->add('roles', 'choice', array('choices' => array('ROLE_DISTRIBUTOR' => 'ROLE_DISTRIBUTOR', 'ROLE_DISTRIBUTOR_ADMIN' => 'ROLE_DISTRIBUTOR_ADMIN')))->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $formrole->handleRequest($request);
            $data = $formrole->getData();
            $user->addRole(($data['roles']));
            $user->setAccount($Account);
            $user->setEntiti($Entiti);
            $user->setStatus(1);
            if ($form->isValid()) {
                $em->persist($user);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));

                return $this->redirect($this->generateUrl('DistStaff', array('id' => $Account->getId())));

            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Distributors:StaffAdd.html.twig',
            array(
                'Entiti' => $Account->getEntiti(),
                'Account' => $Account,
                'form' => $form->createView(),
                'formrole' => $formrole->createView()));

    }

    public function staffEditAction(Request $request, $user_id)
    {
        $this->check_User($id);


        $user = new User();
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('HelloDiDiDistributorsBundle:User')->find($id);
        $form = $this->createForm(new \HelloDi\DiDistributorsBundle\Form\User\NewUserType('HelloDiDiDistributorsBundle\Entity\User',0), $user, array('cascade_validation' => true));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->flush();
                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl('DistStaff', array('id' => $user->getAccount()->getId())));
            }

        }
        return $this->render('HelloDiDiDistributorsBundle:Distributors:StaffEdit.html.twig', array('Account' => $user->getAccount(), 'Entiti' => $user->getEntiti(), 'userid' => $id, 'form' => $form->createView()));

    }
}
