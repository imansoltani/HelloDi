<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/** 
 * @ORM\Entity
 * @ORM\Table(name="country")
 */
class Country
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** 
     * @ORM\Column(type="string", length=3, nullable=false, name="Iso")
     */
    private $iso;

    /** 
     * @ORM\Column(type="string", length=45, nullable=false, name="Name")
     */
    private $name;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\DetailHistory", mappedBy="Country")
     */
    private $DetailHistories;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Entiti", mappedBy="Country")
     */
    private $Entitis;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Item", mappedBy="Country")
     */
    private $Items;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->Entitis = new \Doctrine\Common\Collections\ArrayCollection();
        $this->Items = new \Doctrine\Common\Collections\ArrayCollection();
        $this->DetailHistories = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set iso
     *
     * @param string $iso
     * @return Country
     */
    public function setIso($iso)
    {
        $this->iso = $iso;
    
        return $this;
    }

    /**
     * Get iso
     *
     * @return string 
     */
    public function getIso()
    {
        return $this->iso;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Country
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
     * Add Entitis
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Entiti $entitis
     * @return Country
     */
    public function addEntiti(\HelloDi\DiDistributorsBundle\Entity\Entiti $entitis)
    {
        $this->Entitis[] = $entitis;
    
        return $this;
    }

    /**
     * Remove Entitis
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Entiti $entitis
     */
    public function removeEntiti(\HelloDi\DiDistributorsBundle\Entity\Entiti $entitis)
    {
        $this->Entitis->removeElement($entitis);
    }

    /**
     * Get Entitis
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEntitis()
    {
        return $this->Entitis;
    }

    /**
     * Add Items
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Item $items
     * @return Country
     */
    public function addItem(\HelloDi\DiDistributorsBundle\Entity\Item $items)
    {
        $this->Items[] = $items;
    
        return $this;
    }

    /**
     * Remove Items
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Item $items
     */
    public function removeItem(\HelloDi\DiDistributorsBundle\Entity\Item $items)
    {
        $this->Items->removeElement($items);
    }

    /**
     * Get Items
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getItems()
    {
        return $this->Items;
    }

    /**
     * Add DetailHistories
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\DetailHistory $detailHistories
     * @return Country
     */
    public function addDetailHistorie(\HelloDi\DiDistributorsBundle\Entity\DetailHistory $detailHistories)
    {
        $this->DetailHistories[] = $detailHistories;
    
        return $this;
    }

    /**
     * Remove DetailHistories
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\DetailHistory $detailHistories
     */
    public function removeDetailHistorie(\HelloDi\DiDistributorsBundle\Entity\DetailHistory $detailHistories)
    {
        $this->DetailHistories->removeElement($detailHistories);
    }

    /**
     * Get DetailHistories
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDetailHistories()
    {
        return $this->DetailHistories;
    }
}