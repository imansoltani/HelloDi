<?php
namespace HelloDi\PricingBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\CoreBundle\Entity\Item;
use HelloDi\CoreBundle\Entity\Tax;

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
    protected $id;

    /**
     * @ORM\Column(type="decimal", nullable=false, name="price", scale=2)
     */
    protected $price;

    /**
     * @ORM\Column(type="boolean", nullable=false, name="favourite")
     */
    protected $favourite = false;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\PricingBundle\Entity\ChangingPrice", mappedBy="prices")
     */
    protected $changingPrices;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\CoreBundle\Entity\Tax", inversedBy="prices")
     * @ORM\JoinColumn(name="tax_id", referencedColumnName="id", nullable=true)
     */
    protected $tax;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\CoreBundle\Entity\Item", inversedBy="prices")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id", nullable=false)
     */
    protected $item;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\AccountingBundle\Entity\Account", inversedBy="prices")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     */
    protected $account;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->changingPrices = new ArrayCollection();
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
     * Set favourite
     *
     * @param boolean $favourite
     * @return Price
     */
    public function setFavourite($favourite)
    {
        $this->favourite = $favourite;

        return $this;
    }

    /**
     * Get favourite
     *
     * @return boolean
     */
    public function getFavourite()
    {
        return $this->favourite;
    }

    /**
     * Add changingPrices
     *
     * @param ChangingPrice $changingPrices
     * @return Price
     */
    public function addChangingPrice(ChangingPrice $changingPrices)
    {
        $this->changingPrices[] = $changingPrices;

        return $this;
    }

    /**
     * Remove changingPrices
     *
     * @param ChangingPrice $changingPrices
     */
    public function removeChangingPrice(ChangingPrice $changingPrices)
    {
        $this->changingPrices->removeElement($changingPrices);
    }

    /**
     * Get changingPrices
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChangingPrices()
    {
        return $this->changingPrices;
    }

    /**
     * Set Tax
     *
     * @param Tax $tax
     * @return Price
     */
    public function setTax(Tax $tax = null)
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * Get Tax
     *
     * @return Tax
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * Set Item
     *
     * @param Item $item
     * @return Price
     */
    public function setItem(Item $item)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get Item
     *
     * @return Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set account
     *
     * @param Account $account
     * @return Price
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getItem()->getName() . " - " . $this->getPrice();
    }
}