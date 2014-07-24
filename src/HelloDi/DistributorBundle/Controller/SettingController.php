<?php
namespace HelloDi\DistributorBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SettingController extends Controller
{
    public function profileAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $distributor = $em->getRepository('HelloDiDistributorBundle:Distributor')->findOneBy(array(
                'account' => $this->getUser()->getAccount()
            ));

        return $this->render('HelloDiDistributorBundle:setting:Profile.html.twig', array(
                'distributor' => $distributor
            ));
    }
}
