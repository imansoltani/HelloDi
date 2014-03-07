<?php
namespace HelloDi\AccountingBundle\Entity;

use Doctrine\ORM\EntityRepository;

class AccountRepository extends EntityRepository
{
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