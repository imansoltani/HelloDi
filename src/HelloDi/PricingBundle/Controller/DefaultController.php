<?php

namespace HelloDi\PricingBundle\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\CoreBundle\Entity\Item;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager|object
     */
    private $em;

    /**
     * constructor
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function HistoryItemInAccount(Account $account, Item $item, \DateTime $from = null, \DateTime $to = null)
    {

    }

    public function HistoryItemsInAccount(Account $account, \DateTime $from = null, \DateTime $to = null)
    {

    }
}
