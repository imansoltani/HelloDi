<?php

namespace HelloDi\AccountingBundle\Controller;

use Doctrine\ORM\EntityManager;
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
     * constructor
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
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
        return ($account->getAccBalance() + $account->getAccCreditLimit() - $account->getReserve()) >= $amount;
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

    /**
     * @param float $amount
     * @param User $user
     * @param Account $destination
     * @param string $descriptionForOrigin
     * @param string $descriptionForDestination
     * @return Transfer
     * @throws \Exception
     */
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
     * @param float $amount
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
     * @param float $amount
     * @param Account $account
     * @param boolean $freeze
     * @throws \Exception
     * @return boolean
     */
    public function reserveAmount($amount, Account $account, $freeze)
    {
        if(!$amount || $amount <= 0)
            throw new \Exception("Amount must be larger than zero.");

        // do freeze
        if($freeze == true)
        {
            if ($this->checkAvailableBalance($amount,$account))
            {
                $account->setReserve($account->getReserve()+$amount);
                return true;
            }
            return false;
        }
        // do unfreeze
        elseif ($freeze == false)
        {
            $account->setReserve($account->getReserve()-$amount);
            return true;
        }
        throw new \Exception("Freeze must be set.");
    }

    /**
     * @param array $array array["account","amount,"description","fees"]
     * @return bool
     */
    public function processTransaction(array $array)
    {
        $groupByAccount = array();
        foreach($array as $row)
        {
            $account_id = $row["account"]->getId();
            if(!isset($groupByAccount[$account_id]))
                $groupByAccount[$account_id] = array(0,$row["account"]); // row[amount,account]
            $groupByAccount[$account_id][0] += $row["amount"];
        }

        foreach($groupByAccount as $row) // row[amount,account]
            if($row[0]<0 && !$this->checkAvailableBalance(-$row[0],$row[1]))
                return false;

        foreach($array as $row)
            $this->createTransaction($row["amount"],$row["account"],$row["description"],$row["fees"]);

        $this->em->flush();

        return true;
    }
}
