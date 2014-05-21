<?php
namespace HelloDi\DistributorBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class DistributorRepository
 * @package HelloDi\DistributorBundle\Entity
 */
class DistributorRepository extends EntityRepository
{
    /**
     * @param $id
     * @return Distributor|null
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