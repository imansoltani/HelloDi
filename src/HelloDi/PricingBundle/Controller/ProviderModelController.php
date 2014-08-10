<?php

namespace HelloDi\PricingBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\CoreBundle\Entity\Item;
use HelloDi\PricingBundle\Entity\Model;
use HelloDi\PricingBundle\Entity\Price;
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

        return $this->render("HelloDiPricingBundle:providerModel:index.html.twig",array(
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
            ->add('add','submit', array(
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
                        if(!$item)
                            throw new \Exception("Item doesn't exist.");

                        if($item->getCurrency() != $model->getCurrency())
                            throw new \Exception("Currency of an Item not equal to selected Currency.");

                        if(!is_numeric($value))
                            throw new \Exception('amount must be numeric.');
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

        $items = $em->getRepository('HelloDiCoreBundle:Item')->findAll();

        return $this->render("HelloDiPricingBundle:providerModel:new.html.twig",array(
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

        $form = $this->createForm(new ModelType($currencies),$model,array('attr'=>array(
                'class' => 'YesNoMessage',
                'header' => $this->get('translator')->trans('Register_transaction',array(),'message'),
                'message' => 'Are you sure you perform this operation?<br>All Prices from providers that has this model will be update.',
            )))
            ->add('update','submit', array(
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
                        if(!$item)
                            throw new \Exception("Item doesn't exist.");

                        if($item->getCurrency() != $model->getCurrency())
                            throw new \Exception("Currency of an Item not equal to selected Currency.");

                        if(!is_numeric($value))
                            throw new \Exception('amount must be numeric.');
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

                if($this->updatePricesFromModel($model))
                    $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('All prices of this account updated.',array(),'message'));
                else
                    $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('Error in updating prices of this account.',array(),'message'));

                return $this->redirect($this->generateUrl("hello_di_pricing_provider_model_index"));
            }
        }

        $items = $em->getRepository('HelloDiCoreBundle:Item')->findAll();

        return $this->render("HelloDiPricingBundle:providerModel:edit.html.twig",array(
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

    public function setModelAction(Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $provider = $em->getRepository('HelloDiAggregatorBundle:Provider')->findByAccountId($id);
        if(!$provider)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'account'),'message'));

        $form = $this->createFormBuilder($provider->getAccount(),array('attr'=>array(
                'class' => 'YesNoMessage',
                'header' => $this->get('translator')->trans('Register_transaction',array(),'message'),
                'message' => 'Are you sure you perform this operation?<br>All Prices from this provider will be change.',
            )))
            ->add('model', 'entity', array(
                    'class' => 'HelloDiPricingBundle:Model',
                    'property' => 'name',
                    'query_builder' => function (EntityRepository $er) use ($provider) {
                            return $er->createQueryBuilder('u')
                                ->where('u.account is null')
                                ->andWhere("u.currency = :currency")->setParameter('currency', $provider->getCurrency());
                        },
                    'expanded' => true,
                    'required' => true,
                    'label' => 'model','translation_domain' => 'model'
                ))
            ->add('update','submit', array(
                    'label'=>'Update','translation_domain'=>'common',
                    'attr'=>array('first-button')
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('onclick'=>'window.location.assign("'.$this->generateUrl('hello_di_master_provider_items', array('id' => $id)).'")','last-button')
                ))
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->flush();

                if($this->updatePricesFromModel($provider->getAccount()->getModel(), $provider->getAccount()))
                    $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('All prices of this account updated.',array(),'message'));
                else
                    $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('Error in updating prices of this account.',array(),'message'));

                return $this->redirect($this->generateUrl('hello_di_master_provider_items', array('id' => $id)));
            }
        }

        return $this->render('HelloDiPricingBundle:providerModel:setModel.html.twig', array(
                'account' => $provider->getAccount(),
                'form' => $form->createView()
            ));
    }

    private function updatePricesFromModel(Model $model, Account $account = null)
    {
        try
        {
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();

            /** @var Item[] $items */
            $items = array();

            /** @var Account[] $accounts */
            $accounts = $account ? array($account) : $model->getAccounts();

            foreach ($accounts as $account)
            {
                $new_prices = json_decode($model->getJson(), true);

                foreach ($account->getPrices() as $price)
                {
                    /** @var Price $price */
                    if(isset($new_prices[$price->getItem()->getId()])){
                        $price->setPrice($new_prices[$price->getItem()->getId()]);
                        $new_prices[$price->getItem()->getId()] = 'read';
                    }
                    else
                        $em->remove($price);
                }

                foreach ($new_prices as $key=>$new_price)
                {
                    if($new_price != 'read'){
                        if(!isset($items[$key]))
                            $items[$key] = $em->getRepository('HelloDiCoreBundle:Item')->find($key);

                        $price = new Price();
                        $price->setPrice($new_price);
                        $price->setAccount($account);
                        $price->setItem($items[$key]);
                        $account->addPrice($price);
                        $em->persist($price);
                    }
                }
            }

            $em->flush();

            return true;
        }
        catch(\Exception $e)
        {
            return false;
        }
    }
}