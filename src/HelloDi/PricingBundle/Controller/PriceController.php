<?php

namespace HelloDi\PricingBundle\Controller;

use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\PricingBundle\Entity\Price;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PriceController extends Controller
{
    public function pricingAction()
    {
        $em = $this->getDoctrine()->getManager();

        $account = $this->getUser()->getAccount();

        $items = $em->createQueryBuilder()
            ->select('item.id','item.itemCode','item.itemName','item.itemFaceValue','priceDist.price')
            ->from('HelloDiDiDistributorsBundle:Item','item')
            ->innerJoin('item.Prices','priceProv')
            ->innerJoin('priceProv.Account','accProv')
            ->where('accProv.type = :accType')->setParameter('accType',Account::PROVIDER)
            ->leftJoin('item.Prices','priceDist','WITH','priceDist.Account = :accDist')
            ->setParameter('accDist',$account)
            ->getQuery()->getArrayResult();

        return $this->render("HelloDiPricingBundle:Price:pricing.html.twig",array(
            'json_data'=>json_encode($items)
        ));
    }

    public function updatePriceAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $account = $this->getUser()->getAccount();

        $itemId = $request->get('id',0);
        if($itemId == 0) return new Response('false');

        $priceAmount = $request->get('price','');
        if($priceAmount!= "" && (!is_numeric($priceAmount) || $priceAmount<0)) return new Response('false');

        $item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->find($itemId);
        if(!$item) return new Response('false');

        /** @var Price $priceDist */
        $priceDist = $em->createQueryBuilder()
            ->select('price')
            ->from('HelloDiPricingBundle:Price','price')
            ->where('price.Item = :item')->setParameter('item',$item)
            ->andWhere('price.Account = :accDist')->setParameter('accDist',$account)
            ->getQuery()->getOneOrNullResult();

        if($priceDist)                                  //price exist
        {
            if($priceAmount == '')                      //remove
                $em->remove($priceDist);
            else
                $priceDist->setPrice($priceAmount);     //update

            $em->flush();
        }
        elseif($priceAmount != '')                      //amount not empty and price doesn't exist
        {
            $priceProv = $em->createQueryBuilder()      //check for exist price for provider
                ->select('price')
                ->from('HelloDiPricingBundle:Price','price')
                ->where('price.Item = :item')->setParameter('item',$item)
                ->innerJoin('price.Account','accProv')
                ->andWhere('accProv.type = :accType')->setParameter('accType',Account::PROVIDER)
                ->getQuery()->getOneOrNullResult();

            if($priceProv)                              //create
            {
                $priceDist = new Price();
                $priceDist->setAccount($account);
                $priceDist->setPrice($priceAmount);
                $priceDist->setItem($item);
                $priceDist->setIsFavourite(false);
                $em->persist($priceDist);
                $em->flush();
            }
            else                                        //error - can't create
                return new Response('false');
        }

        return new Response('true');
    }
}