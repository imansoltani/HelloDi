<?php
namespace HelloDi\AccountingBundle\Entity;

use HelloDi\DiDistributorsBundle\Entity\Entiti;
use HelloDi\DiDistributorsBundle\Entity\Price;
use HelloDi\DiDistributorsBundle\Entity\Ticket;
use HelloDi\DiDistributorsBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="HelloDi\AccountingBundle\Entity\AccountRepository")
 * @ORM\Table(name="account")
 */
class Account
{
    const API = 1;
    const PROVIDER = 2;
    const DISTRIBUTOR = 3;
    const RETAILER = 4;

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
     * @ORM\Column(type="decimal", nullable=false, name="acc_credit_limit", precision=6, scale=2)
     */
    private $accCreditLimit = 0.0;

    /**
     * @ORM\Column(type="decimal", nullable=false, name="reserve", precision=6, scale=2)
     */
    private $reserve = 0.0;

    /**
     * @ORM\Column(type="smallint", nullable=false, name="type")
     */
    private $type;

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
     * @ORM\OneToMany(targetEntity="HelloDi\AccountingBundle\Entity\CreditLimit", mappedBy="account")
     */
    private $creditLimit;

    /**
     * Constructor
     */
    public function __construct()
    {
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
     * Set reserve
     *
     * @param float $reserve
     * @return Account
     */
    public function setReserve($reserve)
    {
        $this->reserve = $reserve;

        return $this;
    }

    /**
     * Get reserve
     *
     * @return float
     */
    public function getReserve()
    {
        return $this->reserve;
    }

    /**
     * Set type
     *
     * @param integer $type
     * @return Account
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
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
        return $this->accName . ', ' . $this->accDefaultLanguage;
    }

    /**
     * @return string
     */
    public function  __toString()
    {
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