<?php

namespace HelloDi\DiDistributorsBundle\Controller;
use HelloDi\DiDistributorsBundle\Entity\Tax;
use HelloDi\DiDistributorsBundle\Form\Tax\TaxType;
use HelloDi\DiDistributorsBundle\Form\searchProvRemovedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HelloDi\DiDistributorsBundle\Entity\Account;
use Symfony\Component\HttpFoundation\Response;


class TaxController extends Controller
{

    public function taxAction(Request $req)
    {

        $em=$this->getDoctrine()->getEntityManager();

        $newtax=new Tax();

        $form=$this->createForm(new TaxType(),$newtax);

        if($req->isMethod('POST'))
        {
            $form->handleRequest($req);
            if($form->isValid())
            {
                if($em->getRepository('HelloDiDiDistributorsBundle:Tax')->findOneBy(array('Country'=>$newtax->getCountry(),'taxend'=>null)))
                {
                    $tax=$em->getRepository('HelloDiDiDistributorsBundle:Tax')->findOneBy(array('Country'=>$newtax->getCountry(),'taxend'=>null));
                    $tax->setTaxend(new \DateTime('now'));
                }
                    $em->persist($newtax);
                    $em->flush();
                $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
            }


        }


      $historytax=$em->getRepository('HelloDiDiDistributorsBundle:Tax')->findBy(array(),array('id'=>'desc'));


        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $historytax,
            $req->get('page', 1) /*page number*/,
            10/*limit per page*/
        );

     return   $this->render('HelloDiDiDistributorsBundle:Tax:Edit.html.twig',
            array(
                'form'=>$form->createView(),
                'pagination'=>$pagination
            ));
    }

}
