<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/** 
 * @ORM\Entity
 * @ORM\Table(name="tax")
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
     * @ORM\Column(type="float", nullable=false, name="tax")
     */
    private $tax;


    /**
     * @ORM\Column(type="datetime", nullable=false, name="Tax_Start")
     */
    private $taxstart;


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
     * Set taxstart
     *
     * @param \DateTime $taxstart
     * @return Tax
     */
    public function setTaxstart($taxstart)
    {
        $this->taxstart= $taxstart;

        return $this;
    }

    /**
     * Get taxstart
     *
     * @return \DateTime
     */
    public function getTaxstart()
    {
        return $this->taxstart;
    }


}