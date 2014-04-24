<?php
namespace HelloDi\AccountingBundle\Entity;

use Doctrine\ORM\EntityRepository;
use HelloDi\CoreBundle\Entity\Api;
use HelloDi\CoreBundle\Entity\Provider;
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
        switch ($account->getType()) {
            case Account::API:
                return $this->getEntityManager()->getRepository("HelloDiDiDistributorsBundle:Api")
                    ->findOneBy(array("Account" => $account));

            case Account::DISTRIBUTOR:
                return $this->getEntityManager()->getRepository("HelloDiDiDistributorsBundle:Distributor")
                    ->findOneBy(array("Account" => $account));

            case Account::PROVIDER:
                return $this->getEntityManager()->getRepository("HelloDiDiDistributorsBundle:Provider")
                    ->findOneBy(array("Account" => $account));

            case Account::RETAILER:
                return $this->getEntityManager()->getRepository("HelloDiDiDistributorsBundle:Retailer")
                    ->findOneBy(array("Account" => $account));
        }

        return null;
    }
}