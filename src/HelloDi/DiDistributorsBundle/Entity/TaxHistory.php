<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/** 
 * @ORM\Entity
 * @ORM\Table(name="taxhistory")
 */
class TaxHistory
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime",nullable=true, name="Tax_End")
     */
    private $taxend;

    /**
     * @ORM\Column(type="float", nullable=false, name="tax")
     */
    private $vat;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Tax", inversedBy="TaxHistories")
     * @ORM\JoinColumn(name="tax_id", referencedColumnName="id", nullable=true)
     */
    private $Tax;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Distributor", mappedBy="taxHistory")
     */
    private $Distributors;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->Distributors = new ArrayCollection();
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
     * Set taxend
     *
     * @param \DateTime $taxend
     * @return TaxHistory
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
     * Set vat
     *
     * @param float $vat
     * @return TaxHistory
     */
    public function setVat($vat)
    {
        $this->vat = $vat;
    
        return $this;
    }

    /**
     * Get vat
     *
     * @return float 
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * Set Tax
     *
     * @param Tax $tax
     * @return TaxHistory
     */
    public function setTax(Tax $tax = null)
    {
        $this->Tax = $tax;
    
        return $this;
    }

    /**
     * Get Tax
     *
     * @return Tax
     */
    public function getTax()
    {
        return $this->Tax;
    }
    
    /**
     * Add Distributors
     *
     * @param Distributor $distributors
     * @return TaxHistory
     */
    public function addDistributor(Distributor $distributors)
    {
        $this->Distributors[] = $distributors;
    
        return $this;
    }

    /**
     * Remove Distributors
     *
     * @param Distributor $distributors
     */
    public function removeDistributor(Distributor $distributors)
    {
        $this->Distributors->removeElement($distributors);
    }

    /**
     * Get Distributors
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDistributors()
    {
        return $this->Distributors;
    }
}