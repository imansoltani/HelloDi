<?php
namespace HelloDi\RetailerBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TopUpController extends Controller
{
//    public function IMTUAction()
//    {
//        /** @var EntityManager $em */
//        $em = $this->getDoctrine()->getManager();
//
//        $Account = $this->getUser()->getAccount();
//
//        $qb = $em->createQueryBuilder()
//            ->select('p')
//            ->from('HelloDiDiDistributorsBundle:Price','p')
//            ->innerJoin('p.Item','i')
//            ->where('i.itemType = :type')->setParameter('type','dmtu')
//            ->andWhere('p.Account = :account')->setParameter('account',$Account)
//            ->andWhere('p.priceStatus = 1');
//
//        $prices=$qb->getQuery()->getResult();
//
//        return $this->render('HelloDiDiDistributorsBundle:Retailers:ShopDmtu.html.twig',array(
//                'Prices'=>$prices,
//                'Account'=>$Account,
//            ));
//    }
}
