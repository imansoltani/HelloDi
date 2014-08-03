<?php
namespace HelloDi\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="HelloDi\CoreBundle\Entity\CodeRepository")
 * @ORM\Table(name="code", indexes={@ORM\Index(name="SerialNumberIDX", columns={"serial_number"})})
 */
class Code
{
    const AVAILABLE = 1;
    const UNAVAILABLE = 0;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="bigint", nullable=false, name="serial_number")
     */
    protected $serialNumber;

    /**
     * @ORM\Column(type="bigint", nullable=false, name="pin")
     */
    protected $pin;

    /**
     * @ORM\Column(type="boolean", nullable=false, name="status")
     */
    protected $status;

    /**
     * @ORM\ManyToMany(targetEntity="HelloDi\CoreBundle\Entity\Pin", mappedBy="codes")
     */
    protected $pins;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\CoreBundle\Entity\Item", inversedBy="codes")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id", nullable=false)
     */
    protected $item;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\CoreBundle\Entity\Input", inversedBy="codes")
     * @ORM\JoinColumn(name="input_id", referencedColumnName="id", nullable=false)
     */
    protected $input;

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
     * Set serialNumber
     *
     * @param string $serialNumber
     * @return Code
     */
    public function setSerialNumber($serialNumber)
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    /**
     * Get serialNumber
     *
     * @return string
     */
    public function getSerialNumber()
    {
        return $this->serialNumber;
    }

    /**
     * Set pin
     *
     * @param string $pin
     * @return Code
     */
    public function setPin($pin)
    {
        $this->pin = $pin;

        return $this;
    }

    /**
     * Get pin
     *
     * @return string
     */
    public function getPin()
    {
        return $this->pin;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Code
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Add pins
     *
     * @param Pin $pins
     * @return Code
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

    /**
     * Set item
     *
     * @param Item $item
     * @return Code
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

    /**
     * Set input
     *
     * @param Input $input
     * @return Code
     */
    public function setInput(Input $input)
    {
        $this->input = $input;

        return $this;
    }

    /**
     * Get input
     *
     * @return Input
     */
    public function getInput()
    {
        return $this->input;
    }
}