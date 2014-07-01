<?php

namespace HelloDi\PricingBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
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

        $amounts = [];

        if($request->isMethod('post'))
        {
            $form->handleRequest($request);

            $amounts = json_decode($model->getJson(),true);

            if(is_array($amounts) && count($amounts)>0)
            {
                try {
                    foreach($amounts as $key=>$value) {
                        $item = $em->getRepository("HelloDiCoreBundle:Item")->find($key);
                        if(!$item) throw new \Exception("Item doesn't exist.");

                        if($item->getCurrency() != $model->getCurrency()) {
                            if(!$em->getRepository('HelloDiCoreBundle:Denomination')->findOneBy(array(
                                    'item' => $item,
                                    'currency' => $model->getCurrency()
                                )))
                                $form->addError(new FormError("Currency of an Item not equal to selected Currency or An Item hasn't denomination with selected currency."));
                        }

                        if(!is_numeric($value)) throw new \Exception('amount must be numeric.');
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
                $em->persist($model);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl("hello_di_pricing_provider_model_index"));
            }
        }

        $items = $em->createQueryBuilder()
            ->select('item', 'denominations')
            ->from('HelloDiCoreBundle:Item','item')
            ->LeftJoin('item.denominations', 'denominations')
            ->getQuery()->getResult();

        return $this->render("HelloDiPricingBundle:ProviderModel:new.html.twig",array(
                'items' => $items,
                'amounts' => $amounts,
                'form' => $form->createView(),
        ));
    }

    public function editAction(Request $request,$id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $model = $em->getRepository("HelloDiPricingBundle:Model")->find($id);

        $currencies = $this->container->getParameter('Currencies.Account');

        if (!$model || $model->getAccount() != null) {
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'model'),'message'));
        }

        $form = $this->createForm(new ModelType($currencies),$model)
            ->add('submit','submit', array(
                    'label'=>'Update','translation_domain'=>'common',
                    'attr'=>array('first-button','onclick'=>"$('#json').val(getJson())")
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('last-button','onclick'=>'window.location.assign("'.$this->generateUrl('hello_di_pricing_provider_model_index').'")')
                ))
        ;

        $amounts = json_decode($model->getJson(),true);

        if($request->isMethod('post'))
        {
            $form->handleRequest($request);

            $amounts = json_decode($model->getJson(),true);

            if(is_array($amounts) && count($amounts)>0)
            {
                try {
                    foreach($amounts as $key=>$value) {
                        $item = $em->getRepository("HelloDiCoreBundle:Item")->find($key);
                        if(!$item) throw new \Exception("Item doesn't exist.");

                        if($item->getCurrency() != $model->getCurrency()) {
                            if(!$em->getRepository('HelloDiCoreBundle:Denomination')->findOneBy(array(
                                    'item' => $item,
                                    'currency' => $model->getCurrency()
                                )))
                                $form->addError(new FormError("Currency of an Item not equal to selected Currency or An Item hasn't denomination with selected currency."));
                        }

                        if(!is_numeric($value)) throw new \Exception('amount must be numeric.');
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
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl("hello_di_pricing_provider_model_index"));
            }
        }

        $items = $em->createQueryBuilder()
            ->select('item', 'denominations')
            ->from('HelloDiCoreBundle:Item','item')
            ->LeftJoin('item.denominations', 'denominations')
            ->getQuery()->getResult();

        return $this->render("HelloDiPricingBundle:ProviderModel:edit.html.twig",array(
                'items' => $items,
                'amounts' => $amounts,
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