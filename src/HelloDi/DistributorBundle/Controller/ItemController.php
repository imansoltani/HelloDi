<?php
namespace HelloDi\DistributorBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ItemController extends Controller
{
    public function indexAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $account = $this->getUser()->getAccount();

        $distributor = $em->getRepository('HelloDiDistributorBundle:Distributor')->findOneBy(array('account'=>$account));

        $prices = $em->getRepository('HelloDiPricingBundle:Price')->findBy(array('account'=>$account));

        return $this->render('HelloDiDistributorBundle:item:index.html.twig', array(
                'prices' => $prices,
                'distributor' => $distributor
            ));
    }
}
