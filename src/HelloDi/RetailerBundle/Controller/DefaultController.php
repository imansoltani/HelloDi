<?php
namespace HelloDi\RetailerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function dashboardAction()
    {
        $em = $this->getDoctrine()->getManager();

        $Notifications = $em->getRepository('HelloDiDiDistributorsBundle:Notification')->findBy(array(
            'Account' => $this->getUser()->getAccount()
        ));

        return $this->render('HelloDiRetailerBundle::dashboard.html.twig', array(
            'Notifications' => $Notifications
        ));
    }
}
