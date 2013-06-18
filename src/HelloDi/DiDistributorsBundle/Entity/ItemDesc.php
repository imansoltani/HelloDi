<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/** 
 * @ORM\Entity
 * @ORM\Table(name="itemdesc")
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
    private $descdesc;

    /** 
     * @ORM\Column(type="string", length=2, nullable=false, name="desc_lang")
     */
    private $desclang;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Item", inversedBy="ItemDescs")
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
     * Set descdesc
     *
     * @param string $descdesc
     * @return ItemDesc
     */
    public function setDescdesc($descdesc)
    {
        $this->descdesc = $descdesc;
    
        return $this;
    }

    /**
     * Get descdesc
     *
     * @return string 
     */
    public function getDescdesc()
    {
        return $this->descdesc;
    }

    /**
     * Set desclang
     *
     * @param string $desclang
     * @return ItemDesc
     */
    public function setDesclang($desclang)
    {
        $this->desclang = $desclang;
    
        return $this;
    }

    /**
     * Get desclang
     *
     * @return string 
     */
    public function getDesclang()
    {
        return $this->desclang;
    }

    /**
     * Set Item
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Item $item
     * @return ItemDesc
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