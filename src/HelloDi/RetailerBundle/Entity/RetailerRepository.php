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
}