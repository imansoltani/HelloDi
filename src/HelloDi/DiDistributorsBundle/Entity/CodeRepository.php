<?php

namespace HelloDi\DiDistributorsBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class CodeRepository
 * @package HelloDi\DiDistributorsBundle\Entity
 */
class CodeRepository extends EntityRepository
{
    /**
     * @param Item $item
     * @return integer
     */
    public function countAvailableCodeByItem(Item $item)
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('count(code)')
            ->from('HelloDiDiDistributorsBundle:Code', 'code')
            ->where('code.status = 1')
            ->andWhere('code.Item = :item')
            ->setParameter('item', $item);
        $query = $qb->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * @param Item $item
     * @return Code
     */
    public function findOldestAvailableCodeByItem(Item $item)
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('code')
            ->from('HelloDiDiDistributorsBundle:Code', 'code')
            ->where('code.status = 1')
            ->andWhere('code.Item = :item')
            ->setParameter('item', $item)
            ->setMaxResults(1);
        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }
}
