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
     * @ORM\Column(type="float", nullable=false, name="tax")
     */
    private $tax;


    /**
     * @ORM\Column(type="datetime", nullable=true, name="Tax_End")
     */
    private $taxend;


    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Country", inversedBy="Taxs")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=true)
     */
    private $Country;


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
}