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
     * @param Account $account
     * @return bool
     */
    public function checkBalanceAndCreditLimit(Account $account = null)
    {
        if (!$account) $account = $this->getUser()->getAccount();

        return ($account->getAccBalance()+$account->getCreditLimit()) > 0;
    }

    /**
     * if account not set, account of current user selected.
     * if date not set, current date selected.
     *
     * @param int $amount
     * @param Account $account
     * @param \DateTime $date
     * @param string $description
     * @param float $fees
     * @return Transaction
     * @throws \Exception
     */
    public function recordTransaction($amount, Account $account = null, \DateTime $date = null, $description = null, $fees = null)
    {
        if(!$amount)
            throw new \Exception("Amount must be set.");

        if($amount<0 && !$this->checkBalanceAndCreditLimit())
            throw new \Exception("Balance is not enough.");

        if (!$account) $account = $this->getUser()->getAccount();

        if($account->getAccType() == Account::PROVIDER)
            throw new \Exception("Account must be distributor or retailer.");

        if(!$date) $date = new \DateTime();

        $transaction = new Transaction();
        $transaction->setTranAmount($amount);
        $transaction->setAccount($account);
        $transaction->setTranDate($date);
        if($description) $transaction->setTranDescription($description);
        if($fees) $transaction->setTranFees($fees);
        $this->em->persist($transaction);
        return $transaction;
    }

    /**
     * @param int $amount
     * @param User $user
     * @param Account $destination
     * @param \DateTime $date
     * @return Transfer
     * @throws \Exception
     */
    public function recordTransfer($amount, User $user = null, Account $destination , $date = null)
    {
        if(!$amount || $amount <= 0)
            throw new \Exception("Amount must be larger than zero.");

        if(!$user) $user = $this->getUser();

        if(!$destination)
            throw new \Exception("Destination must be set.");

        $transfer = new Transfer();

        if($user->hasRole("ROLE_MASTER_ADMIN") || $user->hasRole("ROLE_MASTER"))
        {
            if($destination->getAccType() != Account::DISTRIBUTOR)
                throw new \Exception("Destination account must be distributor.");

            $transfer->setDestinationTransaction($this->recordTransaction($amount,$destination,$date,"transfer by master"));
        }
        elseif($user->hasRole("ROLE_DISTRIBUTOR_ADMIN") || $user->hasRole("ROLE_DISTRIBUTOR"))
        {
            if($destination->getAccType() != Account::RETAILER)
                throw new \Exception("Destination account must be retailer.");

            $transfer->setOriginTransaction($this->recordTransaction(-$amount,$user->getAccount(),$date,"transfer by dist"));
            $transfer->setDestinationTransaction($this->recordTransaction($amount,$destination,$date,"transfer by dist"));
        }
        else
        {
            throw new \Exception("This user can't record transfer.");
        }

        $transfer->setUser($user);
        $this->em->persist($transfer);
        return $transfer;
    }

    /**
     * @param int $amount
     * @param User $user
     * @param Account $account
     * @param \DateTime $date
     * @return CreditLimit
     * @throws \Exception
     */
    public function updateCreditLimit($amount, User $user = null, Account $account, $date = null)
    {
        if(!$amount || $amount <= 0)
            throw new \Exception("Amount must be larger than zero.");

        if(!$user) $user = $this->getUser();

        if(!$account)
            throw new \Exception("Account must be set.");

        $creditLimit = new CreditLimit();

        if($user->hasRole("ROLE_MASTER_ADMIN") || $user->hasRole("ROLE_MASTER"))
        {
            if($account->getAccType() != Account::DISTRIBUTOR)
                throw new \Exception("Account account must be distributor.");
        }
        elseif($user->hasRole("ROLE_DISTRIBUTOR_ADMIN") || $user->hasRole("ROLE_DISTRIBUTOR"))
        {
            if($account->getAccType() != Account::RETAILER)
                throw new \Exception("Destination account must be retailer.");

            $DiffAccountCreditLimit = $account->getCreditLimit() - $amount;
            if($DiffAccountCreditLimit < 0)
                $creditLimit->setTransaction($this->recordTransaction($DiffAccountCreditLimit,$account,$date,"credit limit"));
        }
        else
        {
            throw new \Exception("This user can't record transfer.");
        }

        $account->setAccCreditLimit($amount);

        $creditLimit->setDate($date?:new \DateTime());
        $creditLimit->setUser($user);
        $creditLimit->setAmount($amount);
        $creditLimit->setAccount($account);
        $this->em->persist($creditLimit);
        return $creditLimit;
    }

    /**
     * @param $amount
     * @param Account $account
     * @throws \Exception
     */
    public function ReserveAmountOnBalance($amount, Account $account)
    {
        if(!$amount || $amount <= 0)
            throw new \Exception("Amount must be larger than zero.");

        if(!$account)
            throw new \Exception("Account must be set.");
    }
}
