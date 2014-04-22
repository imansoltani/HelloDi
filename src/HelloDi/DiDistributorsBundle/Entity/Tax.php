<?php
namespace HelloDi\DiDistributorsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use HelloDi\PricingBundle\Entity\Price;

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
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Country", inversedBy="taxes")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=true)
     */
    private $country;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\PricingBundle\Entity\Price", mappedBy="tax")
     */
    private $prices;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\TaxHistory", mappedBy="tax")
     */
    private $taxHistories;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->taxHistories = new ArrayCollection();
        $this->prices = new ArrayCollection();
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
     * Set country
     *
     * @param Country $country
     * @return Tax
     */
    public function setCountry(Country $country = null)
    {
        $this->country = $country;
    
        return $this;
    }

    /**
     * Get country
     *
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Add prices
     *
     * @param Price $prices
     * @return Tax
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
     * Add taxHistories
     *
     * @param TaxHistory $taxHistories
     * @return Tax
     */
    public function addTaxHistorie(TaxHistory $taxHistories)
    {
        $this->taxHistories[] = $taxHistories;
    
        return $this;
    }

    /**
     * Remove taxHistories
     *
     * @param TaxHistory $taxHistories
     */
    public function removeTaxHistorie(TaxHistory $taxHistories)
    {
        $this->taxHistories->removeElement($taxHistories);
    }

    /**
     * Get taxHistories
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTaxHistories()
    {
        return $this->taxHistories;
    }
}