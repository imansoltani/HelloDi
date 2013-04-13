<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use HelloDi\DiDistributorsBundle\Entity\Operator;
use HelloDi\DiDistributorsBundle\Form\OperatorType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class OperatorController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $operators = $em->getRepository('HelloDiDiDistributorsBundle:Operator')->findAll();

        return $this->render('HelloDiDiDistributorsBundle:Operator:index.html.twig', array(
            'operators' => $operators,
        ));
    }

    public function newAction(Request $request)
    {
        $operator  = new Operator();
        $form   = $this->createForm(new OperatorType(), $operator);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $operator->upload();
                $em = $this->getDoctrine()->getManager();
                $em->persist($operator);
                $em->flush();
                return $this->redirect($this->generateUrl('operator'));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Operator:new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $operator = $em->getRepository('HelloDiDiDistributorsBundle:Operator')->find($id);

        if (!$operator) {
            throw $this->createNotFoundException('Unable to find Operator entity.');
        }

        $editForm = $this->createForm(new OperatorType(), $operator);

        if ($request->isMethod('POST')) {
            $editForm->bind($request);

            if ($editForm->isValid()) {
                $operator->upload();
                $em->persist($operator);
                $em->flush();

                return $this->redirect($this->generateUrl('operator'));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Operator:edit.html.twig', array(
            'edit_form'   => $editForm->createView(),
            'operator' => $operator
        ));
    }
}
