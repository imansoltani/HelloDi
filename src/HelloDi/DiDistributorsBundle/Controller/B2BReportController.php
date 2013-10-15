<?php
namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Helper\SoapClientTimeout;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

        $em = $this->getDoctrine()->getManager();

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
        ini_set('max_execution_time', 60);
        $em= $this->getDoctrine()->getManager();
        $firstStatusNullDate = clone $em->getRepository('HelloDiDiDistributorsBundle:b2blog')->findOneBy(array('status'=>null),array('id'=>'asc'))->getDate();
        $lastStatusNullDate = clone $em->getRepository('HelloDiDiDistributorsBundle:b2blog')->findOneBy(array('status'=>null),array('id'=>'desc'))->getDate();

        try
        {
            $client = new SoapClientTimeout($this->container->getParameter('B2BServer.WSDL'));
            $client->__setTimeout(40);
            $result = $client->QueryAccount(array(
                'Request' => array(
                    'UserInfo' => array(
                        'UserName'=>$this->container->getParameter('B2BServer.UserName'),
                        'Password'=>$this->container->getParameter('B2BServer.Password')
                    ),
                    'ClientReferenceData' => array(),
                    'Parameters' => array(
                        'ServiceNumber' => $this->container->getParameter('B2BServer.ServiceNumber'),
                        'ReturnBillingHistory' => 'Y',
                        'DateFrom' => date_format($firstStatusNullDate->modify('-1 day'),'Y-m-d'),
                        'DateTo' => date_format($lastStatusNullDate->modify('+1 day'),'Y-m-d')
                    ),
                )
            ));
            return $this->redirect($this->getRequest()->headers->get('referer'));
            $QueryAccountResponse = $result->QueryAccountResponse;
            die(print_r($QueryAccountResponse));
    //        return new Response($client->__getLastResponse(),200,array('Content-Type'=>'xml'));

            $logs = $em->getRepository('HelloDiDiDistributorsBundle:b2blog')->findBy(array('status'=>null));

            $dataList = $QueryAccountResponse->BillDataList->Data;
            $i = 0;
            foreach($logs as $log)
            {
                $i = $this->findDatainB2BLog($log->getClientTransactionID(),$dataList,$i);
                $data = is_array($dataList)?$dataList[$i]:$dataList;
                $log->setStatus($data->Description=="Success"?1:0);
                $log->setTransactionID('Update_Log');
                if($log->getStatus()==1)
                {

                }
                else
                {
                    $log->setStatusCode('Update_Log');
                }
            }

        }
        catch(\Exception $e)
        {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('error_b2b',array(),'message'));
        }

    }

    private function findDatainB2BLog($MyClientId,$dataList,$i)
    {
        if(is_array($dataList))
        {
            while($dataList[$i]->BillTransactionID!=$MyClientId)
            {
                $i++;
                if($i == count($dataList)) return false;
            }
            return $i;
        }
        else
        {
            if ($dataList->BillTransactionID==$MyClientId)
                return 0;
            else
                return false;
        }
    }
}