<?php

namespace HelloDi\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * OrderCode
 *
 * @ORM\Table(name="order_code")
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
     * @ORM\OneToMany(targetEntity="HelloDi\CoreBundle\Entity\Pin", mappedBy="order")
     */
    private $pins;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->pins = new ArrayCollection();
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

    /**
     * Add pins
     *
     * @param Pin $pins
     * @return OrderCode
     */
    public function addPin(Pin $pins)
    {
        $this->pins[] = $pins;

        return $this;
    }

    /**
     * Remove pins
     *
     * @param Pin $pins
     */
    public function removePin(Pin $pins)
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
}