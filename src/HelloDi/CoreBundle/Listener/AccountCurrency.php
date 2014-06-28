<?php
namespace HelloDi\CoreBundle\Listener;

use Doctrine\ORM\EntityManager;
use HelloDi\AccountingBundle\Entity\Account;

/**
 * Class AccountCurrency
 * @package HelloDi\CoreBundle\Listener
 */
class AccountCurrency {

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param Account $account
     * @return string
     */
    public function get(Account $account)
    {
        $accountType = $this->em->getRepository('HelloDiAccountingBundle:Account')->getAccountType($account);

        if(!$accountType)
            return "";
        else
            return $account->getType() == Account::RETAILER
                ? $accountType->getDistributor()->getCurrency()
                : $accountType->getCurrency();
    }
}