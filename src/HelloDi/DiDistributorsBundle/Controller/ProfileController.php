<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use HelloDi\DiDistributorsBundle\Entity\DetailHistory;
use HelloDi\DiDistributorsBundle\Form\Entiti\EditAddressEntitiType;
use HelloDi\DiDistributorsBundle\Form\Entiti\EditEntitiMasterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;


class ProfileController extends Controller
{
    public function  EntitiAction(Request $req)
    {
        $em=$this->getDoctrine()->getManager();
        $Entiti=$this->getUser()->getEntiti();

        $form=$this->createForm(new EditEntitiMasterType(),$Entiti);

        if($req->isMethod('post'))
        {
            $form->handleRequest($req);
            if($form->isValid())
            {

                $em->flush();
                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
            }
        }

        return $this->render('HelloDiDiDistributorsBundle:Profile:Entiti.html.twig', array(
            'edit_form'=>$form->createView(),
            'entity' => $Entiti,
        ));

    }


    public function AddressAction(Request $request)
    {

        $em=$this->getDoctrine()->getManager();
        $DetaHis=new DetailHistory();
        $entity=$this->getUser()->getEntiti();
        $form=$this->createForm(new EditAddressEntitiType(),$entity);

        if($request->isMethod('post'))
        {
            $form->handleRequest($request);
            if($form->isValid())
            {

                $DetaHis->setAdrs1($entity->getEntAdrs1());
                $DetaHis->setAdrs2($entity->getEntAdrs2());
                $DetaHis->setAdrs3($entity->getEntAdrs3());
                $DetaHis->setAdrsNp($entity->getEntNp());
                $DetaHis->setAdrsCity($entity->getEntCity());
                $DetaHis->setCountry($entity->getCountry());
                $DetaHis->setEntiti($entity);
                $DetaHis->setAdrsDate(new \DateTime('now'));
                $em->persist($DetaHis);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));

            }
        }
        return $this->render('HelloDiDiDistributorsBundle:Profile:Address.html.twig', array(
            'entity' => $entity,
            'form_edit'=>$form->createView()
        ));


    }

}
