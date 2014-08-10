<?php

namespace HelloDi\AggregatorBundle\Entity;

use Doctrine\ORM\EntityRepository;
use HelloDi\CoreBundle\Entity\Item;

/**
 * Class CodeRepository
 * @package HelloDi\AggregatorBundle\Entity
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
            ->from('HelloDiAggregatorBundle:Code', 'code')
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
            ->from('HelloDiAggregatorBundle:Code', 'code')
            ->where('code.status = 1')
            ->andWhere('code.Item = :item')
            ->setParameter('item', $item)
            ->setMaxResults(1);
        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }
}
