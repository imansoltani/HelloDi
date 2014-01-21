<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/** 
 * @ORM\Entity
 * @ORM\Table(name="changingprice")
 */
class ChangingPrice
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** 
     * @ORM\Column(type="decimal", nullable=false, scale=2)
     */
    private $price;

    /** 
     * @ORM\Column(type="date", nullable=false)
     */
    private $changeDate;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Price", inversedBy="ChangingPrices")
     * @ORM\JoinColumn(name="price_id", referencedColumnName="id", nullable=false)
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
     * Set price
     *
     * @param float $price
     * @return ChangingPrice
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
     * Set changeDate
     *
     * @param \DateTime $changeDate
     * @return ChangingPrice
     */
    public function setChangeDate($changeDate)
    {
        $this->changeDate = $changeDate;
    
        return $this;
    }

    /**
     * Get changeDate
     *
     * @return \DateTime 
     */
    public function getChangeDate()
    {
        return $this->changeDate;
    }

    /**
     * Set Prices
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Price $prices
     * @return ChangingPrice
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