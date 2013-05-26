<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use HelloDi\DiDistributorsBundle\Entity\CdSearch;
use HelloDi\DiDistributorsBundle\Form\CdSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HelloDi\DiDistributorsBundle\Form\CodeType;
use Symfony\Component\HttpFoundation\Response;

class CodeController extends Controller
{
    public function indexAction(Request $request)
    {
        $search = new CdSearch();
        $form   = $this->createForm(new CdSearchType(), $search);

        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb = $qb->select('code')
            ->from('HelloDiDiDistributorsBundle:Code','code')
            ->getQuery();

        if ($request->isMethod('POST')) {
            $form->bind($request);
            $qb = $em->createQueryBuilder();
            $qb = $qb->select('code')
                ->from('HelloDiDiDistributorsBundle:Code','code')
                ->innerJoin('code.Item','codeitem')
                ->innerJoin('code.Input','codeinput');

            if($search->getPro() != "")
                $qb = $qb->andWhere($qb->expr()->eq('codeitem.operator',intval($search->getPro()->getId())));

            if($search->getItem()!="")
                $qb =  $qb->andWhere($qb->expr()->eq('code.Item',intval($search->getItem()->getId() )));

            if($search->getInputFileName()!="")
                $qb =  $qb->andWhere($qb->expr()->like('codeinput.fileName', $qb->expr()->literal($search->getInputFileName().'%')));

            if($search->getSerial()!="")
                $qb =  $qb->andWhere($qb->expr()->like('code.serialNumber', $qb->expr()->literal($search->getSerial().'%')));

            if($search->getPin()!="")
                $qb =  $qb->andWhere($qb->expr()->like('code.pin', $qb->expr()->literal($search->getPin().'%')));

            if($search->getStatus()=='0')
                $qb =  $qb->andWhere($qb->expr()->eq('code.status',0));

            if($search->getStatus()=='1')
                $qb =  $qb->andWhere($qb->expr()->eq('code.status',1));

            if($search->getInsertdate()!="")
                $qb =$qb->andWhere($qb->expr()->like('codeinput.dateInsert', $qb->expr()->literal(date_format($search->getInsertdate() ,'Y-m-d'))));

            if($search->getExpiredate()!="")
                $qb =$qb->andWhere($qb->expr()->like('codeinput.dateExpiry', $qb->expr()->literal(date_format($search->getExpiredate() ,'Y-m-d'))));

            $qb = $qb->getQuery();
        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $qb,
            $this->get('request')->query->get('page', 1),
            10
        );
        return $this->render('HelloDiDiDistributorsBundle:Code:index.html.twig', array(
            'pagination' => $pagination,
            'form' => $form->createView()
        ));
    }

    public function historyAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $code = $em->getRepository('HelloDiDiDistributorsBundle:Code')->find($id);

        return $this->render('HelloDiDiDistributorsBundle:Code:history.html.twig', array(
            'code'=>$code
        ));
    }
}
