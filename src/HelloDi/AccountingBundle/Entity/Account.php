<?php
namespace HelloDi\AccountingBundle\Entity;

use HelloDi\DiDistributorsBundle\Entity\Entity;
use HelloDi\PricingBundle\Entity\Model;
use HelloDi\PricingBundle\Entity\Price;
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
    const PROVIDER = 1;
    const DISTRIBUTOR = 2;
    const RETAILER = 3;
    const API = 4;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=2, nullable=true, name="default_language")
     */
    private $defaultLanguage;

    /**
     * @ORM\Column(type="string", length=45, nullable=false, name="name")
     */
    private $name;

    /**
     * @ORM\Column(type="decimal", nullable=false, name="balance", precision=6, scale=2)
     */
    private $balance = 0.0;

    /**
     * @ORM\Column(type="decimal", nullable=false, name="credit_limit_amount", precision=6, scale=2)
     */
    private $creditLimitAmount = 0.0;

    /**
     * @ORM\Column(type="decimal", nullable=false, name="reserve", precision=6, scale=2)
     */
    private $reserve = 0.0;

    /**
     * @ORM\Column(type="smallint", nullable=false, name="type")
     */
    private $type;

    /**
     * @ORM\Column(type="date", nullable=false, name="creation_date")
     */
    private $creationDate;

    /**
     * @ORM\Column(type="string", length=45, nullable=true, name="time_zone")
     */
    private $timeZone;

    /**
     * @ORM\Column(type="integer", nullable=true, name="terms")
     */
    private $terms;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\PricingBundle\Entity\Price", mappedBy="account")
     */
    private $prices;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Ticket", mappedBy="accountDist")
     */
    private $ticketsDist;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Ticket", mappedBy="accountRetailer")
     */
    private $ticketsRetailer;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\AccountingBundle\Entity\Transaction", mappedBy="account")
     */
    private $transactions;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\User", mappedBy="account")
     */
    private $users;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Entity", inversedBy="accounts")
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id", nullable=true)
     */
    private $entity;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\AccountingBundle\Entity\CreditLimit", mappedBy="account")
     */
    private $creditLimits;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\PricingBundle\Entity\Model", mappedBy="account")
     */
    private $models;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->prices = new ArrayCollection();
        $this->ticketsDist = new ArrayCollection();
        $this->ticketsRetailer = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->creditLimits = new ArrayCollection();
        $this->models = new ArrayCollection();
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
     * Set defaultLanguage
     *
     * @param string $defaultLanguage
     * @return Account
     */
    public function setDefaultLanguage($defaultLanguage)
    {
        $this->defaultLanguage = $defaultLanguage;

        return $this;
    }

    /**
     * Get defaultLanguage
     *
     * @return string
     */
    public function getDefaultLanguage()
    {
        return $this->defaultLanguage;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Account
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set balance
     *
     * @param string $balance
     * @return Account
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * Get balance
     *
     * @return string
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * Set creditLimitAmount
     *
     * @param string $creditLimitAmount
     * @return Account
     */
    public function setCreditLimitAmount($creditLimitAmount)
    {
        $this->creditLimitAmount = $creditLimitAmount;

        return $this;
    }

    /**
     * Get creditLimitAmount
     *
     * @return string
     */
    public function getCreditLimitAmount()
    {
        return $this->creditLimitAmount;
    }

    /**
     * Set reserve
     *
     * @param string $reserve
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
     * @return string
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
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return Account
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set timeZone
     *
     * @param string $timeZone
     * @return Account
     */
    public function setTimeZone($timeZone)
    {
        $this->timeZone = $timeZone;

        return $this;
    }

    /**
     * Get timeZone
     *
     * @return string
     */
    public function getTimeZone()
    {
        return $this->timeZone;
    }

    /**
     * Set terms
     *
     * @param integer $terms
     * @return Account
     */
    public function setTerms($terms)
    {
        $this->terms = $terms;

        return $this;
    }

    /**
     * Get terms
     *
     * @return integer
     */
    public function getTerms()
    {
        return $this->terms;
    }

    /**
     * Add prices
     *
     * @param Price $prices
     * @return Account
     */
    public function addPrice(Price $prices)
    {
        $this->prices[] = $prices;

        return $this;
    }

    /**
     * Remove prices
     *
     * @param Price $prices
     */
    public function removePrice(Price $prices)
    {
        $this->prices->removeElement($prices);
    }

    /**
     * Get prices
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * Add ticketsDist
     *
     * @param Ticket $ticketsDist
     * @return Account
     */
    public function addTicketsDist(Ticket $ticketsDist)
    {
        $this->ticketsDist[] = $ticketsDist;

        return $this;
    }

    /**
     * Remove ticketsDist
     *
     * @param Ticket $ticketsDist
     */
    public function removeTicketsDist(Ticket $ticketsDist)
    {
        $this->ticketsDist->removeElement($ticketsDist);
    }

    /**
     * Get ticketsDist
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTicketsDist()
    {
        return $this->ticketsDist;
    }

    /**
     * Add ticketsRetailer
     *
     * @param Ticket $ticketsRetailer
     * @return Account
     */
    public function addTicketsRetailer(Ticket $ticketsRetailer)
    {
        $this->ticketsRetailer[] = $ticketsRetailer;

        return $this;
    }

    /**
     * Remove ticketsRetailer
     *
     * @param Ticket $ticketsRetailer
     */
    public function removeTicketsRetailer(Ticket $ticketsRetailer)
    {
        $this->ticketsRetailer->removeElement($ticketsRetailer);
    }

    /**
     * Get ticketsRetailer
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTicketsRetailer()
    {
        return $this->ticketsRetailer;
    }

    /**
     * Add transactions
     *
     * @param Transaction $transactions
     * @return Account
     */
    public function addTransaction(Transaction $transactions)
    {
        $this->transactions[] = $transactions;

        return $this;
    }

    /**
     * Remove transactions
     *
     * @param Transaction $transactions
     */
    public function removeTransaction(Transaction $transactions)
    {
        $this->transactions->removeElement($transactions);
    }

    /**
     * Get transactions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * Add users
     *
     * @param User $users
     * @return Account
     */
    public function addUser(User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param User $users
     */
    public function removeUser(User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set entity
     *
     * @param Entity $entity
     * @return Account
     */
    public function setEntity(Entity $entity = null)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get entity
     *
     * @return Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Add creditLimits
     *
     * @param CreditLimit $creditLimits
     * @return Account
     */
    public function addCreditLimit(CreditLimit $creditLimits)
    {
        $this->creditLimits[] = $creditLimits;

        return $this;
    }

    /**
     * Remove creditLimits
     *
     * @param CreditLimit $creditLimits
     */
    public function removeCreditLimit(CreditLimit $creditLimits)
    {
        $this->creditLimits->removeElement($creditLimits);
    }

    /**
     * Get creditLimits
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCreditLimits()
    {
        return $this->creditLimits;
    }

    /**
     * Add models
     *
     * @param Model $models
     * @return Account
     */
    public function addModel(Model $models)
    {
        $this->models[] = $models;

        return $this;
    }

    /**
     * Remove models
     *
     * @param Model $models
     */
    public function removeModel(Model $models)
    {
        $this->models->removeElement($models);
    }

    /**
     * Get models
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getModels()
    {
        return $this->models;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->name . ', ' . $this->defaultLanguage;
    }

    /**
     * @return string
     */
    public function  __toString()
    {
        return $this->getName();
    }
}