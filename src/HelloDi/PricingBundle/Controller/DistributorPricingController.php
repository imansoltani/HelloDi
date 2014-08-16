<?php

namespace HelloDi\PricingBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\DistributorBundle\Entity\Distributor;
use HelloDi\PricingBundle\Entity\Price;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DistributorPricingController extends Controller
{
    public function pricingAction($id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $distributor = $em->getRepository('HelloDiDistributorBundle:Distributor')->findByAccountId($id);
        if(!$distributor)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'account'),'message'));

        $items = $em->createQueryBuilder()
            ->select('item.id as item_id','item.code','item.name','item.faceValue','item.currency as item_currency','providerPrice.price as price_provider','provider.currency as price_currency','distPrice.price')
            ->from("HelloDiAggregatorBundle:Provider", "provider")
            ->innerJoin("provider.account", "providerAccount")
            ->innerJoin("providerAccount.prices", "providerPrice")
            ->innerJoin("providerPrice.item", "item")
            ->leftJoin('item.prices','distPrice','WITH','distPrice.account = :distAccount')->setParameter('distAccount',$distributor->getAccount())
            ->getQuery()->getArrayResult();

        return $this->render("HelloDiPricingBundle:distributorPricing:pricing.html.twig",array(
                'json_data'=>json_encode($items),
                'account' => $distributor->getAccount(),
                'distributor' => $distributor
        ));
    }

    public function updatePriceAction(Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $distributor = $em->getRepository('HelloDiDistributorBundle:Distributor')->findByAccountId($id);
        if(!$distributor)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'account'),'message'));

        $itemId = $request->request->get('item_id',0);
        if($itemId <= 0) return new Response('0-Item ID is incorrect.');

        $priceAmount = $request->request->get('price','');
        if($priceAmount!= "" && (!is_numeric($priceAmount) || $priceAmount<0)) return new Response('0-Price is incorrect.');

        $item = $em->getRepository('HelloDiCoreBundle:Item')->find($itemId);
        if(!$item) return new Response("0-Couldn't find the Item.");

        $info = "";

        /** @var Price $priceDist */
        $priceDist = $em->createQueryBuilder()
            ->select('price')
            ->from('HelloDiPricingBundle:Price','price')
            ->where('price.item = :item')->setParameter('item',$item)
            ->andWhere('price.account = :accDist')->setParameter('accDist',$distributor->getAccount())
            ->getQuery()->getOneOrNullResult();

        if($priceDist) {                                //price exist
            if($priceAmount == '') {
                $em->remove($priceDist);                //remove
                $info = 'removed';
            }
            else {
                $priceDist->setPrice($priceAmount);     //update
                $info = 'updated';
            }
        }
        elseif($priceAmount != '') {                    //amount not empty and price doesn't exist
            $priceProv = $em->createQueryBuilder()      //check for exist price for provider
                ->select('price')
                ->from('HelloDiPricingBundle:Price','price')
                ->where('price.item = :item')->setParameter('item',$item)
                ->innerJoin('price.account','accProv')
                ->andWhere('accProv.type = :accType')->setParameter('accType',Account::PROVIDER)
                ->getQuery()->getOneOrNullResult();

            if($priceProv) {                            //create
                $priceDist = new Price();
                $priceDist->setAccount($distributor->getAccount());
                $priceDist->setPrice($priceAmount);
                $priceDist->setItem($item);
                $priceDist->setFavourite(false);
                $em->persist($priceDist);
                $info = 'created';
            }
            else                                        //error - can't create - because any provider has not this item
                return new Response("0-No provider has this item.");
        }

        $em->flush();
        return new Response('1-'.$info);
    }

    public function copyPricesAction(Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $distributor = $em->getRepository('HelloDiDistributorBundle:Distributor')->findByAccountId($id);
        if(!$distributor)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'account'),'message'));

        $form = $this->createFormBuilder(null, array('attr' => array(
                'class' => 'YesNoMessage',
                'header' => $this->get('translator')->trans('Register_transaction',array(),'message'),
                'message' => 'Are you sure you perform this operation?<br>All Prices from this distributor will be change.',
            )))
            ->add('distributor', 'entity', array(
                    'class' => 'HelloDiDistributorBundle:Distributor',
                    'property' => 'NameWithEntity',
                    'query_builder' => function (EntityRepository $er) use ($distributor) {
                            return $er->createQueryBuilder('u')
                                ->where('u != :distributor')->setParameter('distributor', $distributor)
                                ->andWhere("u.currency = :currency")->setParameter('currency', $distributor->getCurrency());
                        },
                    'expanded' => true,
                    'required' => true,
                    'label' => 'Copy From','translation_domain' => 'model'
                ))
            ->add('update','submit', array(
                    'label'=>'Update','translation_domain'=>'common',
                    'attr'=>array('first-button')
                ))
            ->add('cancel','button',array(
                    'label'=>'Cancel','translation_domain'=>'common',
                    'attr'=>array('onclick'=>'window.location.assign("'.$this->generateUrl('hello_di_pricing_distributor_item_index', array('id' => $id)).'")','last-button')
                ))
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                if($this->copyPrices($form->get('distributor')->getData(), $distributor))
                    $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('All prices copied to this account.',array(),'message'));
                else
                    $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('Error in coping prices to this account.',array(),'message'));

                return $this->redirect($this->generateUrl('hello_di_pricing_distributor_item_index', array('id' => $id)));
            }
        }

        return $this->render('HelloDiPricingBundle:distributorPricing:copyPrices.html.twig', array(
                'account' => $distributor->getAccount(),
                'form' => $form->createView()
            ));
    }

    private function copyPrices(Distributor $from, Distributor $to)
    {
        try {
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();

            $em->createQueryBuilder()
                ->delete('HelloDiPricingBundle:Price', 'price')
                ->where('price.account = :acc_to')->setParameter('acc_to', $to->getAccount())
                ->getQuery()->execute();

            foreach($from->getAccount()->getPrices() as $priceFrom) {
                /** @var Price $priceFrom */
                $priceTo = new Price();
                $priceTo->setItem($priceFrom->getItem());
                $priceTo->setAccount($to->getAccount());
                $to->getAccount()->addPrice($priceTo);
                $priceTo->setPrice($priceFrom->getPrice());
                $priceTo->setTax($priceFrom->getTax());

                $em->persist($priceTo);
            }
            $em->flush();

            return true;
        }
        catch(\Exception $ex) {
            return false;
        }
    }
}