<?php
namespace HelloDi\CoreBundle\Listener;

use Doctrine\ORM\EntityManager;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\AggregatorBundle\Entity\Provider;
use HelloDi\CoreBundle\Entity\Api;
use HelloDi\DistributorBundle\Entity\Distributor;
use HelloDi\RetailerBundle\Entity\Retailer;

/**
 * Class AccountType
 * @package HelloDi\CoreBundle\Listener
 */
class AccountTypeFinder {

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
     * @return Api|Distributor|Provider|Retailer|null
     */
    public function getType(Account $account)
    {
        if(!$account)
            return null;

        switch ($account->getType()) {
            case Account::API:
                return $this->em->getRepository("HelloDiCoreBundle:Api")
                    ->findOneBy(array("account" => $account));

            case Account::PROVIDER:
                return $this->em->getRepository("HelloDiAggregatorBundle:Provider")
                    ->findOneBy(array("account" => $account));

            case Account::DISTRIBUTOR:
                return $this->em->getRepository("HelloDiDistributorBundle:Distributor")
                    ->findOneBy(array("account" => $account));

            case Account::RETAILER:
                return $this->em->getRepository("HelloDiRetailerBundle:Retailer")
                    ->findOneBy(array("account" => $account));
        }

        return null;
    }

    /**
     * @param Account $account
     * @return string
     */
    public function getCurrency(Account $account)
    {
        $accountType = $this->getType($account);

        if(!$accountType)
            return "";
        else
            return $account->getType() == Account::RETAILER
                ? $accountType->getDistributor()->getCurrency()
                : $accountType->getCurrency();
    }

    /**
     * @param int $id
     * @return string
     */
    public function getCurrencyById($id)
    {
        $account = $this->em->getRepository('HelloDiAccountingBundle:Account')->find($id);
        if(!$account)
            return "";

        $accountType = $this->getType($account);

        if(!$accountType)
            return "";
        else
            return $account->getType() == Account::RETAILER
                ? $accountType->getDistributor()->getCurrency()
                : $accountType->getCurrency();
    }
}