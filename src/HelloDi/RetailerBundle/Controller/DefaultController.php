<?php
namespace HelloDi\RetailerBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function dashboardAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $notifications = $em->getRepository('HelloDiCoreBundle:Notification')->findBy(array(
            'account' => $this->getUser()->getAccount()
        ));

        return $this->render('HelloDiRetailerBundle::dashboard.html.twig', array(
                'notifications' => $notifications
            ));
    }
}
