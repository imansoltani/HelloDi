<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/** 
 * @ORM\Entity
 * @ORM\Table(name="transaction")
 * @ORM\HasLifecycleCallbacks()
 */
class Transaction
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** 
     * @ORM\Column(type="decimal", nullable=true)
     */
    private $tranBookingValue;

    /** 
     * @ORM\Column(type="decimal", nullable=true, name="tran_credit")
     */
    private $tranCredit;

    /** 
     * @ORM\Column(type="decimal", nullable=true, name="tran_debit")
     */
    private $tranDebit;

    /** 
     * @ORM\Column(type="decimal", nullable=false, name="tran_fees")
     */
    private $tranFees;

    /** 
     * @ORM\Column(type="string", length=3, nullable=false, name="tran_currency")
     */
    private $tranCurrency;

    /** 
     * @ORM\Column(type="date", nullable=false, name="tran_date")
     */
    private $tranDate;

    /** 
     * @ORM\Column(type="datetime", nullable=true, name="tran_insert")
     */
    private $tranInsert;

    /** 
     * @ORM\Column(type="string", length=45, nullable=true, name="tran_description")
     */
    private $tranDescription;

    /** 
     * @ORM\Column(type="string", length=30, nullable=false)
     */
    private $tranAction;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Account", inversedBy="Transactions")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     */
    private $Account;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\User", inversedBy="Transactions")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $User;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Code", inversedBy="Transactions")
     * @ORM\JoinColumn(name="code_id", referencedColumnName="id", nullable=false)
     */
    private $Code;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set tranBookingValue
     *
     * @param float $tranBookingValue
     * @return Transaction
     */
    public function setTranBookingValue($tranBookingValue)
    {
        $this->tranBookingValue = $tranBookingValue;
    
        return $this;
    }

    /**
     * Get tranBookingValue
     *
     * @return float 
     */
    public function getTranBookingValue()
    {
        return $this->tranBookingValue;
    }

    /**
     * Set tranCredit
     *
     * @param float $tranCredit
     * @return Transaction
     */
    public function setTranCredit($tranCredit)
    {
        $this->tranCredit = $tranCredit;
    
        return $this;
    }

    /**
     * Get tranCredit
     *
     * @return float 
     */
    public function getTranCredit()
    {
        return $this->tranCredit;
    }

    /**
     * Set tranDebit
     *
     * @param float $tranDebit
     * @return Transaction
     */
    public function setTranDebit($tranDebit)
    {
        $this->tranDebit = $tranDebit;
    
        return $this;
    }

    /**
     * Get tranDebit
     *
     * @return float 
     */
    public function getTranDebit()
    {
        return $this->tranDebit;
    }

    /**
     * Set tranFees
     *
     * @param float $tranFees
     * @return Transaction
     */
    public function setTranFees($tranFees)
    {
        $this->tranFees = $tranFees;
    
        return $this;
    }

    /**
     * Get tranFees
     *
     * @return float 
     */
    public function getTranFees()
    {
        return $this->tranFees;
    }

    /**
     * Set tranCurrency
     *
     * @param string $tranCurrency
     * @return Transaction
     */
    public function setTranCurrency($tranCurrency)
    {
        $this->tranCurrency = $tranCurrency;
    
        return $this;
    }

    /**
     * Get tranCurrency
     *
     * @return string 
     */
    public function getTranCurrency()
    {
        return $this->tranCurrency;
    }

    /**
     * Set tranDate
     *
     * @param \DateTime $tranDate
     * @return Transaction
     */
    public function setTranDate($tranDate)
    {
        $this->tranDate = $tranDate;
    
        return $this;
    }

    /**
     * Get tranDate
     *
     * @return \DateTime 
     */
    public function getTranDate()
    {
        return $this->tranDate;
    }

    /**
     * Set tranInsert
     *
     * @param \DateTime $tranInsert
     * @return Transaction
     */
    public function setTranInsert($tranInsert)
    {
        $this->tranInsert = $tranInsert;
    
        return $this;
    }

    /**
     * Get tranInsert
     *
     * @return \DateTime 
     */
    public function getTranInsert()
    {
        return $this->tranInsert;
    }

    /**
     * Set tranDescription
     *
     * @param string $tranDescription
     * @return Transaction
     */
    public function setTranDescription($tranDescription)
    {
        $this->tranDescription = $tranDescription;
    
        return $this;
    }

    /**
     * Get tranDescription
     *
     * @return string 
     */
    public function getTranDescription()
    {
        return $this->tranDescription;
    }

    /**
     * Set tranAction
     *
     * @param string $tranAction
     * @return Transaction
     */
    public function setTranAction($tranAction)
    {
        $this->tranAction = $tranAction;
    
        return $this;
    }

    /**
     * Get tranAction
     *
     * @return string 
     */
    public function getTranAction()
    {
        return $this->tranAction;
    }

    /**
     * Set Account
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Account $account
     * @return Transaction
     */
    public function setAccount(\HelloDi\DiDistributorsBundle\Entity\Account $account)
    {
        $this->Account = $account;
    
        return $this;
    }

    /**
     * Get Account
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\Account 
     */
    public function getAccount()
    {
        return $this->Account;
    }

    /**
     * Set User
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\User $user
     * @return Transaction
     */
    public function setUser(\HelloDi\DiDistributorsBundle\Entity\User $user)
    {
        $this->User = $user;
    
        return $this;
    }

    /**
     * Get User
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->User;
    }

    /**
     * Set Code
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Code $code
     * @return Transaction
     */
    public function setCode(\HelloDi\DiDistributorsBundle\Entity\Code $code)
    {
        $this->Code = $code;
    
        return $this;
    }

    /**
     * Get Code
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\Code 
     */
    public function getCode()
    {
        return $this->Code;
    }

    /**
     * @ORM\PrePersist
     */
    public function updateAccountBalance()
    {
        switch ($this->getTranAction()) {
            case 'trans':
                break;

            case 'peym':
                $amount = $this->getTranCredit();
                $currentBalance = $this->getAccount()->getAccBalance();
                $this->getAccount()->setAccBalance($currentBalance + $amount);
                break;

            case 'sale':
                $amount = $this->getTranCredit();
                $currentBalance = $this->getAccount()->getAccBalance();
                $this->getAccount()->setAccBalance($currentBalance - $amount);
                break;

            case 'cred':
                break;

            case 'add':
                break;
        }
    }
}