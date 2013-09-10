<?php

namespace HelloDi\DiDistributorsBundle\Controller;
use HelloDi\DiDistributorsBundle\Entity\Tax;
use HelloDi\DiDistributorsBundle\Entity\TaxHistory;
use HelloDi\DiDistributorsBundle\Form\Tax\TaxType;
use HelloDi\DiDistributorsBundle\Form\searchProvRemovedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HelloDi\DiDistributorsBundle\Entity\Account;
use Symfony\Component\HttpFoundation\Response;


class TaxController extends Controller
{

    public function taxAction(Request $req,$Active)
    {


        $em=$this->getDoctrine()->getManager();

        $newtax=new Tax();

        $newtaxhistory=new TaxHistory();

        $form=$this->createForm(new TaxType(),$newtax);

        if($req->isMethod('POST'))
        {
            $form->handleRequest($req);

            if($form->isValid())
            {
                $tax=$em->getRepository('HelloDiDiDistributorsBundle:Tax')->findOneBy(array('Country'=>$newtax->getCountry()));


                if($tax!=null)
                      {

                    $taxhistory=$em->getRepository('HelloDiDiDistributorsBundle:TaxHistory')->findOneBy(array('Tax'=>$tax,'taxend'=>null));

                    $tax->setTax($newtax->getTax());

                    $taxhistory->setTaxend(new \DateTime('now'));

                    $newtaxhistory->setVat($tax->getTax());

                    $newtaxhistory->setTax($tax);

                    $em->persist($newtaxhistory);

                    $em->flush();
                     }

                else
                     {
                $em->persist($newtax);

                $newtaxhistory->setVat($newtax->getTax());

                $newtaxhistory->setTax($newtax);

                $em->persist($newtaxhistory);

                $em->flush();
                     }

                    $this->get('session')->getFlashBag()->add('success',$this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
            }


        }
        $taxhistory2=$em->createQueryBuilder();
        $taxhistory2->select('Tx')
            ->from('HelloDiDiDistributorsBundle:TaxHistory','Tx')
            ->innerJoin('Tx.Tax','TxT')
            ->innerJoin('TxT.Country','TxTCo')
        ;
      if($Active==0)
      {
          $taxhistory2->orderBy('Tx.id','desc')->addOrderBy('TxTCo.name');

      }

      elseif($Active==1)
           {
        $taxhistory2->where(
                $taxhistory2->expr()->isNull('Tx.taxend')
            )
            ->orderBy('Tx.id','desc')->addOrderBy('TxTCo.name');

            }

      elseif($Active==2)
          {
          $taxhistory2->where(
                      $taxhistory2->expr()->isNotNull('Tx.taxend')
                    )
                  ->orderBy('Tx.id','desc')->addOrderBy('TxTCo.name');

          }

        $historytax=$taxhistory2->getQuery();


     return   $this->render('HelloDiDiDistributorsBundle:Tax:Edit.html.twig',
            array(
                'form'=>$form->createView(),
                'pagination'=>$historytax->getResult()
            ));
    }

}
