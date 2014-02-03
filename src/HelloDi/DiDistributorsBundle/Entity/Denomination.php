<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/** 
 * @ORM\Entity
 * @ORM\Table(name="denomination")
 */
class Denomination
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="decimal", nullable=false, name="denomination", scale=2)
     */
    private $denomination;

    /**
     * @ORM\Column(type="string", length=3, nullable=false, name="currency")
     */
    private $currency;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Item", inversedBy="Denominations")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id", nullable=false)
     */
    private $Item;

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
     * Set denomination
     *
     * @param float $denomination
     * @return Denomination
     */
    public function setDenomination($denomination)
    {
        $this->denomination = $denomination;
    
        return $this;
    }

    /**
     * Get denomination
     *
     * @return float 
     */
    public function getDenomination()
    {
        return $this->denomination;
    }

    /**
     * Set currency
     *
     * @param string $currency
     * @return Denomination
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
     * Set Item
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Item $item
     * @return Denomination
     */
    public function setItem(\HelloDi\DiDistributorsBundle\Entity\Item $item)
    {
        $this->Item = $item;
    
        return $this;
    }

    /**
     * Get Item
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\Item 
     */
    public function getItem()
    {
        return $this->Item;
    }
}