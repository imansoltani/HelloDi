<?php
namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class B2BReportController extends Controller
{
    public function indexAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('item','entity',array(
                'class'=>'HelloDi\DiDistributorsBundle\Entity\Item',
                'property' => 'itemName',
                'empty_value'=>'All',
                'empty_data'=>'',
                'required'=>false,'label'=>'Name','translation_domain' => 'item',
                'query_builder' => function(EntityRepository $er)
                {
                    return $er->createQueryBuilder('u')->where("u.itemType = 'imtu' ");
                }
            ))
            ->add('user','entity',array(
                'class'=>'HelloDi\DiDistributorsBundle\Entity\User',
                'property' => 'username',
                'empty_value'=>'All',
                'empty_data'=>'',
                'required'=>false,'label'=>'UserName','translation_domain' => 'user',
                'query_builder' => function(EntityRepository $er)
                {
                    return $er->createQueryBuilder('u')->innerJoin('u.B2BLogs','b2blog');
                }
            ))
            ->add('status','choice',array(
                'required'=>false,'label'=>'status','translation_domain' => 'b2b',
                'empty_value'=>'All',
                'empty_data'=>'',
                'choices' => array('1' => 'done', '0' => 'error', '2' => 'null'),
            ))
            ->add('fromdate','date',array(
                'required'=>false,'label'=>'fromDate','translation_domain' => 'b2b',
                'widget' => 'single_text', 'format' => 'yyyy/MM/dd'
            ))
            ->add('todate','date',array(
                'required'=>false,'label'=>'toDate','translation_domain' => 'b2b',
                'widget' => 'single_text', 'format' => 'yyyy/MM/dd'
            ))
            ->getForm();

        $em = $this->getDoctrine()->getEntityManager();

        $qb = $em->createQueryBuilder()
            ->select('b2blog')
            ->from('HelloDiDiDistributorsBundle:B2BLog','b2blog');

        if($request->isMethod('post'))
        {
            $form->handleRequest($request);
            $data = $form->getData();

            if($data['item']!='')   $qb = $qb->where('b2blog.Item = :item')->setParameter('item',$data['item']);
            if($data['user']!='')   $qb = $qb->where('b2blog.User = :user')->setParameter('user',$data['user']);
            switch ($data['status'])
            {
                case '1': $qb = $qb->where('b2blog.status = 1'); break;
                case '0': $qb = $qb->where('b2blog.status = 0'); break;
                case '2': $qb = $qb->where('b2blog.status is null'); break;
            }
            if($data['fromdate']!='') $qb = $qb->andWhere('b2blog.date >= :fromdate')->setParameter('fromdate',$data['fromdate']);
            if($data['todate']!='')   $qb = $qb->andWhere('b2blog.date <= :todate')->setParameter('todate',$data['todate']);
        }

        $qb = $qb->orderBy('b2blog.id', 'desc');

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $qb,
            $request->get('page',1),
            10,
            array('distinct' => false)
        );

        return $this->render('HelloDiDiDistributorsBundle:B2B_Report:index.html.twig',array(
            'pagination'=>$pagination,
            'form'=>$form->createView()
        ));
    }

    public function UpdateImtuTransactionAction()
    {
        $em= $this->getDoctrine()->getEntityManager();

        $logs = $em->getRepository('HelloDiDiDistributorsBundle:b2blog')->findBy(array('status'=>null));

        foreach($logs as $log)
        {

        }
    }
}