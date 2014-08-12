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
            $parameters = $request->getSession()->get('code_search', array());
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
                ->select('code','input','item')
                ->from('HelloDiAggregatorBundle:Code','code')
                ->join('code.input','input')
                ->join('code.item','item')
            ;

            if(isset($form_data['provider']))
                $qb->andWhere('input.provider = :provider')->setParameter('provider', $form_data['provider']);

            if(isset($form_data['item']))
                $qb->andWhere('item = :item')->setParameter('item', $form_data['item']);

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

        $input_transactions = $em->createQueryBuilder()
            ->select('transaction.date, account.name, account.type, user.firstName, user.lastName, transaction.amount, transaction.description')
            ->from('HelloDiAggregatorBundle:Input', 'input')
            ->innerJoin('input.codes', 'code')
            ->where('code = :code')->setParameter('code', $code)
            ->innerJoin('input.providerTransaction', 'transaction')
            ->innerJoin('transaction.account', 'account')
            ->innerJoin('input.user', 'user')
            ->getQuery()->getArrayResult();

        $pin_transactions = $em->createQueryBuilder()
            ->select('transaction.date, account.name, account.type, user.firstName, user.lastName, transaction.amount, transaction.description')
            ->from('HelloDiAggregatorBundle:Pin', 'pin')
            ->innerJoin('pin.codes', 'code')
            ->where('code = :code')->setParameter('code', $code)
            ->innerJoin('pin.transaction', 'transaction')
            ->innerJoin('transaction.account', 'account')
            ->innerJoin('pin.user', 'user')
            ->getQuery()->getArrayResult();

        $pin_comm_transactions = $em->createQueryBuilder()
            ->select('transaction.date, account.name, account.type, user.firstName, user.lastName, transaction.amount, transaction.description')
            ->from('HelloDiAggregatorBundle:Pin', 'pin')
            ->innerJoin('pin.codes', 'code')
            ->where('code = :code')->setParameter('code', $code)
            ->innerJoin('pin.commissionerTransaction', 'transaction')
            ->innerJoin('transaction.account', 'account')
            ->innerJoin('pin.user', 'user')
            ->getQuery()->getArrayResult();

        $transactions = array_merge($input_transactions, $pin_transactions, $pin_comm_transactions);

        usort($transactions, function(array $a, array $b) {
                if ($a['date'] == $b['date']) return 0;
                return ($a['date'] < $b['date']) ? 1 : -1;
            });

        return $this->render('HelloDiMasterBundle:code:history.html.twig', array(
            'code'=> $code,
            'transactions' => $transactions
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
}
