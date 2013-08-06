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
            if  ($account->getAccBalance() + $account->getAccCreditLimit() >= ($price->getPrice()*$count))
                return true;
        }

        else
        {
            $this->session->getFlashBag()->add('error','The Currencies are NOT matched!');
            return false;
        }
    }

    public function isBalanceEnoughForMoney(Account $account,$value)
    {

        if ($account->getAccBalance() >= $value  ) {
            return true;
        } else {
            $this->session->getFlashBag()->add('error','balance is not enough!');
            return false;
        }
    }



   public function isMoreThanCreditLimit(Account $account,$value)
   {
       if(($account->getAccBalance()-$value)>=$account->getAccCreditLimit())
           return true;
       else{

           $this->session->getFlashBag()->add('error','balance less than credit limit !');
           return false;
       }


   }

    public function isAccCreditLimitPlus(Account $account,$value)
    {
        if(($account->getAccCreditLimit()-$value)>=0)
            return true;
        else
        {
            $this->session->getFlashBag()->add('error','CreditLimit must be positive!');
            return false;
        }


    }


    private function currenciesMatch($accountCurr, $priceCurr)
    {
        if (strcasecmp($accountCurr, $priceCurr) == 0) {
            return true;
        }
        return false;
    }
}
