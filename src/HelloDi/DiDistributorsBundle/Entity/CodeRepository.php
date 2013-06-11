<?php

namespace HelloDi\DiDistributorsBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CodeRepository extends EntityRepository
{
    public function countAvailableCodeByItem($item)
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('count(code)')
            ->from('HelloDiDiDistributorsBundle:Code','code')
            ->where('code.status = 1')
            ->andWhere('code.Item = :iitem')
            ->setParameter('iitem',$item);
        $query = $qb->getQuery();

        return $query->getSingleScalarResult();
    }

    public function findOldestAvailableCodeByItem($item)
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('code')
            ->from('HelloDiDiDistributorsBundle:Code','code')
            ->where('code.status = 1')
            ->andWhere('code.Item = :iitem')
            ->setParameter('iitem',$item)
            ->setMaxResults(1);
        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }
}
