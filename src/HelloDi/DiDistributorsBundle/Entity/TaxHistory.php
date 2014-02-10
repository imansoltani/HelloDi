<?php
namespace HelloDi\DiDistributorsBundle\Entity;
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
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Pin", mappedBy="taxHistory")
     */
    private $pins;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->pins = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param \HelloDi\DiDistributorsBundle\Entity\Tax $tax
     * @return TaxHistory
     */
    public function setTax(\HelloDi\DiDistributorsBundle\Entity\Tax $tax = null)
    {
        $this->Tax = $tax;
    
        return $this;
    }

    /**
     * Get Tax
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\Tax 
     */
    public function getTax()
    {
        return $this->Tax;
    }

    /**
     * Add pins
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Pin $pins
     * @return TaxHistory
     */
    public function addPin(\HelloDi\DiDistributorsBundle\Entity\Pin $pins)
    {
        $this->pins[] = $pins;
    
        return $this;
    }

    /**
     * Remove pins
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Pin $pins
     */
    public function removePin(\HelloDi\DiDistributorsBundle\Entity\Pin $pins)
    {
        $this->pins->removeElement($pins);
    }

    /**
     * Get pins
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPins()
    {
        return $this->pins;
    }
}