<?php
namespace HelloDi\AccountingBundle\Entity;

use HelloDi\DiDistributorsBundle\Entity\Entiti;
use HelloDi\DiDistributorsBundle\Entity\Input;
use HelloDi\DiDistributorsBundle\Entity\Price;
use HelloDi\DiDistributorsBundle\Entity\Ticket;
use HelloDi\DiDistributorsBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/** 
 * @ORM\Entity
 * @ORM\Table(name="account")
 */
class Account
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=2, nullable=true, name="acc_default_language")
     */
    private $accDefaultLanguage;

    /**
     * @ORM\Column(type="string", length=45, nullable=false, name="acc_name")
     */
    private $accName;

    /** 
     * @ORM\Column(type="decimal", nullable=false, name="acc_balance", precision=6, scale=2)
     */
    private $accBalance = 0.0;

    /** 
     * @ORM\Column(type="string", length=3, nullable=false, name="acc_currency")
     */
    private $accCurrency;

    /** 
     * @ORM\Column(type="decimal", nullable=false, name="acc_credit_limit", precision=6, scale=2)
     */
    private $accCreditLimit = 0.0;

    /** 
     * @ORM\Column(type="date", nullable=false, name="acc_creation_date")
     */
    private $accCreationDate;

    /** 
     * @ORM\Column(type="string", length=45, nullable=true, name="acc_time_zone")
     */
    private $accTimeZone;

    /** 
     * @ORM\Column(type="integer", nullable=true, name="acc_terms")
     */
    private $accTerms;

    /** 
     * @ORM\Column(type="smallint", nullable=false, name="acc_type")
     */
    private $accType;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\AccountingBundle\Entity\Account", mappedBy="Parent")
     */
    private $children;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Input", mappedBy="Account")
     */
    private $Inputs;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Price", mappedBy="Account")
     */
    private $Prices;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Ticket", mappedBy="Account")
     */
    private $Tickets;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\AccountingBundle\Entity\Transaction", mappedBy="Account")
     */
    private $Transactions;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\User", mappedBy="Account")
     */
    private $Users;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Entiti", inversedBy="Accounts")
     * @ORM\JoinColumn(name="entiti_id", referencedColumnName="id", nullable=true)
     */
    private $Entiti;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\AccountingBundle\Entity\Account", inversedBy="children")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id")
     */
    private $Parent;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\AccountingBundle\Entity\CreditLimit", mappedBy="account")
     */
    private $creditLimit;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->Inputs = new ArrayCollection();
        $this->Prices = new ArrayCollection();
        $this->Tickets = new ArrayCollection();
        $this->Transactions = new ArrayCollection();
        $this->Users = new ArrayCollection();
        $this->creditLimit = new ArrayCollection();
    }
    
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
     * Get accDefaultLanguage
     *
     * @return string 
     */
    public function getAccDefaultLanguage()
    {
        return $this->accDefaultLanguage;
    }

    /**
     * Set accName
     *
     * @param string $accName
     * @return Account
     */
    public function setAccName($accName)
    {
        $this->accName = $accName;
    
        return $this;
    }

    /**
     * Get accName
     *
     * @return string 
     */
    public function getAccName()
    {
        return $this->accName;
    }

    /**
     * Set accBalance
     *
     * @param float $accBalance
     * @return Account
     */
    public function setAccBalance($accBalance)
    {
        $this->accBalance = $accBalance;
    
        return $this;
    }

    /**
     * Get accBalance
     *
     * @return float 
     */
    public function getAccBalance()
    {
        return $this->accBalance;
    }

    /**
     * Set accCurrency
     *
     * @param string $accCurrency
     * @return Account
     */
    public function setAccCurrency($accCurrency)
    {
        $this->accCurrency = $accCurrency;
    
        return $this;
    }

    /**
     * Get accCurrency
     *
     * @return string 
     */
    public function getAccCurrency()
    {
        return $this->accCurrency;
    }

    /**
     * Set accCreditLimit
     *
     * @param float $accCreditLimit
     * @return Account
     */
    public function setAccCreditLimit($accCreditLimit)
    {
        $this->accCreditLimit = $accCreditLimit;
    
        return $this;
    }

    /**
     * Get accCreditLimit
     *
     * @return float 
     */
    public function getAccCreditLimit()
    {
        return $this->accCreditLimit;
    }

    /**
     * Set accCreationDate
     *
     * @param \DateTime $accCreationDate
     * @return Account
     */
    public function setAccCreationDate($accCreationDate)
    {
        $this->accCreationDate = $accCreationDate;
    
        return $this;
    }

    /**
     * Get accCreationDate
     *
     * @return \DateTime 
     */
    public function getAccCreationDate()
    {
        return $this->accCreationDate;
    }

    /**
     * Set accTimeZone
     *
     * @param string $accTimeZone
     * @return Account
     */
    public function setAccTimeZone($accTimeZone)
    {
        $this->accTimeZone = $accTimeZone;
    
        return $this;
    }

    /**
     * Get accTimeZone
     *
     * @return string 
     */
    public function getAccTimeZone()
    {
        return $this->accTimeZone;
    }

    /**
     * Set accTerms
     *
     * @param integer $accTerms
     * @return Account
     */
    public function setAccTerms($accTerms)
    {
        $this->accTerms = $accTerms;
    
        return $this;
    }

    /**
     * Get accTerms
     *
     * @return integer 
     */
    public function getAccTerms()
    {
        return $this->accTerms;
    }

    /**
     * Set accType
     *
     * @param integer $accType
     * @return Account
     */
    public function setAccType($accType)
    {
        $this->accType = $accType;
    
        return $this;
    }

    /**
     * Get accType
     *
     * @return integer 
     */
    public function getAccType()
    {
        return $this->accType;
    }

    /**
     * Add children
     *
     * @param Account $children
     * @return Account
     */
    public function addChildren(Account $children)
    {
        $this->children[] = $children;
    
        return $this;
    }

    /**
     * Remove children
     *
     * @param Account $children
     */
    public function removeChildren(Account $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get Children
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Add Inputs
     *
     * @param Input $inputs
     * @return Account
     */
    public function addInput(Input $inputs)
    {
        $this->Inputs[] = $inputs;
    
        return $this;
    }

    /**
     * Remove Inputs
     *
     * @param Input $inputs
     */
    public function removeInput(Input $inputs)
    {
        $this->Inputs->removeElement($inputs);
    }

    /**
     * Get Inputs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInputs()
    {
        return $this->Inputs;
    }

    /**
     * Add Prices
     *
     * @param Price $prices
     * @return Account
     */
    public function addPrice(Price $prices)
    {
        $this->Prices[] = $prices;
    
        return $this;
    }

    /**
     * Remove Prices
     *
     * @param Price $prices
     */
    public function removePrice(Price $prices)
    {
        $this->Prices->removeElement($prices);
    }

    /**
     * Get Prices
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPrices()
    {
        return $this->Prices;
    }

    /**
     * Add Tickets
     *
     * @param Ticket $tickets
     * @return Account
     */
    public function addTicket(Ticket $tickets)
    {
        $this->Tickets[] = $tickets;
    
        return $this;
    }

    /**
     * Remove Tickets
     *
     * @param Ticket $tickets
     */
    public function removeTicket(Ticket $tickets)
    {
        $this->Tickets->removeElement($tickets);
    }

    /**
     * Get Tickets
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTickets()
    {
        return $this->Tickets;
    }

    /**
     * Add Transactions
     *
     * @param Transaction $transactions
     * @return Account
     */
    public function addTransaction(Transaction $transactions)
    {
        $this->Transactions[] = $transactions;
    
        return $this;
    }

    /**
     * Remove Transactions
     *
     * @param Transaction $transactions
     */
    public function removeTransaction(Transaction $transactions)
    {
        $this->Transactions->removeElement($transactions);
    }

    /**
     * Get Transactions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTransactions()
    {
        return $this->Transactions;
    }

    /**
     * Add Users
     *
     * @param User $users
     * @return Account
     */
    public function addUser(User $users)
    {
        $this->Users[] = $users;
    
        return $this;
    }

    /**
     * Remove Users
     *
     * @param User $users
     */
    public function removeUser(User $users)
    {
        $this->Users->removeElement($users);
    }

    /**
     * Get Users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->Users;
    }

    /**
     * Set Entiti
     *
     * @param Entiti $entiti
     * @return Account
     */
    public function setEntiti(Entiti $entiti)
    {
        $this->Entiti = $entiti;
    
        return $this;
    }

    /**
     * Get Entiti
     *
     * @return Entiti
     */
    public function getEntiti()
    {
        return $this->Entiti;
    }

    /**
     * Set Parent
     *
     * @param Account $parent
     * @return Account
     */
    public function setParent(Account $parent = null)
    {
        $this->Parent = $parent;
    
        return $this;
    }

    /**
     * Get Parent
     *
     * @return Account
     */
    public function getParent()
    {
        return $this->Parent;
    }

    /**
     * Set accDefaultLanguage
     *
     * @param string $accDefaultLanguage
     * @return Account
     */
    public function setAccDefaultLanguage($accDefaultLanguage)
    {
        $this->accDefaultLanguage = $accDefaultLanguage;
    
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->accName .', '. $this->accDefaultLanguage ;
    }

    /**
     * @return string
     */
    public function getNameWithCurrency()
    {
        return $this->getAccName() .' | '.$this->getAccBalance().' ( '. $this->getAccCurrency().' )' ;
    }

    /**
     * @return string
     */
    public  function  __toString(){
        return $this->getAccName();
    }

    /**
     * Add creditLimit
     *
     * @param CreditLimit $creditLimit
     * @return Account
     */
    public function addCreditLimit(CreditLimit $creditLimit)
    {
        $this->creditLimit[] = $creditLimit;
    
        return $this;
    }

    /**
     * Remove creditLimit
     *
     * @param CreditLimit $creditLimit
     */
    public function removeCreditLimit(CreditLimit $creditLimit)
    {
        $this->creditLimit->removeElement($creditLimit);
    }

    /**
     * Get creditLimit
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCreditLimit()
    {
        return $this->creditLimit;
    }
}