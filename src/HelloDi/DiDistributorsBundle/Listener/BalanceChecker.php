<?php

namespace HelloDi\DiDistributorsBundle\Listener;

use HelloDi\DiDistributorsBundle\Entity\Account;
use HelloDi\DiDistributorsBundle\Entity\Price;

class BalanceChecker
{
    public function isBalanceEnough(Account $account, Price $price, $count = 1)
    {

        if ($this->currenciesMatch($account->getAccCurrency(), $price->getPriceCurrency())) {
            return  ($account->getAccBalance() + $account->getAccCreditLimit() >= ($price->getPrice()*$count));
        } else {
            throw new \Exception('The Currencies are NOT matched!');
        }
    }

    public function isBalanceEnoughForMoney(Account $account,$value)
    {

        if ($account->getAccBalance() >= $value  ) {
            return true;
        } else {
            throw new \Exception('The Currencies are NOT matched!');
        }
    }


   public function isMoreThanCreditLimit(Account $account,$value)
   {
       if(($account->getAccBalance()-$value)>=$account->getAccCreditLimit())
           return true;
       throw new \Exception('موجودی از اعتبار کمتر می شود');

   }

    public function isAccCreditLimitPlus(Account $account,$value)
    {
        if(($account->getAccCreditLimit()-$value)>=0)
            return true;
        throw new \Exception('اعتبار نمی تواند منفی باشد');

    }


    private function currenciesMatch($accountCurr, $priceCurr)
    {
        if (strcasecmp($accountCurr, $priceCurr) == 0) {
            return true;
        }
        return false;
    }
}
