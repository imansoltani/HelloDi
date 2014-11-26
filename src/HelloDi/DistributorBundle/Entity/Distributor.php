<?php

namespace HelloDi\DistributorBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\CoreBundle\Entity\Ticket;
use HelloDi\RetailerBundle\Entity\Retailer;

/**
 * Distributor
 *
 * @ORM\Table(name="distributor")
 * @ORM\Entity(repositoryClass="HelloDi\DistributorBundle\Entity\DistributorRepository")
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
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=3)
     */
    protected $currency;

    /**
     * @var string
     *
     * @ORM\Column(name="timeZone", type="string", length=45)
     */
    protected $timeZone;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\RetailerBundle\Entity\Retailer", mappedBy="distributor")
     */
    protected $retailers;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\AccountingBundle\Entity\Account")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     */
    protected $account;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\CoreBundle\Entity\Ticket", mappedBy="distributor")
     */
    protected $tickets;

    /**
     * @ORM\Column(type="boolean", name="vat")
     */
    protected $vat = true;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->retailers = new ArrayCollection();
        $this->tickets = new ArrayCollection();
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
     * Add tickets
     *
     * @param Ticket $tickets
     * @return Distributor
     */
    public function addTicket(Ticket $tickets)
    {
        $this->tickets[] = $tickets;
    
        return $this;
    }

    /**
     * Remove tickets
     *
     * @param Ticket $tickets
     */
    public function removeTicket(Ticket $tickets)
    {
        $this->tickets->removeElement($tickets);
    }

    /**
     * Get tickets
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * @return string
     */
    public function getNameWithCurrency()
    {
        return $this->getAccount()->getName() . ' | ' . $this->getAccount()->getBalance() . ' ( ' . $this->getCurrency() . ' )';
    }

    /**
     * @return string
     */
    public function getNameWithEntity()
    {
        return $this->getAccount()->getName() . ' - ' . $this->getAccount()->getEntity()->getName();
    }

    /**
     * Set vat
     *
     * @param boolean $vat
     * @return Distributor
     */
    public function setVat($vat)
    {
        $this->vat = $vat;
    
        return $this;
    }

    /**
     * Get vat
     *
     * @return boolean 
     */
    public function getVat()
    {
        return $this->vat;
    }
}