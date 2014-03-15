<?php

namespace HelloDi\DiDistributorsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use HelloDi\AccountingBundle\Entity\Account;

/**
 * Provider
 *
 * @ORM\Table(name="provider")
 * @ORM\Entity(repositoryClass="HelloDi\DiDistributorsBundle\Entity\ProviderRepository")
 */
class Provider
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
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Input", mappedBy="Account")
     */
    private $inputs;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\AccountingBundle\Entity\Account", inversedBy="provider")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     */
    private $account;

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
     * Constructor
     */
    public function __construct()
    {
        $this->inputs = new ArrayCollection();
    }
    
    /**
     * Set currency
     *
     * @param string $currency
     * @return Provider
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
     * @return Provider
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
     * Add inputs
     *
     * @param Input $inputs
     * @return Provider
     */
    public function addInput(Input $inputs)
    {
        $this->inputs[] = $inputs;
    
        return $this;
    }

    /**
     * Remove inputs
     *
     * @param Input $inputs
     */
    public function removeInput(Input $inputs)
    {
        $this->inputs->removeElement($inputs);
    }

    /**
     * Get inputs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInputs()
    {
        return $this->inputs;
    }

    /**
     * Set account
     *
     * @param Account $account
     * @return Provider
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