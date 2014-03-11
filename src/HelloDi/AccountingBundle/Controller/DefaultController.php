<?php

namespace HelloDi\AccountingBundle\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\AccountingBundle\Container\TransactionContainer;
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
    private function createTransaction($amount, Account $account, $description, $fees = 0.0)
    {
        $transaction = new Transaction();
        $transaction->setTranAmount($amount);
        $transaction->setAccount($account);
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
        $this->isAmountAcceptable($amount);

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
        $this->isAmountAcceptable($amount);

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
        $this->isAmountAcceptable($amount);

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
     * @param TransactionContainer[] $array
     * @return bool
     */
    public function processTransaction($array)
    {
        usort($array, function (TransactionContainer $a, TransactionContainer $b) {
            if ($a->getAccount()->getId() == $b->getAccount()->getId()) {
                return 0;
            }
            return ($a->getAccount()->getId() < $b->getAccount()->getId()) ? -1 : 1;
        });

        $this->em->beginTransaction();

        /** @var Account $lastAccount */
        $lastAccount = null;
        $sum = 0;

        foreach($array as $transactionContainer)
        {
            /** var TransactionContainer $transactionContainer */
            if(!$lastAccount && $lastAccount->getId() != $transactionContainer->getAccount()->getId())
            {
                if($sum<0 && !$this->checkAvailableBalance(-$sum,$lastAccount))
                {
                    $this->em->flush();
                    $this->em->rollback();
                    return false;
                }
                $this->createTransaction(
                    $transactionContainer->getAmount(),
                    $transactionContainer->getAccount(),
                    $transactionContainer->getDescription(),
                    $transactionContainer->getFees()
                );
                $lastAccount = $transactionContainer->getAccount();
                $sum = 0;
            }
            $sum += $transactionContainer->getAmount();
        }
        $this->em->flush();
        $this->em->commit();
        return true;
    }

    private function isAmountAcceptable($amount)
    {
        if(!$amount || $amount <= 0)
            throw new \Exception("Amount must be larger than zero.");
    }
}
