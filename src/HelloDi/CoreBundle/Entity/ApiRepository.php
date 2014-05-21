<?php
namespace HelloDi\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class ApiRepository
 * @package HelloDi\CoreBundle\Entity
 */
class ApiRepository extends EntityRepository
{
    /**
     * @param $id
     * @return Api|null
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