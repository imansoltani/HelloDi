<?php

namespace HelloDi\DiDistributorsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderCode
 *
 * @ORM\Table(name="ordercode")
 * @ORM\Entity
 */
class OrderCode
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=2, nullable=false)
     */
    private $lang;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Pin", mappedBy="order")
     */
    private $pins;

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
     * Constructor
     */
    public function __construct()
    {
        $this->pins = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add pins
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Pin $pins
     * @return OrderCode
     */
    public function addPin(\HelloDi\DiDistributorsBundle\Entity\Pin $pins)
    {
        $this->pins[] = $pins;

        return $this;
    }

    /**
     * Remove pins
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Pin $pins
     */
    public function removePin(\HelloDi\DiDistributorsBundle\Entity\Pin $pins)
    {
        $this->pins->removeElement($pins);
    }

    /**
     * Get pins
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPins()
    {
        return $this->pins;
    }

    /**
     * Set lang
     *
     * @param string $lang
     * @return OrderCode
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    
        return $this;
    }

    /**
     * Get lang
     *
     * @return string 
     */
    public function getLang()
    {
        return $this->lang;
    }
}