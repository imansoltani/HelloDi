<?php
namespace HelloDi\CoreBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="HelloDi\CoreBundle\Entity\TaxRepository")
 * @ORM\Table(name="tax", indexes={@ORM\Index(name="CountryIDX", columns={"country"})})
 */
class Tax
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=2)
     */
    protected $country;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $dateEnd;

    /**
     * @ORM\Column(type="decimal", precision=4, scale=2)
     */
    protected $vat;

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
     * Set country
     *
     * @param string $country
     * @return Tax
     */
    public function setCountry($country)
    {
        $this->country = $country;
    
        return $this;
    }

    /**
     * Get country
     *
     * @return string 
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set dateEnd
     *
     * @param \DateTime $dateEnd
     * @return Tax
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;
    
        return $this;
    }

    /**
     * Get dateEnd
     *
     * @return \DateTime 
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * Set vat
     *
     * @param float $vat
     * @return Tax
     */
    public function setVat($vat)
    {
        $this->vat = $vat;
    
        return $this;
    }

    /**
     * Get vat
     *
     * @return float
     */
    public function getVat()
    {
        return $this->vat;
    }
}