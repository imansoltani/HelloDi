<?php
namespace HelloDi\DistributorBundle\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\AggregatorBundle\Form\SaleSearchType;
use HelloDi\RetailerBundle\Entity\Retailer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ReportController extends Controller
{
    public function salesAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $distributor = $em->getRepository('HelloDiDistributorBundle:Distributor')->findOneBy(array('account' => $this->getUser()->getAccount()));
        if(!$distributor)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'account'),'message'));

        $form = $this->createForm(new SaleSearchType($distributor->getAccount()), null, array(
                'attr' => array('class' => 'SearchForm'),
                'method' => 'get',
            ))
            ->add('search','submit', array(
                    'label'=>'Search','translation_domain'=>'common',
                ));

        $form->handleRequest($request);

        $qb = $em->createQueryBuilder()
            ->select('code as code_row, pin, item, transaction, commissioner_transaction, ret_account, dist_account')
            ->from('HelloDiAggregatorBundle:Code', 'code')
            ->innerJoin('code.item', 'item')
            ->innerJoin('code.pins', 'pin')
            ->innerJoin('pin.transaction', 'transaction')
            ->innerJoin('transaction.account', 'ret_account')
            ->innerJoin('pin.commissionerTransaction', 'commissioner_transaction')
            ->innerJoin('commissioner_transaction.account', 'dist_account')
            ->where('dist_account = :dist_account')->setParameter('dist_account', $distributor->getAccount())
            ->orderBy('pin.date', 'desc');

        $group = false;

        if($form->isValid())
        {
            $form_data = $form->getData();

            $group = in_array(1, $form_data['group_by']);

            if(isset($form_data['itemType']))
                $qb->andWhere('item.type = :item_type')->setParameter('item_type', $form_data['itemType']);

            if(isset($form_data['item']))
                $qb->andWhere('item = :item')->setParameter('item', $form_data['item']);

            if(isset($form_data['retailer'])) {
                /** @var Retailer $retailer */
                $retailer = $form_data['retailer'];
                $qb->andWhere('ret_account = :ret_account')->setParameter('ret_account', $retailer->getAccount());
            }

            if(isset($form_data['from']))
                $qb->andWhere('pin.date >= :from')->setParameter('from', $form_data['from']);

            if(isset($form_data['to']))
                $qb->andWhere('pin.date <= :to')->setParameter('to', $form_data['to']);

            if($group)
                $qb ->addSelect('count(code.id) as quantity, DATE(pin.date) AS groupDate, sum(transaction.amount / pin.count) as sum_retailer, sum(commissioner_transaction.amount / pin.count) as sum_distributor')
                    ->groupBy('groupDate, item, ret_account');
        }

        $sales = $this->get('knp_paginator')->paginate($qb->getQuery()->getResult(), $request->get('page', 1), 20);

        return $this->render('HelloDiDistributorBundle:report:sales.html.twig', array(
                'distributor' => $distributor,
                'sales' => $sales,
                'form' => $form->createView(),
                'group' => $group
            ));
    }
}
