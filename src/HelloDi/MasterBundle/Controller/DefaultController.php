<?php
namespace HelloDi\MasterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function dashboardAction()
    {
        $em = $this->getDoctrine()->getManager();
        $notifications = $em->getRepository('HelloDiCoreBundle:Notification')->findBy(array('account' => null));

        return $this->render('HelloDiMasterBundle::dashboard.html.twig', array(
            'Notifications' => $notifications
        ));
    }
}