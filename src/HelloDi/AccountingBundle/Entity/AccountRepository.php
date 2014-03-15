<?php
namespace HelloDi\AccountingBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class AccountRepository
 * @package HelloDi\AccountingBundle\Entity
 */
class AccountRepository extends EntityRepository
{
    /**
     * @param Account $account
     * @return \HelloDi\DiDistributorsBundle\Entity\Api|\HelloDi\DiDistributorsBundle\Entity\Distributor|\HelloDi\DiDistributorsBundle\Entity\Provider|\HelloDi\DiDistributorsBundle\Entity\Retailer|null
     */
    public function getAccountType(Account $account)
    {
        switch ($account->getType())
        {
            case Account::API:
                return $this->getEntityManager()->getRepository("HelloDiDiDistributorsBundle:Api")
                    ->findOneBy(array("Account"=>$account));

            case Account::DISTRIBUTOR:
                return $this->getEntityManager()->getRepository("HelloDiDiDistributorsBundle:Distributor")
                    ->findOneBy(array("Account"=>$account));

            case Account::PROVIDER:
                return $this->getEntityManager()->getRepository("HelloDiDiDistributorsBundle:Provider")
                    ->findOneBy(array("Account"=>$account));

            case Account::RETAILER:
                return $this->getEntityManager()->getRepository("HelloDiDiDistributorsBundle:Retailer")
                    ->findOneBy(array("Account"=>$account));
        }

        return null;
    }
}