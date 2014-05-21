<?php
namespace HelloDi\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class ProviderRepository
 * @package HelloDi\CoreBundle\Entity
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