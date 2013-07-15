<?php

namespace HelloDi\DiDistributorsBundle\Listener;

use Doctrine\ORM\EntityManager;
use HelloDi\DiDistributorsBundle\Entity\Account;
use HelloDi\DiDistributorsBundle\Entity\Item;
use HelloDi\DiDistributorsBundle\Entity\Price;

class CodeSelector
{
    private $em;
    private $balancecheker;

    public function __construct(EntityManager $entityManager, BalanceChecker $balancecheker)
    {
        $this->em = $entityManager;
        $this->balancecheker = $balancecheker;
    }

    public function lookForAvailableCode(Account $account, Price $price, Item $item, $count = 1)
    {

        if ($this->balancecheker->isBalanceEnough($account, $price, $count))
        {
            $em = $this->em;

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
                $em->flush();
                $codes[] = $code;
            }
            return $codes;
        }
        throw new \Exception("Balance is not enough.",2);
    }
}
