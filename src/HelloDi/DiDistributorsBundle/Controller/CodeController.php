<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use HelloDi\DiDistributorsBundle\Entity\CdSearch;
use HelloDi\DiDistributorsBundle\Entity\Transaction;
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

        $first = 1;
        $pagination = null;
        $count = 0;
        if ($request->isMethod('POST')) {
            $first = 0;

            $qb = $em->createQueryBuilder()
                ->select('code')
                ->from('HelloDiDiDistributorsBundle:Code','code');

            $form->handleRequest($request);
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

            if($data['status']!='3')
                $qb = $qb->andWhere('code.status = :status')->setParameter('status', $data['status']);

            if($data['insertdate']!="")
                $qb = $qb->andWhere("input.dateInsert = :insertdate")->setParameter('insertdate', $data['insertdate']);

            if($data['expiredate']!="")
                $qb = $qb->andWhere("input.dateExpiry = :expiredate")->setParameter('expiredate', $data['expiredate']);

            $count = count($qb->getQuery()->getResult());

            $paginator = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $qb,
                $request->get('page'),
                20,
                array('distinct' => false)
            );
        }

        return $this->render('HelloDiDiDistributorsBundle:Code:index.html.twig', array(
            'pagination' => $pagination,
            'form' => $form->createView(),
            'count' => $count,
            'first' => $first
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

    public function DeadBeatAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $code = $em->getRepository('HelloDiDiDistributorsBundle:Code')->find($id);
        if($code->getStatus()==1)
        {
            try
            {
                $user= $this->get('security.context')->getToken()->getUser();
                $tranadd = $em->getRepository('HelloDiDiDistributorsBundle:Transaction')->findOneBy(array('Code'=>$code,'tranAction'=>'add'),array('id'=>'desc'));

                $code->setStatus(2);

                $tranrmv = new Transaction();
                $tranrmv->setAccount($tranadd->getAccount());
                $tranrmv->setTranAmount(-($tranadd->getTranAmount()));
                $tranrmv->setTranFees(0);
                $tranrmv->setTranDescription('Code id is: ' . $code->getId());
                $tranrmv->setTranCurrency($tranadd->getAccount()->getAccCurrency());
                $tranrmv->setTranDate(new \DateTime('now'));
                $tranrmv->setTranInsert(new \DateTime('now'));
                $tranrmv->setCode($code);
                $tranrmv->setTranAction('rmv');
                $tranrmv->setTranType(0);
                $tranrmv->setUser($user);
                $tranrmv->setTranBookingValue(null);
                $tranrmv->setTranBalance($tranadd->getAccount()->getAccBalance());
                $em->persist($tranrmv);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Code is Dead Beat successfull!');
            }
            catch(\Exception $e)
            {
                $this->get('session')->getFlashBag()->add('error', 'Error in Dead Beat!');
            }
        }
        else
        {
            $this->get('session')->getFlashBag()->add('error', 'The code must be Active to Dead Beat!');
        }
        return $this->redirect($this->getRequest()->headers->get('referer'));
    }

    public function CreditNoteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $code = $em->getRepository('HelloDiDiDistributorsBundle:Code')->find($id);
        if($code->getStatus()==0)
        {
            try
            {
                $user= $this->get('security.context')->getToken()->getUser();
                $transale = $em->getRepository('HelloDiDiDistributorsBundle:Transaction')->findOneBy(array('Code'=>$code,'tranAction'=>'sale'),array('id'=>'desc'));
                $trancom = $em->getRepository('HelloDiDiDistributorsBundle:Transaction')->findOneBy(array('Code'=>$code,'tranAction'=>'com'),array('id'=>'desc'));

                $code->setStatus(1);

                $trancrntsale = new Transaction();
                $trancrntsale->setAccount($transale->getAccount());
                $trancrntsale->setTranAmount(-($transale->getTranAmount()));
                $trancrntsale->setTranFees(0);
                $trancrntsale->setTranDescription('Code id is: ' . $code->getId());
                $trancrntsale->setTranCurrency($transale->getAccount()->getAccCurrency());
                $trancrntsale->setTranDate(new \DateTime('now'));
                $trancrntsale->setTranInsert(new \DateTime('now'));
                $trancrntsale->setCode($code);
                $trancrntsale->setTranAction('crnt');
                $trancrntsale->setTranType(1);
                $trancrntsale->setUser($user);
                $trancrntsale->setTranBookingValue(null);
                $trancrntsale->setTranBalance($transale->getAccount()->getAccBalance());
                $em->persist($trancrntsale);

                $trancrntcom = new Transaction();
                $trancrntcom->setAccount($trancom->getAccount());
                $trancrntcom->setTranAmount(-($trancom->getTranAmount()));
                $trancrntcom->setTranFees(0);
                $trancrntcom->setTranDescription('Code id is: ' . $code->getId());
                $trancrntcom->setTranCurrency($trancom->getAccount()->getAccCurrency());
                $trancrntcom->setTranDate(new \DateTime('now'));
                $trancrntcom->setTranInsert(new \DateTime('now'));
                $trancrntcom->setCode($code);
                $trancrntcom->setTranAction('crnt');
                $trancrntcom->setTranType(0);
                $trancrntcom->setUser($user);
                $trancrntcom->setTranBookingValue(null);
                $trancrntcom->setTranBalance($trancom->getAccount()->getAccBalance());
                $em->persist($trancrntcom);

                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Code is Credit Note successfull!');
            }
            catch(\Exception $e)
            {
                $this->get('session')->getFlashBag()->add('error', 'Error in Credit Note!');
            }
        }
        else
        {
            $this->get('session')->getFlashBag()->add('error', 'The code must be Active to Credit Note!');
        }
        return $this->redirect($this->getRequest()->headers->get('referer'));
    }
}
