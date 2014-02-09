<?php

namespace HelloDi\DiDistributorsBundle\Listener;

use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\DiDistributorsBundle\Entity\Price;

class BalanceChecker
{
    private  $session;
    private $translator;
    function  __construct($sesion,$translator)
    {
        $this->session=$sesion;
        $this->translator=$translator;
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
            $this->session->getFlashBag()->add('error',
                $this->translator->trans('balance_is_not_enough',array(),'message'));
            return false;
        }
    }



   public function isMoreThanCreditLimit(Account $account,$value)
   {
       if(($account->getAccBalance()-$value)>=$account->getAccCreditLimit())
           return true;
       else{

           $this->session->getFlashBag()->add('error',
               $this->translator->trans('balance_should_more_than_credit_limit',array(),'message'));
                   return false;
       }


   }

    public function isAccCreditLimitPlus(Account $account,$value)
    {
        if(($account->getAccCreditLimit()-$value)>=0)
            return true;
        else
        {
            $this->session->getFlashBag()->add('error',
                $this->translator->trans('credit_Limit_should_be_positive',array(),'message'));
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
