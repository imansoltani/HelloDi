<?php
namespace HelloDi\AccountingBundle\Entity;

use Doctrine\ORM\EntityRepository;
use HelloDi\CoreBundle\Entity\Api;
use HelloDi\AggregatorBundle\Entity\Provider;
use HelloDi\DistributorBundle\Entity\Distributor;
use HelloDi\RetailerBundle\Entity\Retailer;

/**
 * Class AccountRepository
 * @package HelloDi\AccountingBundle\Entity
 */
class AccountRepository extends EntityRepository
{
    /**
     * @param Account $account
     * @return Api|Distributor|Provider|Retailer|null
     */
    public function getAccountType(Account $account)
    {
        if(!$account)
            return null;

        switch ($account->getType()) {
            case Account::API:
                return $this->getEntityManager()->getRepository("HelloDiCoreBundle:Api")
                    ->findOneBy(array("account" => $account));

            case Account::PROVIDER:
                return $this->getEntityManager()->getRepository("HelloDiAggregatorBundle:Provider")
                    ->findOneBy(array("account" => $account));

            case Account::DISTRIBUTOR:
                return $this->getEntityManager()->getRepository("HelloDiDistributorBundle:Distributor")
                    ->findOneBy(array("account" => $account));

            case Account::RETAILER:
                return $this->getEntityManager()->getRepository("HelloDiRetailerBundle:Retailer")
                    ->findOneBy(array("account" => $account));
        }

        return null;
    }
}