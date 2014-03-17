<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/** 
 * @ORM\Entity
 * @ORM\Table(name="item")
 */
class Item
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** 
     * @ORM\Column(type="string", length=45, nullable=false, name="item_name")
     */
    private $itemName;

    /** 
     * @ORM\Column(type="decimal", scale=2, nullable=false, name="item_face_value")
     */
    private $itemFaceValue;

    /** 
     * @ORM\Column(type="string", length=3, nullable=false, name="item_currency")
     */
    private $itemCurrency;

    /** 
     * @ORM\Column(type="string", length=5, nullable=false, name="item_type")
     */
    private $itemType;

    /** 
     * @ORM\Column(type="integer", nullable=false, name="alert_min_stock")
     */
    private $alertMinStock;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Operator", inversedBy="Item")
     * @ORM\JoinColumn(name="Operator_id", referencedColumnName="id", nullable=false)
     */
    private $operator;

    /** 
     * @ORM\Column(type="string", length=100, name="item_code", unique = true)
     */
    private $itemCode;

    /**
     * @ORM\Column(type="date", nullable=false, name="item_date_insert")
     */
    private $itemDateInsert;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Code", mappedBy="Item")
     */
    private $Codes;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Input", mappedBy="Item")
     */
    private $Inputs;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\ItemDesc", mappedBy="Item")
     */
    private $ItemDescs;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\PricingBundle\Entity\Price", mappedBy="Item")
     */
    private $Prices;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\B2BLog", mappedBy="Item")
     */
    private $B2BLogs;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Denomination", mappedBy="Item")
     */
    private $Denominations;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Country", inversedBy="Items")
     * @ORM\JoinColumn(name="Country_id", referencedColumnName="id", nullable=false)
     */
    private $Country;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->Codes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->Inputs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->ItemDescs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->Prices = new \Doctrine\Common\Collections\ArrayCollection();
        $this->B2BLogs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->Denominations = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set itemName
     *
     * @param string $itemName
     * @return Item
     */
    public function setItemName($itemName)
    {
        $this->itemName = $itemName;
    
        return $this;
    }

    /**
     * Get itemName
     *
     * @return string 
     */
    public function getItemName()
    {
        return $this->itemName;
    }

    /**
     * Set itemFaceValue
     *
     * @param float $itemFaceValue
     * @return Item
     */
    public function setItemFaceValue($itemFaceValue)
    {
        $this->itemFaceValue = $itemFaceValue;
    
        return $this;
    }

    /**
     * Get itemFaceValue
     *
     * @return float
     */
    public function getItemFaceValue()
    {
        return $this->itemFaceValue;
    }

    /**
     * Set itemCurrency
     *
     * @param string $itemCurrency
     * @return Item
     */
    public function setItemCurrency($itemCurrency)
    {
        $this->itemCurrency = $itemCurrency;
    
        return $this;
    }

    /**
     * Get itemCurrency
     *
     * @return string 
     */
    public function getItemCurrency()
    {
        return $this->itemCurrency;
    }

    /**
     * Set itemType
     *
     * @param integer $itemType
     * @return Item
     */
    public function setItemType($itemType)
    {
        $this->itemType = $itemType;
    
        return $this;
    }

    /**
     * Get itemType
     *
     * @return integer 
     */
    public function getItemType()
    {
        return $this->itemType;
    }

    /**
     * Set alertMinStock
     *
     * @param integer $alertMinStock
     * @return Item
     */
    public function setAlertMinStock($alertMinStock)
    {
        $this->alertMinStock = $alertMinStock;
    
        return $this;
    }

    /**
     * Get alertMinStock
     *
     * @return integer 
     */
    public function getAlertMinStock()
    {
        return $this->alertMinStock;
    }

    /**
     * Set operator
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Operator $operator
     * @return Item
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
    
        return $this;
    }

    /**
     * Get operator
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\Operator
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Set itemCode
     *
     * @param string $itemCode
     * @return Item
     */
    public function setItemCode($itemCode)
    {
        $this->itemCode = $itemCode;
    
        return $this;
    }

    /**
     * Get itemCode
     *
     * @return string 
     */
    public function getItemCode()
    {
        return $this->itemCode;
    }

    /**
     * Add Codes
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Code $codes
     * @return Item
     */
    public function addCode(\HelloDi\DiDistributorsBundle\Entity\Code $codes)
    {
        $this->Codes[] = $codes;
    
        return $this;
    }

    /**
     * Remove Codes
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Code $codes
     */
    public function removeCode(\HelloDi\DiDistributorsBundle\Entity\Code $codes)
    {
        $this->Codes->removeElement($codes);
    }

    /**
     * Get Codes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCodes()
    {
        return $this->Codes;
    }

    /**
     * Add Inputs
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Input $inputs
     * @return Item
     */
    public function addInput(\HelloDi\DiDistributorsBundle\Entity\Input $inputs)
    {
        $this->Inputs[] = $inputs;
    
        return $this;
    }

    /**
     * Remove Inputs
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Input $inputs
     */
    public function removeInput(\HelloDi\DiDistributorsBundle\Entity\Input $inputs)
    {
        $this->Inputs->removeElement($inputs);
    }

    /**
     * Get Inputs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInputs()
    {
        return $this->Inputs;
    }

    /**
     * Add ItemDescs
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\ItemDesc $itemDescs
     * @return Item
     */
    public function addItemDesc(\HelloDi\DiDistributorsBundle\Entity\ItemDesc $itemDescs)
    {
        $this->ItemDescs[] = $itemDescs;
    
        return $this;
    }

    /**
     * Remove ItemDescs
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\ItemDesc $itemDescs
     */
    public function removeItemDesc(\HelloDi\DiDistributorsBundle\Entity\ItemDesc $itemDescs)
    {
        $this->ItemDescs->removeElement($itemDescs);
    }

    /**
     * Get ItemDescs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getItemDescs()
    {
        return $this->ItemDescs;
    }

    /**
     * Add Prices
     *
     * @param \HelloDi\PricingBundle\Entity\Price $prices
     * @return Item
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

    /**
     * Set Country
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Country $country
     * @return Item
     */
    public function setCountry(\HelloDi\DiDistributorsBundle\Entity\Country $country)
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
     * Set itemDateInsert
     *
     * @param \DateTime $itemDateInsert
     * @return Item
     */
    public function setItemDateInsert($itemDateInsert)
    {
        $this->itemDateInsert = $itemDateInsert;
    
        return $this;
    }

    /**
     * Get itemDateInsert
     *
     * @return \DateTime 
     */
    public function getItemDateInsert()
    {
        return $this->itemDateInsert;
    }

    /**
     * Add B2BLogs
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\B2BLog $b2BLogs
     * @return Item
     */
    public function addB2BLog(\HelloDi\DiDistributorsBundle\Entity\B2BLog $b2BLogs)
    {
        $this->B2BLogs[] = $b2BLogs;
    
        return $this;
    }

    /**
     * Remove B2BLogs
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\B2BLog $b2BLogs
     */
    public function removeB2BLog(\HelloDi\DiDistributorsBundle\Entity\B2BLog $b2BLogs)
    {
        $this->B2BLogs->removeElement($b2BLogs);
    }

    /**
     * Get B2BLogs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getB2BLogs()
    {
        return $this->B2BLogs;
    }

    function __toString()
    {
        return $this->getItemName();
    }

    /**
     * Add Denominations
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Denomination $denominations
     * @return Item
     */
    public function addDenomination(\HelloDi\DiDistributorsBundle\Entity\Denomination $denominations)
    {
        $this->Denominations[] = $denominations;
    
        return $this;
    }

    /**
     * Remove Denominations
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Denomination $denominations
     */
    public function removeDenomination(\HelloDi\DiDistributorsBundle\Entity\Denomination $denominations)
    {
        $this->Denominations->removeElement($denominations);
    }

    /**
     * Get Denominations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDenominations()
    {
        return $this->Denominations;
    }

    /**
     * @param $currency
     * @return float
     */
    public function getDenominationByCurrency($currency)
    {
        foreach($this->Denominations as $denomination)
            if($denomination->getCurrency() == $currency)
                return $denomination->getDenomination();

        return null;
    }
}