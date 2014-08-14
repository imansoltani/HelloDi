<?php
namespace HelloDi\RetailerBundle\Entity;

use Doctrine\ORM\EntityRepository;
use HelloDi\AccountingBundle\Entity\Account;

/**
 * Class RetailerRepository
 * @package HelloDi\RetailerBundle\Entity
 */
class RetailerRepository extends EntityRepository
{
    /**
     * @param $id
     * @return Retailer|null
     */
    public function findByAccountId($id)
    {
        return $this->createQueryBuilder('this')
            ->innerJoin('this.account', 'account')
            ->where('account.id = :id')->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $id
     * @param Account $distributorAccount
     * @return Retailer|null
     */
    public function findByAccountIdAndDistributorAccount($id, Account $distributorAccount)
    {
        return $this->createQueryBuilder('this')
            ->innerJoin('this.account', 'account')
            ->where('account.id = :id')->setParameter('id', $id)
            ->innerJoin('this.distributor', 'distributor')
            ->andWhere('distributor.account = :dist_account')->setParameter('dist_account', $distributorAccount)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $id
     * @return array
     */
    public function findByDistributorId($id)
    {
        return $this->createQueryBuilder('this')
            ->innerJoin('this.distributor', 'distributor')
            ->where('distributor.id = :id')->setParameter('id', $id)
            ->getQuery()->getResult();
    }

    /**
     * @param $id
     * @return array
     */
    public function findByDistributorAccountId($id)
    {
        return $this->createQueryBuilder('this')
            ->innerJoin('this.distributor', 'distributor')
            ->innerJoin('distributor.account', 'account')
            ->where('account.id = :id')->setParameter('id', $id)
            ->getQuery()->getResult();
    }
}