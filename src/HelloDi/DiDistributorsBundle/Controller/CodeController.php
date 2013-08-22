<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use HelloDi\DiDistributorsBundle\Entity\CdSearch;
use HelloDi\DiDistributorsBundle\Entity\Transaction;
use HelloDi\DiDistributorsBundle\Form\CdSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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

        if($request->isMethod('GET') && $request->getSession()->has('codesearch'))
        {
            $data = $request->getSession()->get('codesearch');
            if($data['provider']!=null) $data['provider'] = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($data['provider']);
            if($data['item']!=null) $data['item'] = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($data['item']);
            if($data['inputFileName']!=null) $data['inputFileName'] = $em->getRepository('HelloDiDiDistributorsBundle:Input')->find($data['inputFileName']);
            $form->setData($data);
        }

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $data = $form->getData();
            $first = 0;

            $qb = $em->createQueryBuilder()
                ->select('code')
                ->from('HelloDiDiDistributorsBundle:Code','code')
                ->join('code.Input','input')
                ->join('input.Account','account')
                ->join('code.Item','item');

            if($data['provider'] != null)
                $qb = $qb->andWhere($qb->expr()->eq('account',intval($data['provider']->getId())));

            if($data['item']!=null)
                $qb = $qb->andWhere($qb->expr()->eq('item',intval($data['item']->getId() )));

            if($data['inputFileName']!=null)
                $qb = $qb->andWhere($qb->expr()->eq('input',intval($data['inputFileName']->getId() )));

            if($data['fromserial']!="")
                $qb = $qb->andWhere('code.serialNumber >= :fromserial')->setParameter('fromserial', $data['fromserial']);

            if($data['toserial']!="")
                $qb = $qb->andWhere('code.serialNumber <= :toserial')->setParameter('toserial', $data['toserial']);

            if($data['status']!='3')
                $qb = $qb->andWhere('code.status = :status')->setParameter('status', $data['status']);

            if($data['frominsertdate']!="")
                $qb = $qb->andWhere("input.dateInsert >= :frominsertdate")->setParameter('frominsertdate', $data['frominsertdate']);

            if($data['toinsertdate']!="")
                $qb = $qb->andWhere("input.dateInsert <= :toinsertdate")->setParameter('toinsertdate', $data['toinsertdate']);

            if($data['fromexpiredate']!="")
                $qb = $qb->andWhere("input.dateExpiry >= :fromexpiredate")->setParameter('fromexpiredate', $data['fromexpiredate']);

            if($data['toexpiredate']!="")
                $qb = $qb->andWhere("input.dateExpiry <= :toexpiredate")->setParameter('toexpiredate', $data['toexpiredate']);

            if($data['provider'] != null) $data['provider'] = $data['provider']->getId();
            if($data['item']!=null) $data['item'] = $data['item']->getId();
            if($data['inputFileName']!=null) $data['inputFileName'] = $data['inputFileName']->getId();
            $request->getSession()->set('codesearch',$data);

            $count = count($qb->getQuery()->getResult());

            $paginator = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $qb,
                $request->get('page',1),
                20,
                array('distinct' => false)
            );
        }

        $csv= $request->get('csv', 0);

        if($csv==1 && !$first)
        {
            $searchresult = $qb->getQuery()->getResult();
            $result = "";
            for($i=0;$i<count($searchresult);$i++)
            {
                $row = $searchresult[$i];
                $result .=
                    $row->getSerialNumber().';'.
                    $row->getInput()->getDateProduction()->format('Y/m/d').';'.
                    $row->getInput()->getDateExpiry()->format('Y/m/d').";".
                    rtrim($row->getPin());
                if($i<count($searchresult)-1) $result .="\n";
            }

            return new Response($result,200,array(
                        'Content-Type'          => 'text/csv',
                        'Content-Disposition'   => 'attachment; filename="Codes.csv"'
             ));
        }
        else
        {
            return $this->render('HelloDiDiDistributorsBundle:Code:index.html.twig', array(
                'pagination' => $pagination,
                'form' => $form->createView(),
                'count' => $count,
                'first' => $first
            ));
        }
    }

    public function historyAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $code = $em->getRepository('HelloDiDiDistributorsBundle:Code')->find($id);
        if (!$code) {
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'Code'),'message'));
        }
        $isremoved = ($em->getRepository('HelloDiDiDistributorsBundle:Transaction')->findOneBy(array('Code'=>$code,'tranAction'=>'rmv'))==null ? false : true);

        return $this->render('HelloDiDiDistributorsBundle:Code:history.html.twig', array(
            'code'=> $code,
            'isremoved' => $isremoved
        ));
    }

    public function DeadBeatAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $code = $em->getRepository('HelloDiDiDistributorsBundle:Code')->find($id);
        if (!$code) {
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'Code'),'message'));
        }
        if($code->getStatus()==1)
        {
            try
            {
                $user= $this->get('security.context')->getToken()->getUser();
                $tranadd = $em->getRepository('HelloDiDiDistributorsBundle:Transaction')->findOneBy(array('Code'=>$code,'tranAction'=>'add'),array('id'=>'desc'));

                $code->setStatus(0);

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
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
            }
            catch(\Exception $e)
            {
                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('the_operation_failed',array(),'message'));
            }
        }
        else
        {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('code_must_be_Active',array(),'message'));
        }
        return $this->redirect($this->getRequest()->headers->get('referer'));
    }

    public function CreditNoteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $code = $em->getRepository('HelloDiDiDistributorsBundle:Code')->find($id);
        if (!$code) {
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'Code'),'message'));
        }
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
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
            }
            catch(\Exception $e)
            {
                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('the_operation_failed',array(),'message'));
            }
        }
        else
        {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('code_must_be_Inactive',array(),'message'));
        }
        return $this->redirect($this->getRequest()->headers->get('referer'));
    }

    public function CreditNoteAndDeadBeatAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $code = $em->getRepository('HelloDiDiDistributorsBundle:Code')->find($id);
        if (!$code) {
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'Code'),'message'));
        }
        if($code->getStatus()==0)
        {
            try
            {
                $user= $this->get('security.context')->getToken()->getUser();

                $transale = $em->getRepository('HelloDiDiDistributorsBundle:Transaction')->findOneBy(array('Code'=>$code,'tranAction'=>'sale'),array('id'=>'desc'));
                $trancom = $em->getRepository('HelloDiDiDistributorsBundle:Transaction')->findOneBy(array('Code'=>$code,'tranAction'=>'com'),array('id'=>'desc'));

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

                $tranadd = $em->getRepository('HelloDiDiDistributorsBundle:Transaction')->findOneBy(array('Code'=>$code,'tranAction'=>'add'),array('id'=>'desc'));

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
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
            }
            catch(\Exception $e)
            {
                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('the_operation_failed',array(),'message'));
            }
        }
        else
        {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('code_must_be_Inactive',array(),'message'));
        }
        return $this->redirect($this->getRequest()->headers->get('referer'));
    }

//    public function aaaaAction()
//    {
//        $em = $this->getDoctrine()->getEntityManager();
//
//        $q = $em->createQueryBuilder()
//            -> select('Distinct code.id')
//            -> from('HelloDiDiDistributorsBundle:Code','code')
//            -> innerJoin('code.Transactions','tr')
//            -> where('tr.tranAction = :sale OR tr.tranAction = :rmv')->setParameter('sale','sale')->setParameter('rmv','rmv');
//            ;
//        $rq = $q->getQuery()->getArrayResult();
////        die('aa');
//        $qb=$em->createQueryBuilder();
//        $qb -> select('code')
//            -> from('HelloDiDiDistributorsBundle:Code','code')
//            -> where('code.status = 0')
//            -> andWhere('code.id NOT IN (:aa)')->setParameter('aa',$rq);
//            ;
//        $rqb = $qb->getQuery()->getArrayResult();
//        $qu = $em->createQueryBuilder()
//            ->update('HelloDiDiDistributorsBundle:Code','code')
//            ->set('code.status',1)
//            ->where('code.id IN (:bb)')->setParameter('bb',$rqb)
//            ->getQuery()
//            ->execute()
//           ;
//
//        die('--'.count($qb->getQuery()->getResult()).'--');
//    }
}
