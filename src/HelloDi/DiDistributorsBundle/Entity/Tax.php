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
     * @ORM\OneToMany(targetEntity="HelloDi\PricingBundle\Entity\Price", mappedBy="Tax")
     */
    private $Prices;

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
        $this->TaxHistories=  new \Doctrine\Common\Collections\ArrayCollection();
        $this->Prices = new \Doctrine\Common\Collections\ArrayCollection();

    }

    /**
     * Add TaxHistorys
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\TaxHistory $taxHistorys
     * @return Tax
     */
    public function addTaxHistory(\HelloDi\DiDistributorsBundle\Entity\TaxHistory $taxHistorys)
    {
        $this->TaxHistories[] = $taxHistorys;
    
        return $this;
    }

    /**
     * Remove TaxHistorys
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\TaxHistory $taxHistorys
     */
    public function removeTaxHistory(\HelloDi\DiDistributorsBundle\Entity\TaxHistory $taxHistorys)
    {
        $this->TaxHistories->removeElement($taxHistorys);
    }

    /**
     * Get TaxHistorys
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTaxHistorys()
    {
        return $this->TaxHistories;
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

    /**
     * Add Prices
     *
     * @param \HelloDi\PricingBundle\Entity\Price $prices
     * @return Tax
     */
    public function addPrice(\HelloDi\PricingBundle\Entity\Price $prices)
    {
        $this->Prices[] = $prices;
    
        return $this;
    }

    /**
     * Remove Prices
     *
     * @param \HelloDi\PricingBundle\Entity\Price $prices
     */
    public function removePrice(\HelloDi\PricingBundle\Entity\Price $prices)
    {
        $this->Prices->removeElement($prices);
    }

    /**
     * Get Prices
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPrices()
    {
        return $this->Prices;
    }
}