<?php

namespace HelloDi\MasterBundle\Controller;

use HelloDi\CoreBundle\Entity\Operator;
use HelloDi\MasterBundle\Form\OperatorType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class OperatorController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $operators = $em->getRepository('HelloDiCoreBundle:Operator')->findAll();

        return $this->render('HelloDiMasterBundle:operator:index.html.twig', array(
            'operators' => $operators,
        ));
    }

    public function addAction(Request $request)
    {
        $operator = new Operator();
        $form = $this->createForm(new OperatorType(), $operator)
            ->add('add','submit', array(
                    'label'=>'Add','translation_domain'=>'common',
                    'attr'=>array('first-button')
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('onclick'=>'window.location.assign("'.$this->generateUrl('hello_di_master_operator_index').'")','last-button')
                ))
        ;

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($operator);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl('hello_di_master_operator_index'));
            }
        }

        return $this->render('HelloDiMasterBundle:operator:new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $operator = $em->getRepository('HelloDiCoreBundle:Operator')->find($id);

        if (!$operator) {
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>$this->get('translator')->trans('Operator',array(),'operator')),'message'));
        }

        $editForm = $this->createForm(new OperatorType(), $operator)
            ->add('update','submit', array(
                    'label'=>'Update','translation_domain'=>'common',
                    'attr'=>array('first-button')
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('onclick'=>'window.location.assign("'.$this->generateUrl('hello_di_master_operator_index').'")','last-button')
                ))
            ;

        if ($request->isMethod('POST')) {
            $editForm->handleRequest($request);

            if ($editForm->isValid()) {
                $em->persist($operator);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl('hello_di_master_operator_index'));
            }
        }

        return $this->render('HelloDiMasterBundle:operator:edit.html.twig', array(
            'edit_form'   => $editForm->createView(),
            'operator' => $operator
        ));
    }
}
