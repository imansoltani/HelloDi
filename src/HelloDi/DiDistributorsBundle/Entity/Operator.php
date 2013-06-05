<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="Operator")
 */
class Operator
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=45, nullable=false, name="Name")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=45, nullable=True, name="operator_logo")
     */
    private $Logo;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Item", mappedBy="Operator")
     */
    private $Item;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->Item = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Operator
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
     * Set Logo
     *
     * @param string $logo
     * @return Operator
     */
    public function setLogo($logo)
    {
        $this->Logo = $logo;
    
        return $this;
    }

    /**
     * Get Logo
     *
     * @return string 
     */
    public function getLogo()
    {
        return $this->Logo;
    }

    /**
     * Add Item
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Item $item
     * @return Operator
     */
    public function addItem(\HelloDi\DiDistributorsBundle\Entity\Item $item)
    {
        $this->Item[] = $item;
    
        return $this;
    }

    /**
     * Remove Item
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Item $item
     */
    public function removeItem(\HelloDi\DiDistributorsBundle\Entity\Item $item)
    {
        $this->Item->removeElement($item);
    }

    /**
     * Get Item
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getItem()
    {
        return $this->Item;
    }
}