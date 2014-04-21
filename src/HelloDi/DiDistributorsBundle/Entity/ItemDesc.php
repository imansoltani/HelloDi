<?php
namespace HelloDi\DiDistributorsBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="item_desc")
 */
class ItemDesc
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=false, name="desc_desc")
     */
    private $descDesc;

    /**
     * @ORM\Column(type="string", length=2, nullable=false, name="desc_lang")
     */
    private $descLang;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Item", inversedBy="itemDescriptions")
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
     * Set descDesc
     *
     * @param string $descDesc
     * @return ItemDesc
     */
    public function setDescDesc($descDesc)
    {
        $this->descDesc = $descDesc;

        return $this;
    }

    /**
     * Get descDesc
     *
     * @return string
     */
    public function getDescDesc()
    {
        return $this->descDesc;
    }

    /**
     * Set descLang
     *
     * @param string $descLang
     * @return ItemDesc
     */
    public function setDescLang($descLang)
    {
        $this->descLang = $descLang;

        return $this;
    }

    /**
     * Get descLang
     *
     * @return string
     */
    public function getDescLang()
    {
        return $this->descLang;
    }

    /**
     * Set item
     *
     * @param Item $item
     * @return ItemDesc
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