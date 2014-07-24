<?php
namespace HelloDi\RetailerBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SettingController extends Controller
{
    public function profileAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $retailer = $em->getRepository('HelloDiRetailerBundle:Retailer')->findOneBy(array(
                'account' => $this->getUser()->getAccount()
            ));

        return $this->render('HelloDiRetailerBundle:setting:profile.html.twig', array(
                'retailer' => $retailer
            ));
    }
}
