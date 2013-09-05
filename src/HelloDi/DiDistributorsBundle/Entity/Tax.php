<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/** 
 * @ORM\Entity
 * @ORM\Table(name="tax")
 */
class Tax
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;



    /**
     * @ORM\Column(type="float", nullable=false, name="tax",nullable=true)
     */
    private $tax;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Country", inversedBy="Taxs")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=true)
     */
    private $Country;


    /**
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\TaxHistory", mappedBy="Tax")
     */
    private $TaxHistories;


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
     * Set tax
     *
     * @param float $tax
     * @return Tax
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
    
        return $this;
    }

    /**
     * Get tax
     *
     * @return float 
     */
    public function getTax()
    {
        return $this->tax;
    }


    /**
     * Set taxend
     *
     * @param \DateTime $taxend
     * @return Tax
     */
    public function setTaxend($taxend)
    {
        $this->taxend = $taxend;
    
        return $this;
    }

    /**
     * Get taxend
     *
     * @return \DateTime 
     */
    public function getTaxend()
    {
        return $this->taxend;
    }

    /**
     * Set Country
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Country $country
     * @return Tax
     */
    public function setCountry(\HelloDi\DiDistributorsBundle\Entity\Country $country = null)
    {
        $this->Country = $country;
    
        return $this;
    }

    /**
     * Get Country
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\Country 
     */
    public function getCountry()
    {
        return $this->Country;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->Transactions = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add Transactions
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Transaction $transactions
     * @return Tax
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
     * Add TaxHistorys
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\TaxHistory $taxHistorys
     * @return Tax
     */
    public function addTaxHistory(\HelloDi\DiDistributorsBundle\Entity\TaxHistory $taxHistorys)
    {
        $this->TaxHistorys[] = $taxHistorys;
    
        return $this;
    }

    /**
     * Remove TaxHistorys
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\TaxHistory $taxHistorys
     */
    public function removeTaxHistory(\HelloDi\DiDistributorsBundle\Entity\TaxHistory $taxHistorys)
    {
        $this->TaxHistorys->removeElement($taxHistorys);
    }

    /**
     * Get TaxHistorys
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTaxHistorys()
    {
        return $this->TaxHistorys;
    }

    /**
     * Add TaxHistories
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\TaxHistory $taxHistories
     * @return Tax
     */
    public function addTaxHistorie(\HelloDi\DiDistributorsBundle\Entity\TaxHistory $taxHistories)
    {
        $this->TaxHistories[] = $taxHistories;
    
        return $this;
    }

    /**
     * Remove TaxHistories
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\TaxHistory $taxHistories
     */
    public function removeTaxHistorie(\HelloDi\DiDistributorsBundle\Entity\TaxHistory $taxHistories)
    {
        $this->TaxHistories->removeElement($taxHistories);
    }

    /**
     * Get TaxHistories
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTaxHistories()
    {
        return $this->TaxHistories;
    }
}