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
        $form = $this->createForm(new CdSearchType());

        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder()
            ->select('code')
            ->from('HelloDiDiDistributorsBundle:Code','code');

        if ($request->isMethod('POST')) {
            $form->bind($request);
            $data = $form->getData();

            $qb ->join('code.Input','input')
                ->join('input.Account','account')
                ->join('code.Item','item');

            if($data['provider'] != null)
                $qb = $qb->andWhere($qb->expr()->eq('account',intval($data['provider']->getId())));

            if($data['item']!=null)
                $qb = $qb->andWhere($qb->expr()->eq('item',intval($data['item']->getId() )));

            if($data['inputFileName']!=null)
                $qb = $qb->andWhere($qb->expr()->eq('input',intval($data['inputFileName']->getId() )));

            if($data['serial']!="")
                $qb = $qb->andWhere($qb->expr()->like('code.serialNumber', $qb->expr()->literal($data['serial'].'%')));

            if($data['pin']!="")
                $qb = $qb->andWhere($qb->expr()->like('code.pin', $qb->expr()->literal($data['pin'].'%')));

            if($data['status']!=2)
                $qb = $qb->andWhere('code.status = :status')->setParameter('status', $data['status']);

            if($data['insertdate']!="")
                $qb = $qb->andWhere("input.dateInsert = :insertdate")->setParameter('insertdate', $data['insertdate']);

            if($data['expiredate']!="")
                $qb = $qb->andWhere("input.dateExpiry = :expiredate")->setParameter('expiredate', $data['expiredate']);
        }

        $count = count($qb->getQuery()->getResult());

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $qb,
            $this->get('request')->query->get('page', 1),
            10,
            array('distinct' => false)
        );
        return $this->render('HelloDiDiDistributorsBundle:Code:index.html.twig', array(
            'pagination' => $pagination,
            'form' => $form->createView(),
            'count' => $count
        ));
    }

    public function historyAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $code = $em->getRepository('HelloDiDiDistributorsBundle:Code')->find($id);

        return $this->render('HelloDiDiDistributorsBundle:Code:history.html.twig', array(
            'code'=> $code
        ));
    }
}
