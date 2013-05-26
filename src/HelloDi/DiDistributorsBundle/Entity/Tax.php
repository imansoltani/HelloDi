<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/** 
 * @ORM\Entity
 */
class Tax
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** 
     * @ORM\Column(type="decimal", nullable=false, name="tax")
     */
    private $tax;

    /** 
     * @ORM\Column(type="date", nullable=false, name="tax_start")
     */
    private $taxStart;

    /** 
     * @ORM\Column(type="date", nullable=true, name="tax_end")
     */
    private $taxEnd;

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
     * Set tax
     *
     * @param float $tax
     * @return Tax
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
    
        return $this;
    }

    /**
     * Get tax
     *
     * @return float 
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * Set taxStart
     *
     * @param \DateTime $taxStart
     * @return Tax
     */
    public function setTaxStart($taxStart)
    {
        $this->taxStart = $taxStart;
    
        return $this;
    }

    /**
     * Get taxStart
     *
     * @return \DateTime 
     */
    public function getTaxStart()
    {
        return $this->taxStart;
    }

    /**
     * Set taxEnd
     *
     * @param \DateTime $taxEnd
     * @return Tax
     */
    public function setTaxEnd($taxEnd)
    {
        $this->taxEnd = $taxEnd;
    
        return $this;
    }

    /**
     * Get taxEnd
     *
     * @return \DateTime 
     */
    public function getTaxEnd()
    {
        return $this->taxEnd;
    }
}