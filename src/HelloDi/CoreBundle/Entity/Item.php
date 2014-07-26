<?php
namespace HelloDi\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use HelloDi\PricingBundle\Entity\Price;
use Symfony\Bridge\Doctrine\Validator\Constraints as Unique;

/**
 * @ORM\Entity
 * @ORM\Table(name="item")
 * @Unique\UniqueEntity(fields="code", message="This_item_code_already_exist")
 */
class Item
{
    const DMTU = 'dmtu';
    const IMTU = 'imtu';
    const CLCD = 'clcd';
    const EPMT = 'epmt';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=45, nullable=false, name="name")
     */
    private $name;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=false, name="face_value")
     */
    private $faceValue;

    /**
     * @ORM\Column(type="string", length=3, nullable=false, name="currency")
     */
    private $currency;

    /**
     * @ORM\Column(type="string", length=5, nullable=false, name="type")
     */
    private $type;

    /**
     * @ORM\Column(type="integer", nullable=false, name="alert_min_stock")
     */
    private $alertMinStock;

    /**
     * @ORM\Column(type="string", length=100, name="code", unique = true)
     */
    private $code;

    /**
     * @ORM\Column(type="date", nullable=false, name="date_insert")
     */
    private $dateInsert;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\CoreBundle\Entity\Operator", inversedBy="item")
     * @ORM\JoinColumn(name="Operator_id", referencedColumnName="id", nullable=false)
     */
    private $operator;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\CoreBundle\Entity\Code", mappedBy="item")
     */
    private $codes;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\CoreBundle\Entity\Input", mappedBy="item")
     */
    private $inputs;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\CoreBundle\Entity\ItemDesc", mappedBy="item")
     */
    private $descriptions;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\PricingBundle\Entity\Price", mappedBy="item")
     */
    private $prices;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\CoreBundle\Entity\B2BLog", mappedBy="item")
     */
    private $b2bLogs;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\CoreBundle\Entity\Country")
     * @ORM\JoinColumn(name="Country_id", referencedColumnName="id", nullable=false)
     */
    private $country;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->codes = new ArrayCollection();
        $this->inputs = new ArrayCollection();
        $this->descriptions = new ArrayCollection();
        $this->prices = new ArrayCollection();
        $this->b2bLogs = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Item
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set faceValue
     *
     * @param float $faceValue
     * @return Item
     */
    public function setFaceValue($faceValue)
    {
        $this->faceValue = $faceValue;

        return $this;
    }

    /**
     * Get faceValue
     *
     * @return float
     */
    public function getFaceValue()
    {
        return $this->faceValue;
    }

    /**
     * Set currency
     *
     * @param string $currency
     * @return Item
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
     * Set type
     *
     * @param string $type
     * @return Item
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
     * Set code
     *
     * @param string $code
     * @return Item
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set dateInsert
     *
     * @param \DateTime $dateInsert
     * @return Item
     */
    public function setDateInsert($dateInsert)
    {
        $this->dateInsert = $dateInsert;

        return $this;
    }

    /**
     * Get dateInsert
     *
     * @return \DateTime
     */
    public function getDateInsert()
    {
        return $this->dateInsert;
    }

    /**
     * Set operator
     *
     * @param Operator $operator
     * @return Item
     */
    public function setOperator(Operator $operator)
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * Get operator
     *
     * @return Operator
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Add codes
     *
     * @param Code $codes
     * @return Item
     */
    public function addCode(Code $codes)
    {
        $this->codes[] = $codes;

        return $this;
    }

    /**
     * Remove codes
     *
     * @param Code $codes
     */
    public function removeCode(Code $codes)
    {
        $this->codes->removeElement($codes);
    }

    /**
     * Get codes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCodes()
    {
        return $this->codes;
    }

    /**
     * Add inputs
     *
     * @param Input $inputs
     * @return Item
     */
    public function addInput(Input $inputs)
    {
        $this->inputs[] = $inputs;

        return $this;
    }

    /**
     * Remove inputs
     *
     * @param Input $inputs
     */
    public function removeInput(Input $inputs)
    {
        $this->inputs->removeElement($inputs);
    }

    /**
     * Get inputs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInputs()
    {
        return $this->inputs;
    }

    /**
     * Add descriptions
     *
     * @param ItemDesc $descriptions
     * @return Item
     */
    public function addDescription(ItemDesc $descriptions)
    {
        $this->descriptions[] = $descriptions;

        return $this;
    }

    /**
     * Remove descriptions
     *
     * @param ItemDesc $descriptions
     */
    public function removeDescription(ItemDesc $descriptions)
    {
        $this->descriptions->removeElement($descriptions);
    }

    /**
     * Get descriptions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDescriptions()
    {
        return $this->descriptions;
    }

    /**
     * Add prices
     *
     * @param Price $prices
     * @return Item
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
     * Add b2bLogs
     *
     * @param B2BLog $b2bLogs
     * @return Item
     */
    public function addB2bLog(B2BLog $b2bLogs)
    {
        $this->b2bLogs[] = $b2bLogs;

        return $this;
    }

    /**
     * Remove b2bLogs
     *
     * @param B2BLog $b2bLogs
     */
    public function removeB2bLog(B2BLog $b2bLogs)
    {
        $this->b2bLogs->removeElement($b2bLogs);
    }

    /**
     * Get b2bLogs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getB2bLogs()
    {
        return $this->b2bLogs;
    }

    /**
     * Set country
     *
     * @param Country $country
     * @return Item
     */
    public function setCountry(Country $country)
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
}