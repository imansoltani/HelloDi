<?php

namespace HelloDi\PricingBundle\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\PricingBundle\Entity\Price;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PriceController extends Controller
{
    public function pricingAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $account = $em->getRepository('HelloDiDistributorBundle:Distributor')->findOneBy(array())->getAccount();

        $items = $em->createQueryBuilder()
            ->select('item.id','item.code','item.name','item.faceValue','priceDist.price')
            ->from('HelloDiCoreBundle:Item','item')
            ->innerJoin('item.prices','priceProv')
            ->innerJoin('priceProv.account','accProv')
            ->where('accProv.type = :accType')->setParameter('accType',Account::PROVIDER)
            ->leftJoin('item.prices','priceDist','WITH','priceDist.account = :accDist')->setParameter('accDist',$account)
            ->getQuery()->getArrayResult();

        return $this->render("HelloDiPricingBundle:Price:pricing.html.twig",array(
            'json_data'=>json_encode($items)
        ));
    }

    public function updatePriceAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $account = $em->getRepository('HelloDiDistributorBundle:Distributor')->findOneBy(array())->getAccount();

        $itemId = $request->get('id',0);
        if($itemId == 0) return new Response('0-Item ID is incorrect.');

        $priceAmount = $request->get('price','');
        if($priceAmount!= "" && (!is_numeric($priceAmount) || $priceAmount<0)) return new Response('0-Price is incorrect.');

        $item = $em->getRepository('HelloDiCoreBundle:Item')->find($itemId);
        if(!$item) return new Response("0-Couldn't find the Item.");

        $info = "";

        /** @var Price $priceDist */
        $priceDist = $em->createQueryBuilder()
            ->select('price')
            ->from('HelloDiPricingBundle:Price','price')
            ->where('price.item = :item')->setParameter('item',$item)
            ->andWhere('price.account = :accDist')->setParameter('accDist',$account)
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
                $priceDist->setAccount($account);
                $priceDist->setPrice($priceAmount);
                $priceDist->setItem($item);
                $priceDist->setIsFavourite(false);
                $em->persist($priceDist);
                $info = 'created';
            }
            else                                        //error - can't create - because any provider has not this item
                return new Response("0-No provider has this item.");
        }

        $em->flush();
        return new Response('1-'.$info);
    }
}
