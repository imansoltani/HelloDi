<?php

namespace HelloDi\MasterBundle\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\AccountingBundle\Entity\Transaction;
use HelloDi\AggregatorBundle\Form\PinType;
use HelloDi\AggregatorBundle\Entity\Code;
use HelloDi\AggregatorBundle\Entity\Pin;
use HelloDi\MasterBundle\Form\CodeSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class CodeController extends Controller
{
    public function indexAction(Request $request)
    {
        if($request->query->has('last_search'))
        {
            $parameters = $request->getSession()->get('code_search');
            if($request->query->has('csv'))
                $parameters ['csv'] = '';
            return $this->redirect($this->generateUrl('hello_di_master_code_search',$parameters));
        }

        if($request->query->count()>0)
            return $this->redirect($this->generateUrl('hello_di_master_code_search',$request->query->all()));

        $form = $this->createForm(new CodeSearchType(20), null, array(
                'attr' => array('class' => 'SearchForm'),
                'action' => $this->generateUrl('hello_di_master_code_search'),
                'method' => 'get',
            ))
            ->add('search','submit', array(
                    'label'=>'Search','translation_domain'=>'common',
                ));

        return $this->render('HelloDiMasterBundle:code:index.html.twig', array(
                'form' => $form->createView(),
                'codes' => null
            ));
    }

    public function searchAction(Request $request)
    {
        $form_select = $this->createForm(new PinType(), new Pin());

        $count_per_page = is_numeric($request->get('count_per_page'))?$request->get('count_per_page'):20;

        $form = $this->createForm(new CodeSearchType($count_per_page), null, array(
                'attr' => array('class' => 'SearchForm'),
                'method' => 'get',
            ))
            ->add('search','submit', array(
                    'label'=>'Search','translation_domain'=>'common',
                ));

        $page = $request->get('page', 1);
        $fields = $request->query->all();
        unset($fields['last_search'], $fields['csv'], $fields['page']);
        $form->submit($fields);

        $codes = array();

        if($form->isValid())
        {
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();

            $form_data = $form->getData();

            $qb = $em->createQueryBuilder()
                ->select('code')
                ->from('HelloDiAggregatorBundle:Code','code')
                ->join('code.input','input')
            ;

            if(isset($form_data['provider']))
                $qb->andWhere('input.provider = :provider')->setParameter('provider', $form_data['provider']);

            if(isset($form_data['item']))
                $qb->andWhere('code.item = :item')->setParameter('item', $form_data['item']);

            if(isset($form_data['input']))
                $qb->andWhere('input = :input')->setParameter('input', $form_data['input']);

            if(isset($form_data['fromSerialNumber']))
                $qb->andWhere('code.serialNumber >= :from_serial')->setParameter('from_serial', $form_data['fromSerialNumber']);

            if(isset($form_data['toSerialNumber']))
                $qb->andWhere('code.serialNumber <= :to_serial')->setParameter('to_serial', $form_data['toSerialNumber']);

            if(isset($form_data['status']))
                $qb->andWhere('code.status = :status')->setParameter('status', $form_data['status']);

            if(isset($form_data['fromInsertDate']))
                $qb->andWhere("input.dateInsert >= :from_insert")->setParameter('from_insert', $form_data['fromInsertDate']);

            if(isset($form_data['toInsertDate']))
                $qb->andWhere("input.dateInsert <= :to_insert")->setParameter('to_insert', $form_data['toInsertDate']);

            if(isset($form_data['fromExpireDate']))
                $qb->andWhere("input.dateExpiry >= :from_expire")->setParameter('from_expire', $form_data['fromExpireDate']);

            if(isset($form_data['toExpireDate']))
                $qb->andWhere("input.dateExpiry <= :to_expire")->setParameter('to_expire', $form_data['toExpireDate']);

            $codes = $request->query->has('csv')
                ?
                $qb->getQuery()->getResult()
                :
                $this->get('knp_paginator')->paginate($qb, $page, $count_per_page);

            $request->getSession()->set('code_search', $request->query->all());
        }

        if($request->query->has('csv')) {
            $result = "";
            /** @var Code[] $codes */
            foreach ($codes as $code) {
                $result .=
                    $code->getSerialNumber().';'.
                    $code->getInput()->getDateProduction()->format('Y/m/d').';'.
                    $code->getInput()->getDateExpiry()->format('Y/m/d').";".
                    $code->getPin();
                if(end($codes) !== $code) $result .= PHP_EOL;
            }
            return new Response($result,200,array(
                'Content-Type'          => 'text/csv',
                'Content-Disposition'   => 'attachment; filename="Codes.csv"'
            ));
        }
        else
            return $this->render('HelloDiMasterBundle:code:index.html.twig', array(
                    'form' => $form->createView(),
                    'codes' => $codes,
                    'form_select' => $form_select
                ));
    }

    public function historyAction($id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $code = $em->getRepository('HelloDiAggregatorBundle:Code')->find($id);
        if (!$code)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'Code'),'message'));

        $removed = false;//($em->getRepository('HelloDiAccountingBundle:Transaction')->findOneBy(array('Code'=>$code,'tranAction'=>'rmv'))==null ? false : true);

        return $this->render('HelloDiMasterBundle:code:history.html.twig', array(
            'code'=> $code,
            'removed' => $removed
        ));
    }

    public function DeadBeatAction(Request $request)
    {
        $pin = new Pin();
        $pin->setUser($this->getUser());
        $form_select = $this->createForm(new PinType(), $pin);

        $form_select->handleRequest($request);
        if ($form_select->isValid()) {
            try {
            $pin = $this->get('aggregator')->deadBeatCodes($pin);

            $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully. '.$pin->getCount(). " codes deadbeat by ".-$pin->getTransaction()->getAmount().".", array(), 'message'));
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans($e->getMessage(), array(), 'message'));
            }
        }
        else
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('the_operation_failed', array(), 'message'));

        return $this->redirect($this->getRequest()->headers->get('referer'));
    }

    public function CreditNoteAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        die('--CreditNote--');
//        $code = $em->getRepository('HelloDiDiDistributorsBundle:Code')->find($id);
//        if (!$code) {
//            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'Code'),'message'));
//        }
//        if($code->getStatus()==0)
//        {
//            try
//            {
//                $user= $this->getUser();
//                $transale = $em->getRepository('HelloDiAccountingBundle:Transaction')->findOneBy(array('Code'=>$code,'tranAction'=>'sale'),array('id'=>'desc'));
//                $trancom = $em->getRepository('HelloDiAccountingBundle:Transaction')->findOneBy(array('Code'=>$code,'tranAction'=>'com'),array('id'=>'desc'));
//
//                $code->setStatus(1);
//
//                $trancrntsale = new Transaction();
//                $trancrntsale->setAccount($transale->getAccount());
//                $trancrntsale->setTranAmount(-($transale->getTranAmount()));
//                $trancrntsale->setTranFees(0);
//                $trancrntsale->setTranDescription('Code id is: ' . $code->getId());
//                $trancrntsale->setTranCurrency($transale->getAccount()->getAccCurrency());
//                $trancrntsale->setTranDate(new \DateTime('now'));
//                $trancrntsale->setTranInsert(new \DateTime('now'));
//                $trancrntsale->setCode($code);
//                $trancrntsale->setTranAction('crnt');
//                $trancrntsale->setTranType(1);
//                $trancrntsale->setUser($user);
//                $trancrntsale->setTranBookingValue(null);
//                $trancrntsale->setTranBalance($transale->getAccount()->getAccBalance());
//                $em->persist($trancrntsale);
//
//                $trancrntcom = new Transaction();
//                $trancrntcom->setAccount($trancom->getAccount());
//                $trancrntcom->setTranAmount(-($trancom->getTranAmount()));
//                $trancrntcom->setTranFees(0);
//                $trancrntcom->setTranDescription('Code id is: ' . $code->getId());
//                $trancrntcom->setTranCurrency($trancom->getAccount()->getAccCurrency());
//                $trancrntcom->setTranDate(new \DateTime('now'));
//                $trancrntcom->setTranInsert(new \DateTime('now'));
//                $trancrntcom->setCode($code);
//                $trancrntcom->setTranAction('crnt');
//                $trancrntcom->setTranType(0);
//                $trancrntcom->setUser($user);
//                $trancrntcom->setTranBookingValue(null);
//                $trancrntcom->setTranBalance($trancom->getAccount()->getAccBalance());
//                $em->persist($trancrntcom);
//
//                $em->flush();
//                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
//            }
//            catch(\Exception $e)
//            {
//                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('the_operation_failed',array(),'message'));
//            }
//        }
//        else
//        {
//            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('code_must_be_Inactive',array(),'message'));
//        }
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
                $user= $this->getUser();

                $transale = $em->getRepository('HelloDiAccountingBundle:Transaction')->findOneBy(array('Code'=>$code,'tranAction'=>'sale'),array('id'=>'desc'));
                $trancom = $em->getRepository('HelloDiAccountingBundle:Transaction')->findOneBy(array('Code'=>$code,'tranAction'=>'com'),array('id'=>'desc'));

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

                $tranadd = $em->getRepository('HelloDiAccountingBundle:Transaction')->findOneBy(array('Code'=>$code,'tranAction'=>'add'),array('id'=>'desc'));

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

                if($tranadd->getAccount()->getAccBalance()<=15000)
                    $this->forward('hello_di_di_notification:NewAction',array('id'=>null,'type'=>12,'value'=>'15000 '.$tranadd->getAccount()->getAccCurrency().'   ('.$tranadd->getAccount()->getAccName().')'));


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
//        $em = $this->getDoctrine()->getManager();
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
