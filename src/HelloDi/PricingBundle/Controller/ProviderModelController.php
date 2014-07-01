<?php

namespace HelloDi\PricingBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\PricingBundle\Entity\Model;
use HelloDi\PricingBundle\Form\ModelType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class ProviderModelController extends Controller
{
    public function indexAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $models = $em->getRepository("HelloDiPricingBundle:Model")->findBy(array("account" => null));

        return $this->render("HelloDiPricingBundle:ProviderModel:index.html.twig",array(
            'models' => $models
        ));
    }

    public function addAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $model = new Model();

        $currencies = $this->container->getParameter('Currencies.Account');

        $form = $this->createForm(new ModelType($currencies),$model)
            ->add('submit','submit', array(
                    'label'=>'Add','translation_domain'=>'common',
                    'attr'=>array('first-button','onclick'=>"$('#json').val(getJson())")
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('last-button','onclick'=>'window.location.assign("'.$this->generateUrl('hello_di_pricing_provider_model_index').'")')
                ))
        ;

        $dataArray = [];

        if($request->isMethod('post'))
        {
            $form->handleRequest($request);

            $jsonArray = json_decode($model->getJson(),true);

            if(is_array($jsonArray) && count($jsonArray)>0)
            {
                try {
                    foreach($jsonArray as $row) {
                        $item = $em->getRepository("HelloDiCoreBundle:Item")->find($row['id']);
                        if(!$item) throw new \Exception("Item doesn't exist.");

                        if($item->getCurrency() != $model->getCurrency()) {
                            if(!$em->getRepository('HelloDiCoreBundle:Denomination')->findOneBy(array(
                                    'item' => $item,
                                    'currency' => $model->getCurrency()
                                )))
                                $form->addError(new FormError("Currency of an Item not equal to selected Currency or An Item hasn't denomination with selected currency."));
                        }

                        if(!is_numeric($row['amount'])) throw new \Exception('amount must be numeric.');
                    }
                }
                catch(\Exception $ex) {
                    $form->addError(new FormError($ex->getMessage()));
                }
            }
            else
                $form->addError(new FormError("minimum count must be 1."));

            if($form->isValid())
            {
                $model->setAccount(null);
                $em->persist($model);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl("hello_di_pricing_provider_model_index"));
            }

            foreach(json_decode($model->getJson(),true) as $row)
                $dataArray[$row['id']] = $row['amount'];
        }

        $items = $em->createQueryBuilder()
            ->select('item')
            ->from('HelloDiCoreBundle:Item','item')
            ->getQuery()->getArrayResult();

        foreach($items as &$item)
            $item['amount'] = isset($dataArray[$item['id']])?$dataArray[$item['id']]:"";

        return $this->render("HelloDiPricingBundle:ProviderModel:new.html.twig",array(
            'json_data'=>json_encode($items),
            'form' => $form->createView(),
        ));
    }

    public function editAction(Request $request,$id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var Account $account */
        $account = $this->getUser()->getAccount();

        $model = $em->getRepository("HelloDiPricingBundle:Model")->find($id);

        if (!$model || $model->getAccount() != $account) {
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
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                $em->flush();
                return $this->redirect($this->generateUrl("hello_di_pricing_model_index"));
            }
        }

        /** @var Query $prices */
        $prices = $em->createQueryBuilder()
            ->select('price.id','item.itemCode','item.itemName','price.price')
            ->from('HelloDiPricingBundle:Price','price')
            ->innerJoin('price.Item','item')
            ->where('price.Account = :acc')->setParameter('acc',$account)
            ->where('price.priceStatus = :true')->setParameter('true',true)
            ->getQuery()->getArrayResult();

        $dataArray = [];
        foreach(json_decode($model->getJson(),true) as $row)
            $dataArray[$row['PriceId']] = $row['Amount'];

        foreach($prices as &$price)
            $price['amount'] = isset($dataArray[$price['id']])?$dataArray[$price['id']]:"";

        return $this->render("HelloDiPricingBundle:ProviderModel:edit.html.twig",array(
            'json_data'=>json_encode($prices),
            'form' => $form->createView(),
        ));
    }

    public function DeleteAction($id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $model = $em->getRepository("HelloDiPricingBundle:Model")->find($id);
        if (!$model || $model->getAccount() != null) {
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'model'),'message'));
        }

        $em->remove($model);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
        return $this->redirect($this->generateUrl("hello_di_pricing_provider_model_index"));
    }
}