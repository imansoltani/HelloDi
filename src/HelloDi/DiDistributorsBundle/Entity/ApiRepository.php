<?php
namespace HelloDi\DiDistributorsBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class ApiRepository
 * @package HelloDi\DiDistributorsBundle\Entity
 */
class ApiRepository extends EntityRepository
{
    /**
     * @param $id
     * @return Api|null
     */
    public function getByAccountId($id)
    {
        return $this->createQueryBuilder('this')
            ->innerJoin('this.account', 'account')
            ->where('account.id = :id')->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }
}