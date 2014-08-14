<?php
namespace HelloDi\DistributorBundle\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\CoreBundle\Entity\User;
use HelloDi\UserBundle\Form\RegistrationFormType;
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

        return $this->render('HelloDiDistributorBundle:setting:staff.html.twig', array(
                'users' => $users
            ));
    }

    public function staffAddAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $user = new User();

        $languages = $this->container->getParameter('languages');

        $form = $this->createForm(new RegistrationFormType($languages, Account::DISTRIBUTOR), $user)
            ->add('add','submit', array(
                    'label'=>'Add','translation_domain'=>'common',
                    'attr'=>array('first-button')
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('onclick'=>'window.location.assign("'.$this->generateUrl('hello_di_distributor_setting_staff_index').'")','last-button')
                ))
        ;

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $user->setEntity($this->getUser()->getEntity());
                $user->setAccount($this->getUser()->getAccount());
                $em->persist($user);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully', array(), 'message'));
                return $this->redirect($this->generateUrl('hello_di_distributor_setting_staff_index'));
            }
        }
        return $this->render('HelloDiDistributorBundle:setting:staffAdd.html.twig', array(
                'form' => $form->createView()
            ));
    }

    public function staffEditAction(Request $request, $user_id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('HelloDiCoreBundle:User')->findOneBy(array('id'=>$user_id, 'account'=>$this->getUser()->getAccount()));
        if(!$user)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'user'),'message'));

        $languages = $this->container->getParameter('languages');

        $form = $this->createForm(new RegistrationFormType($languages, Account::DISTRIBUTOR), $user)
            ->remove('plainPassword')
            ->add('update','submit', array(
                    'label'=>'Update','translation_domain'=>'common',
                    'attr'=>array('first-button')
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('onclick'=>'window.location.assign("'.$this->generateUrl('hello_di_distributor_setting_staff_index').'")','last-button')
                ))
        ;

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully', array(), 'message'));
                return $this->redirect($this->generateUrl('hello_di_distributor_setting_staff_index'));
            }
        }

        return $this->render('HelloDiDistributorBundle:setting:staffEdit.html.twig', array(
                'form' => $form->createView()
            ));
    }
}
