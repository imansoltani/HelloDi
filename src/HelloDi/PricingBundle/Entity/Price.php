<?php
namespace HelloDi\PricingBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\DiDistributorsBundle\Entity\Item;
use HelloDi\DiDistributorsBundle\Entity\Tax;

/** 
 * @ORM\Entity
 * @ORM\Table(name="price")
 */
class Price
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** 
     * @ORM\Column(type="decimal", nullable=false, name="price", scale=2)
     */
    private $price;

    /**
     * @ORM\Column(type="boolean", nullable=false, name="isFavourite")
     */
    private $isFavourite;

    /** 
     * @ORM\Column(type="string", length=3, nullable=false, name="price_currency")
     */
    private $priceCurrency;

    /** 
     * @ORM\Column(type="boolean", nullable=false, name="price_status")
     */
    private $priceStatus;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\PricingBundle\Entity\ChangingPrice", mappedBy="Prices")
     */
    private $ChangingPrices;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Tax", inversedBy="Prices")
     * @ORM\JoinColumn(name="tax_id", referencedColumnName="id", nullable=true)
     */
    private $Tax;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Item", inversedBy="Prices")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id", nullable=false)
     */
    private $Item;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\AccountingBundle\Entity\Account", inversedBy="Prices")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     */
    private $Account;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ChangingPrices = new ArrayCollection();
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
     * Set price
     *
     * @param float $price
     * @return Price
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
     * Set priceCurrency
     *
     * @param string $priceCurrency
     * @return Price
     */
    public function setPriceCurrency($priceCurrency)
    {
        $this->priceCurrency = $priceCurrency;
    
        return $this;
    }

    /**
     * Get priceCurrency
     *
     * @return string 
     */
    public function getPriceCurrency()
    {
        return $this->priceCurrency;
    }

    /**
     * Set priceStatus
     *
     * @param boolean $priceStatus
     * @return Price
     */
    public function setPriceStatus($priceStatus)
    {
        $this->priceStatus = $priceStatus;
    
        return $this;
    }

    /**
     * Get priceStatus
     *
     * @return boolean 
     */
    public function getPriceStatus()
    {
        return $this->priceStatus;
    }

    /**
     * Add ChangingPrices
     *
     * @param ChangingPrice $changingPrices
     * @return Price
     */
    public function addChangingPrice(ChangingPrice $changingPrices)
    {
        $this->ChangingPrices[] = $changingPrices;
    
        return $this;
    }

    /**
     * Remove ChangingPrices
     *
     * @param ChangingPrice $changingPrices
     */
    public function removeChangingPrice(ChangingPrice $changingPrices)
    {
        $this->ChangingPrices->removeElement($changingPrices);
    }

    /**
     * Get ChangingPrices
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChangingPrices()
    {
        return $this->ChangingPrices;
    }

    /**
     * Set Item
     *
     * @param Item $item
     * @return Price
     */
    public function setItem(Item $item)
    {
        $this->Item = $item;
    
        return $this;
    }

    /**
     * Get Item
     *
     * @return Item
     */
    public function getItem()
    {
        return $this->Item;
    }

    /**
     * Set Account
     *
     * @param Account $account
     * @return Price
     */
    public function setAccount(Account $account)
    {
        $this->Account = $account;
    
        return $this;
    }

    /**
     * Get Account
     *
     * @return \HelloDi\AccountingBundle\Entity\Account
     */
    public function getAccount()
    {
        return $this->Account;
    }

    /**
     * Set isFavourite
     *
     * @param boolean $isFavourite
     * @return Price
     */
    public function setIsFavourite($isFavourite)
    {
        $this->isFavourite = $isFavourite;
    
        return $this;
    }

    /**
     * Get isFavourite
     *
     * @return boolean 
     */
    public function getIsFavourite()
    {
        return $this->isFavourite;
    }

    /**
     * Set Tax
     *
     * @param Tax $tax
     * @return Price
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

    public function __toString()
    {
        return $this->getItem()->getItemName()." - ".$this->getPrice()." ".$this->getPriceCurrency();
    }
}