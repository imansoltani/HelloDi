<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/** 
 * @ORM\Entity
 * @ORM\Table(indexes={@ORM\Index(name="SerialNumberIDX", columns={"serial_number"})})
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
     * @ORM\Column(type="boolean", nullable=false, name="status")
     */
    private $status;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Transaction", mappedBy="Code")
     */
    private $Transactions;

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
        $this->Transactions = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param boolean $status
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
     * @return boolean 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Add Transactions
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Transaction $transactions
     * @return Code
     */
    public function addTransaction(\HelloDi\DiDistributorsBundle\Entity\Transaction $transactions)
    {
        $this->Transactions[] = $transactions;
    
        return $this;
    }

    /**
     * Remove Transactions
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Transaction $transactions
     */
    public function removeTransaction(\HelloDi\DiDistributorsBundle\Entity\Transaction $transactions)
    {
        $this->Transactions->removeElement($transactions);
    }

    /**
     * Get Transactions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTransactions()
    {
        return $this->Transactions;
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
}