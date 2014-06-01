<?php
namespace HelloDi\CoreBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as Unique;

/**
 * @ORM\Entity
 * @ORM\Table(name="denomination")
 * @Unique\UniqueEntity(fields={"currency","item"}, message="currency is duplicate.")
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
     * @ORM\ManyToOne(targetEntity="HelloDi\CoreBundle\Entity\Item", inversedBy="denominations")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id", nullable=false)
     */
    private $item;

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
     * Set item
     *
     * @param Item $item
     * @return Denomination
     */
    public function setItem(Item $item)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item
     *
     * @return Item
     */
    public function getItem()
    {
        return $this->item;
    }
}