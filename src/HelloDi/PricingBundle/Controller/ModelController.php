<?php

namespace HelloDi\PricingBundle\Controller;

use Doctrine\ORM\Query;
use HelloDi\PricingBundle\Entity\Model;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ModelController extends Controller
{
    public function indexModelAction()
    {

    }

    public function NewDistributorModelAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $account = $em->getRepository("HelloDiAccountingBundle:Account")->find(2);//$this->getUser()->getAccount()

        if($request->isMethod('post'))
        {
            $json = $request->get('json','[]');
            $jsonArray = json_decode($json,true);

            if(is_array($jsonArray) && count($jsonArray)>0)
            {
                try
                {
                    foreach($jsonArray as $row)
                    {
                        $price = $em->getRepository("HelloDiPricingBundle:Price")->find($row['PriceId']);
                        if(!$price || $price->getAccount()!=$account) throw new \Exception('Account has not this item.');
                        if($row['Amount'] < $price->getPrice()) throw new \Exception('amount must be larger than price.');
                    }
                }
                catch(\Exception $ex)
                {
                    return new Response($ex->getMessage());
                }
                $model = new Model();
                $model->setName($request->get('name'));
                $model->setAccount($account);
                $model->setJson($json);
                $em->persist($model);
                $em->flush();
                return new Response('Done');
            }
            return new Response('Error');
        }

        /** @var Query $prices */
        $prices = $em->createQueryBuilder()
            ->select('price.id','item.itemCode','item.itemName','price.price')
            ->from('HelloDiPricingBundle:Price','price')
            ->innerJoin('price.Item','item')
            ->where('price.Account = :acc')->setParameter('acc',$account)
            ->getQuery();

        return $this->render("HelloDiPricingBundle:Model:new.html.twig",array(
            'json_data'=>json_encode($prices->getArrayResult())
        ));

    }

    public function EditDistributorModelAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $account = $em->getRepository("HelloDiAccountingBundle:Account")->find(2);//$this->getUser()->getAccount()

        $model = $em->getRepository("HelloDiPricingBundle:Model")->find($id);

        if (!$model) {
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'model'),'message'));
        }

        $form = $this->createFormBuilder($model)
            ->add("name",'text',array('label' => 'Model Name','required'=>true,'translation_domain' => 'transaction'))
            ->add("json",'hidden')
            ->getForm();

        /** @var Query $prices */
        $prices = $em->createQueryBuilder()
            ->select('price.id','item.itemCode','item.itemName','price.price')
            ->from('HelloDiPricingBundle:Price','price')
            ->innerJoin('price.Item','item')
            ->where('price.Account = :acc')->setParameter('acc',$account)
            ->getQuery()->getArrayResult();

        $dataArray = [];

        if($request->isMethod('get'))
        {
            foreach(json_decode($model->getJson(),true) as $row)
                $dataArray[$row['PriceId']] = $row['Amount'];

            foreach($prices as &$price)
                $price['amount'] = isset($dataArray[$price['id']])?$dataArray[$price['id']]:"";
        }
        elseif($request->isMethod('post'))
        {
            $form->handleRequest($request);
            foreach(json_decode($model->getJson(),true) as $row)
                $dataArray[$row['PriceId']] = $row['Amount'];

            if(is_array($dataArray) && count($dataArray)>0)
            {
                try
                {
                    foreach($dataArray as $key=>$value)
                    {
                        $price = $em->getRepository("HelloDiPricingBundle:Price")->find($key);
                        if(!$price || $price->getAccount()!=$account) throw new \Exception('Account has not this item.');
                        if(!is_numeric($value)) throw new \Exception('amount must be numeric.');
                        if($value < $price->getPrice()) throw new \Exception('amount must be larger than price.');
                    }
                }
                catch(\Exception $ex)
                {
                    $form->addError(new FormError($ex->getMessage()));
                }
            }
            else
                $form->addError(new FormError("minimum count must be 1."));

            if($form->isValid())
            {
                $em->flush();
                return new Response('Done');
            }
            foreach($prices as &$price)
                $price['amount'] = isset($dataArray[$price['id']])?$dataArray[$price['id']]:"";
        }

        return $this->render("HelloDiPricingBundle:Model:edit.html.twig",array(
            'json_data'=>json_encode($prices),
            'form' => $form->createView(),
        ));

    }
}