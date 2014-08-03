<?php
namespace HelloDi\DistributorBundle\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\AccountingBundle\Entity\Account;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ItemController extends Controller
{
    public function indexAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var Account $account */
        $account = $this->getUser()->getAccount();

        $distributor = $em->getRepository('HelloDiDistributorBundle:Distributor')->findOneBy(array('account'=>$account));

        return $this->render('HelloDiDistributorBundle:item:index.html.twig', array(
                'prices' => $account->getPrices(),
                'distributor' => $distributor
            ));
    }
}
