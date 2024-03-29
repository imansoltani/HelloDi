<?php
namespace HelloDi\DistributorBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function dashboardAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $notifications = $em->getRepository('HelloDiCoreBundle:Notification')->findBy(array('account' => $this->getUser()->getAccount()));

        return $this->render('HelloDiDistributorBundle::dashboard.html.twig', array(
            'notifications' => $notifications
        ));
    }
}
