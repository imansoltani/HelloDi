<?php

namespace HelloDi\PricingBundle\Controller;

use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ModelController extends Controller
{
    public function indexModelAction()
    {

    }

    public function NewDistributorModelAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Query $prices */
        $prices = $em->createQueryBuilder()
            ->select('item.id','item.itemCode','item.itemName','price.price')
            ->from('HelloDiPricingBundle:Price','price')
            ->innerJoin('price.Item','item')
            ->innerJoin('price.Account','account')
            ->where('account.id = :acc_id')->setParameter('acc_id',2)
//            ->where('price.Account = :account')->setParameter('account',$this->getUser()->getAccount())
            ->getQuery();

        if($request->isMethod('post'))
        {
            $json = json_decode($request->get('json','[]'));

            if(is_array($json) && count($json)>0)
            {
                return new Response('OK. '.count($json));
            }
            return new Response('Error');

        }

        return $this->render("HelloDiPricingBundle:Model:new.html.twig",array(
            'json_data'=>json_encode($prices->getResult())
        ));

    }

    public function EditDistributorModelAction()
    {
        $em = $this->getDoctrine()->getManager();

        $prices = $em->createQueryBuilder()
            ->select('price.id','item.itemCode','item.itemName','price.price')
            ->from('HelloDiPricingBundle:Price','price')
            ->innerJoin('price.Item','item')
            ->innerJoin('price.Account','account')
            ->where('account.id = :acc_id')->setParameter('acc_id',2)
            ->getQuery()->getResult();

        $result = json_encode($prices);

        return $this->render("HelloDiPricingBundle:ManageItems:Retailers.html.twig",array(
            'json_data'=>$result
        ));
    }
}