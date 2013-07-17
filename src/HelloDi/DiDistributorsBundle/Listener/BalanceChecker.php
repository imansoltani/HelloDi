<?php

namespace HelloDi\DiDistributorsBundle\Listener;

use HelloDi\DiDistributorsBundle\Entity\Account;
use HelloDi\DiDistributorsBundle\Entity\Price;

class BalanceChecker
{
    private  $session;
    function  __construct($sesion)
    {
        $this->session=$sesion;
    }
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
            $this->session->getFlashBag()->add('error','this operation done with error!');
        }
    }



   public function isMoreThanCreditLimit(Account $account,$value)
   {
       if(($account->getAccBalance()-$value)>=$account->getAccCreditLimit())
           return true;
       $this->session->getFlashBag()->add('error','this operation done with error!');

   }

    public function isAccCreditLimitPlus(Account $account,$value)
    {
        if(($account->getAccCreditLimit()-$value)>=0)
            return true;
        $this->session->getFlashBag()->add('error','this operation done with error!');

    }


    private function currenciesMatch($accountCurr, $priceCurr)
    {
        if (strcasecmp($accountCurr, $priceCurr) == 0) {
            return true;
        }
        return false;
    }
}
