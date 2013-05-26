<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/** 
 * @ORM\Entity
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
     * @ORM\Column(type="string", length=45, nullable=false, name="item_face_value")
     */
    private $itemFaceValue;

    /** 
     * @ORM\Column(type="string", length=3, nullable=false, name="item_currency")
     */
    private $itemCurrency;

    /** 
     * @ORM\Column(type="smallint", nullable=false, name="item_type")
     */
    private $itemType;

    /** 
     * @ORM\Column(type="integer", nullable=false, name="alert_min_stock")
     */
    private $alertMinStock;

    /** 
     * @ORM\Column(type="string", length=45, nullable=false, name="operator")
     */
    private $operator;

    /** 
     * @ORM\Column(type="string", length=45, nullable=true, name="item_code")
     */
    private $itemCode;

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
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Price", mappedBy="Item")
     */
    private $Prices;

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
     * @param string $itemFaceValue
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
     * @return string 
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
     * @param string $operator
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
     * @return string 
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
     * @param \HelloDi\DiDistributorsBundle\Entity\Price $prices
     * @return Item
     */
    public function addPrice(\HelloDi\DiDistributorsBundle\Entity\Price $prices)
    {
        $this->Prices[] = $prices;
    
        return $this;
    }

    /**
     * Remove Prices
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Price $prices
     */
    public function removePrice(\HelloDi\DiDistributorsBundle\Entity\Price $prices)
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
}