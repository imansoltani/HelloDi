<?php
namespace HelloDi\RetailerBundle\Entity;

use Doctrine\ORM\EntityRepository;

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
     * @param $dist_id
     * @return Retailer|null
     */
    public function findByAccountIdAndDistributorAccountId($id, $dist_id)
    {
        return $this->createQueryBuilder('this')
            ->innerJoin('this.account', 'account')
            ->where('account.id = :id')->setParameter('id', $id)
            ->innerJoin('this.distributor', 'distributor')
            ->innerJoin('distributor.account', 'distAccount')
            ->andWhere('distAccount.id = :dist_id')->setParameter('dist_id', $dist_id)
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