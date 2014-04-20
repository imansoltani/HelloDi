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
     * @ORM\Column(type="string", length=3, nullable=false, name="iso")
     */
    private $iso;

    /**
     * @ORM\Column(type="string", length=45, nullable=false, name="name")
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Entity", mappedBy="country")
     */
    private $entities;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Tax", mappedBy="Country")
     */
    private $taxes;

    public function getIsoName()
    {
        return $this->getIso() . ' . ' . $this->getName();
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->entities = new ArrayCollection();
        $this->taxes = new ArrayCollection();
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
     * @param Entity $entities
     * @return Country
     */
    public function addEntities(Entity $entities)
    {
        $this->entities[] = $entities;
        return $this;
    }

    /**
     * Remove Entities
     *
     * @param Entity $entities
     */
    public function removeEntities(Entity $entities)
    {
        $this->entities->removeElement($entities);
    }

    /**
     * Get Entities
     *
     * @return Collection
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * Add Taxes
     *
     * @param Tax $taxes
     * @return Country
     */
    public function addTax(Tax $taxes)
    {
        $this->taxes[] = $taxes;
        return $this;
    }

    /**
     * Remove Taxes
     *
     * @param Tax $taxes
     */
    public function removeTax(Tax $taxes)
    {
        $this->taxes->removeElement($taxes);
    }

    /**
     * Get Taxes
     *
     * @return Collection
     */
    public function getTaxes()
    {
        return $this->taxes;
    }
}