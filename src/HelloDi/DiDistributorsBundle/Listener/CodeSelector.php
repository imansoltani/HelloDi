<?php

namespace HelloDi\DiDistributorsBundle\Listener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use HelloDi\DiDistributorsBundle\Entity\Account;
use HelloDi\DiDistributorsBundle\Entity\Item;
use HelloDi\DiDistributorsBundle\Entity\Price;

class CodeSelector
{
    private $doctrine;
    private $balancecheker;

    public function __construct(Registry $doctrine, BalanceChecker $balancecheker)
    {
        $this->doctrine = $doctrine;
        $this->balancecheker = $balancecheker;
    }

    public function lookForAvailableCode(Account $account, Price $price, Item $item, $count = 1)
    {
        if ($this->balancecheker->isBalanceEnough($account, $price, $count))
        {
            $em = $this->doctrine->getManager();

            $countinitem = $em->getRepository('HelloDiDiDistributorsBundle:Code')->countAvailableCodeByItem($item);

            if($count > $countinitem)
            {
                throw new \Exception("Code not exist in this item.",1);
            }

            $codes = array();
            for($i=1;$i<=$count;$i++)
            {
                $code = $em->getRepository('HelloDiDiDistributorsBundle:Code')->findOldestAvailableCodeByItem($item);
                $code->setStatus(0);
                $codes[] = $code;
            }
            return $codes;
        }
        throw new \Exception("Balance is not enough.",2);
    }
}
