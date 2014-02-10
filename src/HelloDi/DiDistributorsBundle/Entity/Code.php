<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/** 
 * @ORM\Entity(repositoryClass="HelloDi\DiDistributorsBundle\Entity\CodeRepository")
 * @ORM\Table(name="code", indexes={@ORM\Index(name="SerialNumberIDX", columns={"serial_number"})})
 */
class Code
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** 
     * @ORM\Column(type="string", length=16, nullable=false, name="serial_number")
     */
    private $serialNumber;

    /** 
     * @ORM\Column(type="string", length=16, nullable=false, name="pin")
     */
    private $pin;

    /** 
     * @ORM\Column(type="smallint", nullable=false, name="status")
     */
    private $status;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Pin", mappedBy="code")
     */
    private $pins;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Item", inversedBy="Codes")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id", nullable=false)
     */
    private $Item;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Input", inversedBy="Codes")
     * @ORM\JoinColumn(name="input_id", referencedColumnName="id", nullable=false)
     */
    private $Input;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->pins = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set Item
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Item $item
     * @return Code
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

    /**
     * Set Input
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Input $input
     * @return Code
     */
    public function setInput(\HelloDi\DiDistributorsBundle\Entity\Input $input)
    {
        $this->Input = $input;
    
        return $this;
    }

    /**
     * Get Input
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\Input 
     */
    public function getInput()
    {
        return $this->Input;
    }

    /**
     * Add pins
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Pin $pins
     * @return Code
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
}