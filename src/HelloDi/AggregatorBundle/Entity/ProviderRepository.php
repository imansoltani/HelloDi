<?php
namespace HelloDi\AggregatorBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class ProviderRepository
 * @package HelloDi\AggregatorBundle\Entity
 */
class ProviderRepository extends EntityRepository
{
    /**
     * @param $id
     * @return Provider|null
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