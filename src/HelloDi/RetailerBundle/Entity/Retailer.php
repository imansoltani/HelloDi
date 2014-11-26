<?php

namespace HelloDi\RetailerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\CoreBundle\Entity\Ticket;
use HelloDi\DistributorBundle\Entity\Distributor;

/**
 * Retailer
 *
 * @ORM\Table(name="retailer")
 * @ORM\Entity(repositoryClass="HelloDi\RetailerBundle\Entity\RetailerRepository")
 */
class Retailer
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
     * @ORM\ManyToOne(targetEntity="HelloDi\DistributorBundle\Entity\Distributor", inversedBy="retailers")
     * @ORM\JoinColumn(name="distributor_id", referencedColumnName="id", nullable=false)
     */
    protected $distributor;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\AccountingBundle\Entity\Account")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     */
    protected $account;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\CoreBundle\Entity\Ticket", mappedBy="retailer")
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
     * Set distributor
     *
     * @param Distributor $distributor
     * @return Retailer
     */
    public function setDistributor(Distributor $distributor)
    {
        $this->distributor = $distributor;

        return $this;
    }

    /**
     * Get distributor
     *
     * @return Distributor
     */
    public function getDistributor()
    {
        return $this->distributor;
    }

    /**
     * Set account
     *
     * @param Account $account
     * @return Retailer
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
     * @return Retailer
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
        return $this->getAccount()->getName() . ' | ' . $this->getAccount()->getBalance() . ' ( ' . $this->getDistributor()->getCurrency() . ' )';
    }

    /**
     * Set vat
     *
     * @param boolean $vat
     * @return Retailer
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