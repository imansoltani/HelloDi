<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 */
class DetailHistory
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="date", nullable=false, name="adrsDate")
     */
    private $adrsDate;

    /**
     * @ORM\Column(type="string", length=45, nullable=false)
     */
    private $adrs1;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $adrs2;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $adrs3;

    /**
     * @ORM\Column(type="string", length=45, nullable=false)
     */
    private $adrsNp;

    /**
     * @ORM\Column(type="string", length=45, nullable=false)
     */
    private $adrsCity;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Country", inversedBy="DetailHistories")
     * @ORM\JoinColumn(name="Country_id", referencedColumnName="id", nullable=false)
     */
    private $Country;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Entiti", inversedBy="DetailHistories")
     * @ORM\JoinColumn(name="entiti_id", referencedColumnName="id", nullable=false)
     */
    private $Entiti;

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
     * Set adrsDate
     *
     * @param \DateTime $adrsDate
     * @return DetailHistory
     */
    public function setAdrsDate($adrsDate)
    {
        $this->adrsDate = $adrsDate;
    
        return $this;
    }

    /**
     * Get adrsDate
     *
     * @return \DateTime 
     */
    public function getAdrsDate()
    {
        return $this->adrsDate;
    }

    /**
     * Set adrs1
     *
     * @param string $adrs1
     * @return DetailHistory
     */
    public function setAdrs1($adrs1)
    {
        $this->adrs1 = $adrs1;
    
        return $this;
    }

    /**
     * Get adrs1
     *
     * @return string 
     */
    public function getAdrs1()
    {
        return $this->adrs1;
    }

    /**
     * Set adrs2
     *
     * @param string $adrs2
     * @return DetailHistory
     */
    public function setAdrs2($adrs2)
    {
        $this->adrs2 = $adrs2;
    
        return $this;
    }

    /**
     * Get adrs2
     *
     * @return string 
     */
    public function getAdrs2()
    {
        return $this->adrs2;
    }

    /**
     * Set adrs3
     *
     * @param string $adrs3
     * @return DetailHistory
     */
    public function setAdrs3($adrs3)
    {
        $this->adrs3 = $adrs3;
    
        return $this;
    }

    /**
     * Get adrs3
     *
     * @return string 
     */
    public function getAdrs3()
    {
        return $this->adrs3;
    }

    /**
     * Set adrsNp
     *
     * @param string $adrsNp
     * @return DetailHistory
     */
    public function setAdrsNp($adrsNp)
    {
        $this->adrsNp = $adrsNp;
    
        return $this;
    }

    /**
     * Get adrsNp
     *
     * @return string 
     */
    public function getAdrsNp()
    {
        return $this->adrsNp;
    }

    /**
     * Set adrsCity
     *
     * @param string $adrsCity
     * @return DetailHistory
     */
    public function setAdrsCity($adrsCity)
    {
        $this->adrsCity = $adrsCity;
    
        return $this;
    }

    /**
     * Get adrsCity
     *
     * @return string 
     */
    public function getAdrsCity()
    {
        return $this->adrsCity;
    }

    /**
     * Set Country
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Country $country
     * @return DetailHistory
     */
    public function setCountry(\HelloDi\DiDistributorsBundle\Entity\Country $country)
    {
        $this->Country = $country;
    
        return $this;
    }

    /**
     * Get Country
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\Country 
     */
    public function getCountry()
    {
        return $this->Country;
    }

    /**
     * Set Entiti
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Entiti $entiti
     * @return DetailHistory
     */
    public function setEntiti(\HelloDi\DiDistributorsBundle\Entity\Entiti $entiti)
    {
        $this->Entiti = $entiti;
    
        return $this;
    }

    /**
     * Get Entiti
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\Entiti 
     */
    public function getEntiti()
    {
        return $this->Entiti;
    }
}