<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Fils du Soleil
 * Date: 28.06.13
 * Time: 19:59
 * To change this template use File | Settings | File Templates.
 */

namespace HelloDi\DiDistributorsBundle\Entity;

use Doctrine\ORM\EntityRepository;

class OgonePaymentRepository extends EntityRepository
{
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