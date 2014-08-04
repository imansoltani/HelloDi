<?php

namespace HelloDi\PricingBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\PricingBundle\Entity\Model;
use HelloDi\PricingBundle\Entity\Price;
use HelloDi\PricingBundle\Form\ModelType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class RetailerModelController extends Controller
{
    public function indexAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var Account $account */
        $account = $this->getUser()->getAccount();

        $models = $em->getRepository("HelloDiPricingBundle:Model")->findBy(array("account"=>$account));

        return $this->render("HelloDiPricingBundle:retailerModel:index.html.twig",array(
            'models' => $models
        ));
    }

    public function addAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var Account $distAccount */
        $distAccount = $this->getUser()->getAccount();

        $model = new Model();

        $form = $this->createForm(new ModelType(),$model)
            ->add('add','submit', array(
                    'label'=>'Add','translation_domain'=>'common',
                    'attr'=>array('first-button','onclick'=>"$('#json').val(getJson())")
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('last-button','onclick'=>'window.location.assign("'.$this->generateUrl('hello_di_pricing_retailer_model_index').'")')
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
                        $price = $em->getRepository("HelloDiPricingBundle:Price")->find($key);
                        if(!$price || $price->getAccount() != $distAccount) throw new \Exception('Account has not this item.');

                        if(!is_numeric($value)) throw new \Exception('amount must be numeric.');

                        if($value < $price->getPrice()) throw new \Exception('amount must be larger than price.');
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
                $model->setAccount($distAccount);
                $em->persist($model);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
                return $this->redirect($this->generateUrl("hello_di_pricing_retailer_model_index"));
            }
        }

        $prices = $em->createQueryBuilder()
            ->select('price')
            ->from('HelloDiPricingBundle:Price','price')
            ->where('price.account = :account')->setParameter('account', $distAccount)
            ->getQuery()->getResult();

        return $this->render("HelloDiPricingBundle:retailerModel:new.html.twig",array(
                'prices' => $prices,
                'amounts' => $amounts,
                'form' => $form->createView(),
        ));
    }

    public function editAction(Request $request,$id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var Account $distAccount */
        $distAccount = $this->getUser()->getAccount();

        $model = $em->getRepository("HelloDiPricingBundle:Model")->find($id);

        if (!$model || $model->getAccount() != $distAccount) {
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'model'),'message'));
        }

        $form = $this->createForm(new ModelType(),$model,array('attr'=>array(
                'class' => 'YesNoMessage',
                'header' => $this->get('translator')->trans('Register_transaction',array(),'message'),
                'message' => 'Are you sure you perform this operation?<br>All Prices from retailers that has this model will be update.',
            )))
            ->add('update','submit', array(
                    'label'=>'Update','translation_domain'=>'common',
                    'attr'=>array('first-button','onclick'=>"$('#json').val(getJson())")
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('last-button','onclick'=>'window.location.assign("'.$this->generateUrl('hello_di_pricing_retailer_model_index').'")')
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
                        $price = $em->getRepository("HelloDiPricingBundle:Price")->find($key);
                        if(!$price || $price->getAccount() != $distAccount) throw new \Exception('Account has not this item.');

                        if(!is_numeric($value)) throw new \Exception('amount must be numeric.');

                        if($value < $price->getPrice()) throw new \Exception('amount must be larger than price.');
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

                return $this->redirect($this->generateUrl("hello_di_pricing_retailer_model_index"));
            }
        }

        $prices = $em->createQueryBuilder()
            ->select('price')
            ->from('HelloDiPricingBundle:Price','price')
            ->where('price.account = :account')->setParameter('account', $distAccount)
            ->getQuery()->getResult();

        return $this->render("HelloDiPricingBundle:retailerModel:edit.html.twig",array(
                'prices' => $prices,
                'amounts' => $amounts,
                'form' => $form->createView(),
            ));
    }

    public function DeleteAction($id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var Account $account */
        $account = $this->getUser()->getAccount();

        $model = $em->getRepository("HelloDiPricingBundle:Model")->find($id);

        if (!$model || $model->getAccount() != $account) {
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'model'),'message'));
        }

        $em->remove($model);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully',array(),'message'));
        return $this->redirect($this->generateUrl("hello_di_pricing_retailer_model_index"));
    }

    public function setModelAction(Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $retailer = $em->getRepository('HelloDiRetailerBundle:Retailer')->findByAccountId($id);
        if(!$retailer)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'account'),'message'));

        $form = $this->createFormBuilder($retailer->getAccount(),array('attr'=>array(
                'class' => 'YesNoMessage',
                'header' => $this->get('translator')->trans('Register_transaction',array(),'message'),
                'message' => 'Are you sure you perform this operation?<br>All Prices from this retailer will be change.',
            )))
            ->add('model', 'entity', array(
                    'class' => 'HelloDiPricingBundle:Model',
                    'property' => 'name',
                    'query_builder' => function (EntityRepository $er) use ($retailer) {
                            return $er->createQueryBuilder('u')
                                ->where('u.account = :distAccount')->setParameter('distAccount', $retailer->getDistributor()->getAccount());
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
                    'attr'=>array('onclick'=>'window.location.assign("'.$this->generateUrl('hello_di_distributor_retailer_item', array('id' => $id)).'")','last-button')
                ))
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->flush();

                if($this->updatePricesFromModel($retailer->getAccount()->getModel(), $retailer->getAccount()))
                    $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('All prices of this account updated.',array(),'message'));
                else
                    $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('Error in updating prices of this account.',array(),'message'));

                return $this->redirect($this->generateUrl('hello_di_distributor_retailer_item', array('id' => $id)));
            }
        }

        return $this->render('HelloDiPricingBundle:retailerModel:setModel.html.twig', array(
                'retailerAccount' => $retailer->getAccount(),
                'form' => $form->createView()
            ));
    }

    private function updatePricesFromModel(Model $model, Account $account = null)
    {
        try
        {
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();

            /** @var Price[] $prices */
            $prices = array();

            /** @var Account[] $accounts */
            $accounts = $account ? array($account) : $model->getAccounts();

            foreach ($accounts as $account)
            {
                $new_prices = json_decode($model->getJson(), true);

                foreach ($account->getPrices() as $price)
                {
                    /** @var Price $price */
                    if(isset($new_prices[$price->getId()])){
                        $price->setPrice($new_prices[$price->getId()]);
                        $new_prices[$price->getId()] = 'read';
                    }
                    else
                        $em->remove($price);
                }

                foreach ($new_prices as $key=>$new_price)
                {
                    if($new_price != 'read'){
                        if(!isset($prices[$key]))
                            $prices[$key] = $em->getRepository('HelloDiPricingBundle:Price')->find($key);

                        $price = new Price();
                        $price->setPrice($new_price);
                        $price->setAccount($account);
                        $price->setItem($prices[$key]->getItem());
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