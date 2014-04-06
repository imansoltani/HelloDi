<?php

namespace HelloDi\PricingBundle\Controller;

use Doctrine\ORM\Query;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\PricingBundle\Entity\Model;
use HelloDi\PricingBundle\Form\ModelType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ModelController extends Controller
{
    public function indexModelAction()
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Account $account */
        $account = $this->getUser()->getAccount();

        $models = $em->getRepository("HelloDiPricingBundle:Model")->findBy(array("account"=>$account));

        return $this->render("HelloDiPricingBundle:Model:index.html.twig",array(
            'models' => $models
        ));
    }

    public function NewDistributorModelAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Account $account */
        $account = $this->getUser()->getAccount();

        $model = new Model();

        $form = $this->createForm(new ModelType(),$model);

        $dataArray = [];

        if($request->isMethod('post'))
        {
            $form->handleRequest($request);
            $jsonArray = json_decode($model->getJson(),true);

            if(is_array($jsonArray) && count($jsonArray)>0)
            {
                try
                {
                    foreach($jsonArray as $row)
                    {
                        $price = $em->getRepository("HelloDiPricingBundle:Price")->find($row['PriceId']);
                        if(!$price || $price->getAccount()!=$account) throw new \Exception('Account has not this item.');
                        if(!is_numeric($row['Amount'])) throw new \Exception('amount must be numeric.');
                        if($row['Amount'] < $price->getPrice()) throw new \Exception('amount must be larger than price.');
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
                $model->setAccount($account);
                $em->persist($model);
                $em->flush();
                return new Response('Done');
            }

            foreach(json_decode($model->getJson(),true) as $row)
                $dataArray[$row['PriceId']] = $row['Amount'];
        }

        /** @var Query $prices */
        $prices = $em->createQueryBuilder()
            ->select('price.id','item.itemCode','item.itemName','price.price')
            ->from('HelloDiPricingBundle:Price','price')
            ->innerJoin('price.Item','item')
            ->where('price.Account = :acc')->setParameter('acc',$account)
            ->getQuery()->getArrayResult();

        foreach($prices as &$price)
            $price['amount'] = isset($dataArray[$price['id']])?$dataArray[$price['id']]:"";

        return $this->render("HelloDiPricingBundle:Model:new.html.twig",array(
            'json_data'=>json_encode($prices),
            'form' => $form->createView(),
        ));
    }

    public function EditDistributorModelAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Account $account */
        $account = $this->getUser()->getAccount();

        $model = $em->getRepository("HelloDiPricingBundle:Model")->find($id);

        if (!$model) {
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'model'),'message'));
        }

        $form = $this->createForm(new ModelType(),$model);

        if($request->isMethod('post'))
        {
            $form->handleRequest($request);
            $jsonArray = json_decode($model->getJson(),true);

            if(is_array($jsonArray) && count($jsonArray)>0)
            {
                try
                {
                    foreach($jsonArray as $row)
                    {
                        $price = $em->getRepository("HelloDiPricingBundle:Price")->find($row['PriceId']);
                        if(!$price || $price->getAccount()!=$account) throw new \Exception('Account has not this item.');
                        if(!is_numeric($row['Amount'])) throw new \Exception('amount must be numeric.');
                        if($row['Amount'] < $price->getPrice()) throw new \Exception('amount must be larger than price.');
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
        }

        /** @var Query $prices */
        $prices = $em->createQueryBuilder()
            ->select('price.id','item.itemCode','item.itemName','price.price')
            ->from('HelloDiPricingBundle:Price','price')
            ->innerJoin('price.Item','item')
            ->where('price.Account = :acc')->setParameter('acc',$account)
            ->getQuery()->getArrayResult();

        $dataArray = [];
        foreach(json_decode($model->getJson(),true) as $row)
            $dataArray[$row['PriceId']] = $row['Amount'];

        foreach($prices as &$price)
            $price['amount'] = isset($dataArray[$price['id']])?$dataArray[$price['id']]:"";

        return $this->render("HelloDiPricingBundle:Model:edit.html.twig",array(
            'json_data'=>json_encode($prices),
            'form' => $form->createView(),
        ));
    }
}