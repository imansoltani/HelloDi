<?php
namespace HelloDi\DistributorBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RetailerController extends Controller
{
    public function indexAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $notifications = $em->getRepository('HelloDiCoreBundle:Notification')->findBy(array('account' => $this->getUser()->getAccount()));

        return $this->render('HelloDiDistributorBundle::dashboard.html.twig', array(
            'notifications' => $notifications
        ));
    }
}
