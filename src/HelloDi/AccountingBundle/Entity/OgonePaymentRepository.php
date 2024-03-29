<?php
namespace HelloDi\AccountingBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class OgonePaymentRepository
 * @package HelloDi\AccountingBundle\Entity
 */
class OgonePaymentRepository extends EntityRepository
{
    /**
     * @param string $orderRef
     * @return OgonePayment|null
     */
    public function findOneByOrderReference($orderRef)
    {
        /**
         * $orderRef is composed by ymdHi + id
         * ymdHi (i.e. the 1st of January 2013 20h59: 1301012059)
         * id starts from the 11th character
         */
        $id = substr($orderRef, 10);
        $payment = $this->find($id);
        if (null === $payment || strval($payment->getOrderReference()) !== strval($orderRef))
        {
            return null;
        }

        return $payment;
    }
}