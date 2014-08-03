<?php
namespace HelloDi\AccountingBundle\Entity;

use HelloDi\CoreBundle\Entity\Entity;
use HelloDi\PricingBundle\Entity\Model;
use HelloDi\PricingBundle\Entity\Price;
use HelloDi\CoreBundle\Entity\User;
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
    protected $id;

    /**
     * @ORM\Column(type="string", length=2, nullable=true, name="default_language")
     */
    protected $defaultLanguage;

    /**
     * @ORM\Column(type="string", length=45, nullable=false, name="name")
     */
    protected $name;

    /**
     * @ORM\Column(type="decimal", nullable=false, name="balance", precision=6, scale=2)
     */
    protected $balance = 0.0;

    /**
     * @ORM\Column(type="decimal", nullable=false, name="credit_limit_amount", precision=6, scale=2)
     */
    protected $creditLimitAmount = 0.0;

    /**
     * @ORM\Column(type="decimal", nullable=false, name="reserve", precision=6, scale=2)
     */
    protected $reserve = 0.0;

    /**
     * @ORM\Column(type="smallint", nullable=false, name="type")
     */
    protected $type;

    /**
     * @ORM\Column(type="date", nullable=false, name="creation_date")
     */
    protected $creationDate;

    /**
     * @ORM\Column(type="integer", nullable=true, name="terms")
     */
    protected $terms = 0;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\PricingBundle\Entity\Price", mappedBy="account")
     */
    protected $prices;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\AccountingBundle\Entity\Transaction", mappedBy="account")
     */
    protected $transactions;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\CoreBundle\Entity\User", mappedBy="account")
     */
    protected $users;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\CoreBundle\Entity\Entity", inversedBy="accounts")
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id", nullable=true)
     */
    protected $entity;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\AccountingBundle\Entity\CreditLimit", mappedBy="account")
     */
    protected $creditLimits;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\PricingBundle\Entity\Model", mappedBy="account")
     */
    protected $models;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\PricingBundle\Entity\Model", inversedBy="accounts")
     * @ORM\JoinColumn(name="model_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->prices = new ArrayCollection();
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
     * @param float $balance
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
     * @return float
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * Set creditLimitAmount
     *
     * @param float $creditLimitAmount
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
     * @return float
     */
    public function getCreditLimitAmount()
    {
        return $this->creditLimitAmount;
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

    /**
     * Set model
     *
     * @param Model $model
     * @return Account
     */
    public function setModel(Model $model = null)
    {
        $this->model = $model;
    
        return $this;
    }

    /**
     * Get model
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }
}