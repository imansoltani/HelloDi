<?php

namespace HelloDi\DiDistributorsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use HelloDi\AccountingBundle\Entity\Account;

/**
 * Distributor
 *
 * @ORM\Table(name="distributor")
 * @ORM\Entity(repositoryClass="HelloDi\DiDistributorsBundle\Entity\DistributorRepository")
 */
class Distributor
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=3)
     */
    private $currency;

    /**
     * @var string
     *
     * @ORM\Column(name="timeZone", type="string", length=45)
     */
    private $timeZone;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\TaxHistory", inversedBy="Distributors")
     * @ORM\JoinColumn(name="tax_history_id", referencedColumnName="id", nullable=true)
     */
    private $taxHistory;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Retailer", mappedBy="distributor")
     */
    private $retailers;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\AccountingBundle\Entity\Account", inversedBy="distributor")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     */
    private $account;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->retailers = new ArrayCollection();
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
     * Set currency
     *
     * @param string $currency
     * @return Distributor
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    
        return $this;
    }

    /**
     * Get currency
     *
     * @return string 
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set timeZone
     *
     * @param string $timeZone
     * @return Distributor
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
     * Set taxHistory
     *
     * @param TaxHistory $taxHistory
     * @return Distributor
     */
    public function setTaxHistory(TaxHistory $taxHistory = null)
    {
        $this->taxHistory = $taxHistory;
    
        return $this;
    }

    /**
     * Get taxHistory
     *
     * @return TaxHistory
     */
    public function getTaxHistory()
    {
        return $this->taxHistory;
    }
    
    /**
     * Add retailers
     *
     * @param Retailer $retailers
     * @return Distributor
     */
    public function addRetailer(Retailer $retailers)
    {
        $this->retailers[] = $retailers;
    
        return $this;
    }

    /**
     * Remove retailers
     *
     * @param Retailer $retailers
     */
    public function removeRetailer(Retailer $retailers)
    {
        $this->retailers->removeElement($retailers);
    }

    /**
     * Get retailers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRetailers()
    {
        return $this->retailers;
    }

    /**
     * Set account
     *
     * @param Account $account
     * @return Distributor
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;
    
        return $this;
    }

    /**
     * Get account
     *
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @return string
     */
    public function getNameWithCurrency()
    {
        return $this->getAccount()->getAccName() . ' | ' . $this->getAccount()->getAccBalance() . ' ( ' . $this->getCurrency() . ' )';
    }
}