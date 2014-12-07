<?php

namespace HelloDi\MasterBundle\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\CoreBundle\Entity\Tax;
use HelloDi\CoreBundle\Entity\User;
use HelloDi\MasterBundle\Form\EntityType;
use HelloDi\MasterBundle\Form\TaxSearchType;
use HelloDi\MasterBundle\Form\TaxType;
use HelloDi\UserBundle\Form\RegistrationFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SettingController extends Controller
{
    public function profileAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getUser()->getEntity();

        $countries = $this->container->getParameter('countries');

        $form = $this->createForm(new EntityType($countries), $entity)
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
        $users = $this->getUser()->getEntity()->getUsers();

        return $this->render('HelloDiMasterBundle:setting:staff.html.twig', array(
                'users' => $users
            ));
    }

    public function staffAddAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $user = new User();

        $languages = $this->container->getParameter('languages');

        $form = $this->createForm(new RegistrationFormType($languages, Account::PROVIDER), $user)
            ->add('add','submit', array(
                    'label'=>'Add','translation_domain'=>'common',
                    'attr'=>array('first-button')
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('onclick'=>'window.location.assign("'.$this->generateUrl('hello_di_master_setting_staff_index').'")','last-button')
                ))
        ;

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $user->setEntity($this->getUser()->getEntity());
                $em->persist($user);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully', array(), 'message'));
                return $this->redirect($this->generateUrl('hello_di_master_setting_staff_index'));
            }
        }
        return $this->render('HelloDiMasterBundle:setting:staffAdd.html.twig', array(
                'form' => $form->createView()
            ));
    }

    public function staffEditAction(Request $request, $user_id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('HelloDiCoreBundle:User')->findOneBy(array('id'=>$user_id,'entity'=>$this->getUser()->getEntity()));
        if(!$user)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'user'),'message'));

        $languages = $this->container->getParameter('languages');

        $form = $this->createForm(new RegistrationFormType($languages, Account::PROVIDER), $user)
            ->remove('plainPassword')
            ->add('update','submit', array(
                    'label'=>'Update','translation_domain'=>'common',
                    'attr'=>array('first-button')
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('onclick'=>'window.location.assign("'.$this->generateUrl('hello_di_master_setting_staff_index').'")','last-button')
                ))
        ;

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully', array(), 'message'));
                return $this->redirect($this->generateUrl('hello_di_master_setting_staff_index'));
            }
        }

        return $this->render('HelloDiMasterBundle:setting:staffEdit.html.twig', array(
                'form' => $form->createView()
            ));
    }

    public function taxAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $countries = $this->container->getParameter('countries');

        $search_country = $request->query->get('country');

        //form search
        $form_search = $this->createForm(new TaxSearchType($countries),
            $search_country ? array("country" => $search_country, 'type'=>2) : array('type'=>1),
            array(
                'attr' => array('class' => 'SearchForm'),
                'method' => 'get',
            ))
            ->add('search','submit', array(
                    'label'=>'Search','translation_domain'=>'common',
                ));

        //form add
        $tax = new Tax();

        $form = $this->createForm(new TaxType($countries), $tax)
            ->add('add','submit', array(
                    'label'=>'Add','translation_domain'=>'common',
                    'attr'=>array('first-button', 'last-button')
                ));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $last_tax = $em->getRepository('HelloDiCoreBundle:Tax')->findOneBy(array('country'=>$tax->getCountry(), 'dateEnd'=>null));
                if($last_tax)
                    $last_tax->setDateEnd(new \DateTime());

                $em->persist($tax);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully', array(), 'message'));
            }
        }

        $qb = $em->createQueryBuilder()
            ->select('tax')
            ->from('HelloDiCoreBundle:Tax', 'tax');
        if($search_country == null)
            $qb->where('tax.dateEnd is null');
        else
            $qb->where('tax.country = :country')->setParameter('country', $search_country);
        $taxes = $qb->getQuery()->getResult();

        return $this->render('HelloDiMasterBundle:setting:tax.html.twig', array(
                'form' => $form->createView(),
                'form_search' => $form_search->createView(),
                'taxes' => $taxes
            ));
    }
}