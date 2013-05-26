<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/** 
 * @ORM\Entity
 */
class PriceHistory
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** 
     * @ORM\Column(type="date", nullable=false, name="date")
     */
    private $date;

    /** 
     * @ORM\Column(type="decimal", nullable=false, name="price")
     */
    private $price;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Price", inversedBy="PricesHistory")
     * @ORM\JoinColumn(name="Price_id", referencedColumnName="id", nullable=false)
     */
    private $Prices;

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
     * Set date
     *
     * @param \DateTime $date
     * @return PriceHistory
     */
    public function setDate($date)
    {
        $this->date = $date;
    
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return PriceHistory
     */
    public function setPrice($price)
    {
        $this->price = $price;
    
        return $this;
    }

    /**
     * Get price
     *
     * @return float 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set Prices
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Price $prices
     * @return PriceHistory
     */
    public function setPrices(\HelloDi\DiDistributorsBundle\Entity\Price $prices)
    {
        $this->Prices = $prices;
    
        return $this;
    }

    /**
     * Get Prices
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\Price 
     */
    public function getPrices()
    {
        return $this->Prices;
    }
}