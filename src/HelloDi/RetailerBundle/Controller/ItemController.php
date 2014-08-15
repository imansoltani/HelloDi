<?php
namespace HelloDi\RetailerBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ItemController extends Controller
{
    public function indexAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $retailer = $em->getRepository('HelloDiRetailerBundle:Retailer')->findOneBy(array('account'=>$this->getUser()->getAccount()));

        $prices = $em->getRepository('HelloDiPricingBundle:Price')->findBy(array('account'=>$retailer->getAccount()));

        return $this->render('HelloDiRetailerBundle:item:index.html.twig', array(
                'prices' => $prices,
                'retailer' => $retailer
            ));
    }

    public function switchFavoriteAction()
    {
        $price_id = $this->getRequest()->get('price_id', 0);

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $price = $em->getRepository('HelloDiPricingBundle:Price')->findOneBy(array('id'=>$price_id, 'account'=>$this->getUser()->getAccount()));
        if(!$price)
            throw $this->createNotFoundException($this->get('translator')->trans('Unable_to_find_%object%',array('object'=>'item'),'message'));

        $price->setIsFavourite(!$price->getIsFavourite());
        $em->flush();

        return new Response($price->getIsFavourite()? '1' : '0');
    }
}
