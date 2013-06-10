<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

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
     * @ORM\Column(type="decimal", nullable=false, name="price")
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
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\ChangingPrice", mappedBy="Prices")
     */
    private $ChangingPrices;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\PriceHistory", mappedBy="Prices")
     */
    private $PricesHistory;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Item", inversedBy="Prices")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id", nullable=false)
     */
    private $Item;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Account", inversedBy="Prices")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     */
    private $Account;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ChangingPrices = new \Doctrine\Common\Collections\ArrayCollection();
        $this->PricesHistory = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param \HelloDi\DiDistributorsBundle\Entity\ChangingPrice $changingPrices
     * @return Price
     */
    public function addChangingPrice(\HelloDi\DiDistributorsBundle\Entity\ChangingPrice $changingPrices)
    {
        $this->ChangingPrices[] = $changingPrices;
    
        return $this;
    }

    /**
     * Remove ChangingPrices
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\ChangingPrice $changingPrices
     */
    public function removeChangingPrice(\HelloDi\DiDistributorsBundle\Entity\ChangingPrice $changingPrices)
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
     * Add PricesHistory
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\PriceHistory $pricesHistory
     * @return Price
     */
    public function addPricesHistory(\HelloDi\DiDistributorsBundle\Entity\PriceHistory $pricesHistory)
    {
        $this->PricesHistory[] = $pricesHistory;
    
        return $this;
    }

    /**
     * Remove PricesHistory
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\PriceHistory $pricesHistory
     */
    public function removePricesHistory(\HelloDi\DiDistributorsBundle\Entity\PriceHistory $pricesHistory)
    {
        $this->PricesHistory->removeElement($pricesHistory);
    }

    /**
     * Get PricesHistory
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPricesHistory()
    {
        return $this->PricesHistory;
    }

    /**
     * Set Item
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Item $item
     * @return Price
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

    /**
     * Set Account
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Account $account
     * @return Price
     */
    public function setAccount(\HelloDi\DiDistributorsBundle\Entity\Account $account)
    {
        $this->Account = $account;
    
        return $this;
    }

    /**
     * Get Account
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\Account 
     */
    public function getAccount()
    {
        return $this->Account;
    }

    public function __toString()
    {
        return $this->getItem()->getItemName()." - ".$this->getPrice()." ".$this->getPriceCurrency();
    }
}