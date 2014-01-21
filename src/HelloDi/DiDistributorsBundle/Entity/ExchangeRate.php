<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/** 
 * @ORM\Entity
 * @ORM\Table(name="exchangerate")
 */
class ExchangeRate
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** 
     * @ORM\Column(type="decimal", nullable=false, name="CHF", scale=2)
     */
    private $CHF;

    /** 
     * @ORM\Column(type="decimal", nullable=false, name="EUR", scale=2)
     */
    private $EUR;

    /** 
     * @ORM\Column(type="datetime", nullable=false, name="date")
     */
    private $date;

    /** 
     * @ORM\Column(type="string", length=45, nullable=false, name="description")
     */
    private $description;

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
     * Set CHF
     *
     * @param float $cHF
     * @return ExchangeRate
     */
    public function setCHF($cHF)
    {
        $this->CHF = $cHF;
    
        return $this;
    }

    /**
     * Get CHF
     *
     * @return float 
     */
    public function getCHF()
    {
        return $this->CHF;
    }

    /**
     * Set EUR
     *
     * @param float $eUR
     * @return ExchangeRate
     */
    public function setEUR($eUR)
    {
        $this->EUR = $eUR;
    
        return $this;
    }

    /**
     * Get EUR
     *
     * @return float 
     */
    public function getEUR()
    {
        return $this->EUR;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return ExchangeRate
     */
    public function setDate($date)
    {
        $this->date = $date;
    
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return ExchangeRate
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }
}