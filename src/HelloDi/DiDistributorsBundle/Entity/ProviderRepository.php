<?php
namespace HelloDi\DiDistributorsBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class ProviderRepository
 * @package HelloDi\DiDistributorsBundle\Entity
 */
class ProviderRepository extends EntityRepository
{
    /**
     * @param $id
     * @return \HelloDi\DiDistributorsBundle\Entity\Provider|null
     */
    public function getByAccountId($id)
    {
        return $this->createQueryBuilder('this')
            ->innerJoin('this.account','account')
            ->where('account.id = :id')->setParameter('id',$id)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }
}