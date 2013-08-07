<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Entity\Tax;
use HelloDi\DiDistributorsBundle\Form\Tax\TaxType;
use HelloDi\DiDistributorsBundle\Entity\DetailHistory;
use HelloDi\DiDistributorsBundle\Entity\Entiti;
use HelloDi\DiDistributorsBundle\Entity\Input;
use HelloDi\DiDistributorsBundle\Entity\Price;
use HelloDi\DiDistributorsBundle\Entity\PriceHistory;
use HelloDi\DiDistributorsBundle\Entity\Transaction;
use HelloDi\DiDistributorsBundle\Entity\User;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistChildSearchType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistChildType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistMasterType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistSearchType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountProvType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountSearchDistType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountSearchProvType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountType;
use HelloDi\DiDistributorsBundle\Form\Account\EditDistType;
use HelloDi\DiDistributorsBundle\Form\Account\EditProvType;
use HelloDi\DiDistributorsBundle\Form\Account\EditRetailerType;
use HelloDi\DiDistributorsBundle\Form\Account\EntitiAccountprovType;
use HelloDi\DiDistributorsBundle\Form\Account\MakeAccountIn2StepType;
use HelloDi\DiDistributorsBundle\Form\Distributors\NewRetailersType;
use HelloDi\DiDistributorsBundle\Form\Entiti\EditEntitiRetailerType;
use HelloDi\DiDistributorsBundle\Form\PriceEditType;
use HelloDi\DiDistributorsBundle\Form\User\NewUserType;
use HelloDi\DiDistributorsBundle\Form\User\UserDistSearchType;
use HelloDi\DiDistributorsBundle\Form\searchProvRemovedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HelloDi\DiDistributorsBundle\Entity\Account;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use HelloDi\DiDistributorsBundle\Form\searchProvTransType;
use Symfony\Component\Validator\Constraints\DateTime;


class TaxController extends Controller
{

    public function taxAction(Request $req)
    {


        $em=$this->getDoctrine()->getEntityManager();
        $tax=$em->getRepository('HelloDiDiDistributorsBundle:Tax')->findOneBy(array(),array('taxstart'=>'desc'));

        $newtax=new Tax();
        if($tax!=null)
        $newtax->setTax($tax->getTax());
        $form=$this->createForm(new TaxType(),$newtax);

        if($req->isMethod('POST'))
        {
            $form->handleRequest($req);
            if($form->isValid())
            {
                $newtax->setTaxstart(new \DateTime('now'));
                $em->persist($newtax);
                $em->flush();
            }
            $this->get('session')->getFlashBag()->add('success','this operation done success !');
        }
        $historytax=$em->createQueryBuilder();
           $historytax->select('TX')
               ->from('HelloDiDiDistributorsBundle:Tax','TX')
             ->orderBy('TX.taxstart','desc')
           ;


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
