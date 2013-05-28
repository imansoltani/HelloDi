<?php
namespace HelloDi\DiDistributorsBundle\Entity;
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
     * @ORM\Column(type="string", length=2, nullable=true, name="accdefaultlanguage")
     */
    private $accDefaultLanguage;

    /** 
     * @ORM\Column(type="string", length=45, nullable=false, name="acc_name")
     */
    private $accName;

    /** 
     * @ORM\Column(type="decimal", nullable=false, name="acc_balance")
     */
    private $accBalance;

    /** 
     * @ORM\Column(type="string", length=3, nullable=false, name="acc_currency")
     */
    private $accCurrency;

    /** 
     * @ORM\Column(type="decimal", nullable=false, name="credit_limit")
     */
    private $accCreditLimit;

    /** 
     * @ORM\Column(type="date", nullable=false, name="acc_creation_date")
     */
    private $accCreationDate;

    /** 
     * @ORM\Column(type="string", length=45, nullable=true, name="acc_time_zone")
     */
    private $accTimeZone;

    /** 
     * @ORM\Column(type="integer", nullable=false, name="acc_terms")
     */
    private $accTerms;

    /** 
     * @ORM\Column(type="smallint", nullable=false, name="acc_type")
     */
    private $accType;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Account", mappedBy="Parent")
     */
    private $Childrens;

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
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Transaction", mappedBy="Account")
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
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Account", inversedBy="Childrens")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id")
     */
    private $Parent;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->Childrens = new \Doctrine\Common\Collections\ArrayCollection();
        $this->Inputs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->Prices = new \Doctrine\Common\Collections\ArrayCollection();
        $this->Tickets = new \Doctrine\Common\Collections\ArrayCollection();
        $this->Transactions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->Users = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add Childrens
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Account $childrens
     * @return Account
     */
    public function addChildren(\HelloDi\DiDistributorsBundle\Entity\Account $childrens)
    {
        $this->Childrens[] = $childrens;
    
        return $this;
    }

    /**
     * Remove Childrens
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Account $childrens
     */
    public function removeChildren(\HelloDi\DiDistributorsBundle\Entity\Account $childrens)
    {
        $this->Childrens->removeElement($childrens);
    }

    /**
     * Get Childrens
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChildrens()
    {
        return $this->Childrens;
    }

    /**
     * Add Inputs
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Input $inputs
     * @return Account
     */
    public function addInput(\HelloDi\DiDistributorsBundle\Entity\Input $inputs)
    {
        $this->Inputs[] = $inputs;
    
        return $this;
    }

    /**
     * Remove Inputs
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Input $inputs
     */
    public function removeInput(\HelloDi\DiDistributorsBundle\Entity\Input $inputs)
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
     * @param \HelloDi\DiDistributorsBundle\Entity\Price $prices
     * @return Account
     */
    public function addPrice(\HelloDi\DiDistributorsBundle\Entity\Price $prices)
    {
        $this->Prices[] = $prices;
    
        return $this;
    }

    /**
     * Remove Prices
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Price $prices
     */
    public function removePrice(\HelloDi\DiDistributorsBundle\Entity\Price $prices)
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
     * @param \HelloDi\DiDistributorsBundle\Entity\Ticket $tickets
     * @return Account
     */
    public function addTicket(\HelloDi\DiDistributorsBundle\Entity\Ticket $tickets)
    {
        $this->Tickets[] = $tickets;
    
        return $this;
    }

    /**
     * Remove Tickets
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Ticket $tickets
     */
    public function removeTicket(\HelloDi\DiDistributorsBundle\Entity\Ticket $tickets)
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
     * @param \HelloDi\DiDistributorsBundle\Entity\Transaction $transactions
     * @return Account
     */
    public function addTransaction(\HelloDi\DiDistributorsBundle\Entity\Transaction $transactions)
    {
        $this->Transactions[] = $transactions;
    
        return $this;
    }

    /**
     * Remove Transactions
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Transaction $transactions
     */
    public function removeTransaction(\HelloDi\DiDistributorsBundle\Entity\Transaction $transactions)
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
     * @param \HelloDi\DiDistributorsBundle\Entity\User $users
     * @return Account
     */
    public function addUser(\HelloDi\DiDistributorsBundle\Entity\User $users)
    {
        $this->Users[] = $users;
    
        return $this;
    }

    /**
     * Remove Users
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\User $users
     */
    public function removeUser(\HelloDi\DiDistributorsBundle\Entity\User $users)
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
     * @param \HelloDi\DiDistributorsBundle\Entity\Entiti $entiti
     * @return Account
     */
    public function setEntiti(\HelloDi\DiDistributorsBundle\Entity\Entiti $entiti)
    {
        $this->Entiti = $entiti;
    
        return $this;
    }

    /**
     * Get Entiti
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\Entiti 
     */
    public function getEntiti()
    {
        return $this->Entiti;
    }

    /**
     * Set Parent
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Account $parent
     * @return Account
     */
    public function setParent(\HelloDi\DiDistributorsBundle\Entity\Account $parent = null)
    {
        $this->Parent = $parent;
    
        return $this;
    }

    /**
     * Get Parent
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\Account 
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
}