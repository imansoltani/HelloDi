<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use HelloDi\DiDistributorsBundle\Form\Entiti\EditEntitiMasterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ProfileController extends Controller
{
    public function  EntitiAction(Request $req)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $this->getUser()->getEntiti();

        $form = $this->createForm(new EditEntitiMasterType(), $entity);

        if ($req->isMethod('post')) {
            $form->handleRequest($req);
            if ($form->isValid()) {
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully', array(), 'message'));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Profile:Entiti.html.twig', array(
            'edit_form' => $form->createView(),
            'entity' => $entity,
        ));
    }
}
