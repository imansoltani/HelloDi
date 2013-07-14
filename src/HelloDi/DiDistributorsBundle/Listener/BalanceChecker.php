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
       throw new \Exception('از اعتبار کمتر میشود');

   }

    private function currenciesMatch($accountCurr, $priceCurr)
    {
        if (strcasecmp($accountCurr, $priceCurr) == 0) {
            return true;
        }
        return false;
    }
}
