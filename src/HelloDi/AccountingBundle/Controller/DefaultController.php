<?php

namespace HelloDi\AccountingBundle\Controller;

use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\AccountingBundle\Entity\Transaction;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function checkBalanceAndCreditLimit(Account $account = null)
    {
        if (!$account) $account = $this->getUser()->getAccount();

        return ($account->getAccBalance()+$account->getCreditLimit()) > 0;
    }

    public function insertTransaction(Transaction $transaction)
    {

    }
}
