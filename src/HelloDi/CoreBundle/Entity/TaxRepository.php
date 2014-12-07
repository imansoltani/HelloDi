<?php
namespace HelloDi\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class TaxRepository
 * @package HelloDi\CoreBundle\Entity
 */
class TaxRepository extends EntityRepository
{
    /**
     * @param string $country
     * @return int
     * @throws \Exception
     */
    public function getCurrentVatOfCountry($country)
    {
        try {
            return $this->createQueryBuilder('this')
                ->select('this.vat')
                ->where('this.country = :country')->setParameter('country', $country)
                ->andWhere('this.dateEnd is null')
                ->getQuery()->getSingleScalarResult();
        }catch (\Exception $e){
            throw new \Exception('Vat for '.$country.' not set.');
        }
    }
}