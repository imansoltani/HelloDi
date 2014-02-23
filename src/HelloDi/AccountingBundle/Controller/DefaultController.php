<?php

namespace HelloDi\AccountingBundle\Controller;

use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\AccountingBundle\Entity\CreditLimit;
use HelloDi\AccountingBundle\Entity\Transaction;
use HelloDi\AccountingBundle\Entity\Transfer;
use HelloDi\DiDistributorsBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class DefaultController
 * @package HelloDi\AccountingBundle\Controller
 */
class DefaultController extends Controller
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager|object
     */
    private $em;

    /**
     *
     */
    public function __construct()
    {
        $this->em = $this->getDoctrine()->getManager();
    }

    /**
     * if account not set, account of current user selected.
     *
     * @param float $amount
     * @param Account $account
     * @return bool
     */
    private function checkAvailableBalance($amount, Account $account)
    {
        return ($account->getAccBalance() + $account->getAccCreditLimit() - $this->get("reserve_container")->get($account)) > $amount;
    }

    /**
     * @param float $amount
     * @param Account $account
     * @param string $description
     * @param float $fees
     * @return Transaction
     */
    private function createTransaction($amount, Account $account, $description = "", $fees = 0.0)
    {
        $transaction = new Transaction();
        $transaction->setTranAmount($amount);
        $transaction->setAccount($account);
        $transaction->setTranDate(new \DateTime());
        $transaction->setTranDescription($description);
        $transaction->setTranFees($fees);
        $this->em->persist($transaction);
        return $transaction;
    }

    public function processTransfer($amount, User $user, Account $destination, $descriptionForOrigin = "", $descriptionForDestination = "")
    {
        if(!$amount || $amount <= 0)
            throw new \Exception("Amount must be larger than zero.");

        $transfer = new Transfer();

        if($user->getAccount())
        {
            $transfer->setOriginTransaction($this->createTransaction(-$amount,$user->getAccount(),$descriptionForOrigin));
        }

        $transfer->setOriginTransaction($this->createTransaction($amount,$destination,$descriptionForDestination));

        $transfer->setUser($user);
        $this->em->persist($transfer);
        $this->em->flush();
        return $transfer;
    }

    /**
     * @param int $amount
     * @param User $user
     * @param Account $account
     * @throws \Exception
     * @return CreditLimit
     */
    public function newCreditLimit($amount, User $user, Account $account)
    {
        if(!$amount || $amount <= 0)
            throw new \Exception("Amount must be larger than zero.");

        $creditLimit = new CreditLimit();

        if($userAccount = $user->getAccount())
        {
            $oldCreditLimit = $userAccount->getAccCreditLimit();
            if($amount > $oldCreditLimit)
                $creditLimit->setTransaction($this->createTransaction($oldCreditLimit - $amount,$account,"credit limit"));
        }

        $creditLimit->setUser($user);
        $creditLimit->setAmount($amount);
        $creditLimit->setAccount($account);
        $this->em->persist($creditLimit);
        $this->em->flush();
        return $creditLimit;
    }

    /**
     * @param $amount
     * @param Account $account
     * @return bool
     * @throws \Exception
     */
    public function freezeAmount($amount, Account $account)
    {
        if(!$amount || $amount <= 0)
            throw new \Exception("Amount must be larger than zero.");

        if ($this->checkAvailableBalance($amount,$account))
        {
            $this->get("reserve_container")->increase($account,$amount);
            return true;
        }
        return false;
    }

    /**
     * @param $amount
     * @param Account $account
     * @return bool
     * @throws \Exception
     */
    public function unfreezeAmount($amount, Account $account)
    {
        if(!$amount || $amount <= 0)
            throw new \Exception("Amount must be larger than zero.");

        $this->get("reserve_container")->decrease($account,$amount);
        return true;
    }
}
