<?php

namespace HelloDi\AccountingBundle\Container;

use HelloDi\AccountingBundle\Entity\Account;

/**
 * Class TransactionContainer
 * @package HelloDi\AccountingBundle\Container
 */
class TransactionContainer {
    /**
     * @var Account
     */
    private $account;
    /**
     * @var float
     */
    private $amount;
    /**
     * @var string
     */
    private $description;
    /**
     * @var float
     */
    private $vat = 0.0;
    /**
     * @var float
     */
    private $fees = 0.0;

    /**
     * @param Account $account
     * @param float $amount
     * @param string $description
     * @param float $vat
     * @param float $fees
     * @throws \Exception
     */
    public function __construct(Account $account, $amount, $description, $vat = 0.0, $fees = 0.0)
    {
        $this->setAccount($account);
        $this->setAmount($amount);
        $this->setDescription($description);
        $this->setVat($vat);
        $this->setFees($fees);
    }

    /**
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param Account $account
     * @throws \Exception
     */
    public function setAccount(Account $account)
    {
        if(!(is_object($account) && $account instanceof Account))
            throw new \Exception("It's not instance of account.");

        $this->account = $account;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     * @throws \Exception
     */
    public function setAmount($amount)
    {
        if(!is_numeric($amount))
            throw new \Exception("It's must be number.");

        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @throws \Exception
     */
    public function setDescription($description)
    {
        if(!is_string($description) && !is_null($description))
            throw new \Exception("It's must be string.");

        $this->description = $description;
    }

    /**
     * @return float
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * @param float $vat
     * @throws \Exception
     */
    public function setVat($vat)
    {
        if(!is_numeric($vat) && $vat >= 0)
            throw new \Exception("It's must be larger than zero.");

        $this->vat = $vat;
    }

    /**
     * @return float
     */
    public function getFees()
    {
        return $this->fees;
    }

    /**
     * @param float $fees
     * @throws \Exception
     */
    public function setFees($fees)
    {
        if(!is_numeric($fees) && $fees >= 0)
            throw new \Exception("It's must be larger than zero.");

        $this->fees = $fees;
    }
} 