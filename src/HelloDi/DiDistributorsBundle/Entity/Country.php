<?php
namespace HelloDi\DiDistributorsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Entiti", mappedBy="Country")
     */
    private $Entities;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Tax", mappedBy="Country")
     */
    private $Taxs;

    public function getIsoName()
    {
        return $this->getIso() . ' . ' . $this->getName();
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->Entities = new ArrayCollection();
        $this->Taxs = new ArrayCollection();
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
     * Add Entities
     *
     * @param Entiti $entities
     * @return Country
     */
    public function addEntitie(Entiti $entities)
    {
        $this->Entities[] = $entities;
        return $this;
    }

    /**
     * Remove Entities
     *
     * @param Entiti $entities
     */
    public function removeEntitie(Entiti $entities)
    {
        $this->Entities->removeElement($entities);
    }

    /**
     * Get Entities
     *
     * @return Collection
     */
    public function getEntities()
    {
        return $this->Entities;
    }

    /**
     * Add Taxs
     *
     * @param Tax $taxs
     * @return Country
     */
    public function addTax(Tax $taxs)
    {
        $this->Taxs[] = $taxs;
        return $this;
    }

    /**
     * Remove Taxs
     *
     * @param Tax $taxs
     */
    public function removeTax(Tax $taxs)
    {
        $this->Taxs->removeElement($taxs);
    }

    /**
     * Get Taxs
     *
     * @return Collection
     */
    public function getTaxs()
    {
        return $this->Taxs;
    }
}