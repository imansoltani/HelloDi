<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/** 
 * @ORM\Entity
 * @ORM\Table(name="entiti")
 */
class Entiti
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** 
     * @ORM\Column(type="string", length=45, nullable=false, name="ent_name")
     */
    private $entName;

    /** 
     * @ORM\Column(type="string", length=45, nullable=true, name="ebt_vat_number")
     */
    private $entVatNumber;

    /** 
     * @ORM\Column(type="string", length=15, nullable=true, name="ent_tel1")
     */
    private $entTel1;

    /** 
     * @ORM\Column(type="string", length=15, nullable=true, name="ent_tel2")
     */
    private $entTel2;

    /** 
     * @ORM\Column(type="string", length=15, nullable=true, name="ent_fax")
     */
    private $entFax;

    /** 
     * @ORM\Column(type="string", length=45, nullable=true, name="ent_website")
     */
    private $entWebsite;

    /** 
     * @ORM\Column(type="string", length=45, nullable=false)
     */
    private $entAdrs1;

    /** 
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $entAdrs2;

    /** 
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $entAdrs3;

    /** 
     * @ORM\Column(type="string", length=45, nullable=false)
     */
    private $entNp;

    /** 
     * @ORM\Column(type="string", length=45, nullable=false)
     */
    private $entCity;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Account", mappedBy="Entiti")
     */
    private $Accounts;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\DetailHistory", mappedBy="Entiti")
     */
    private $DetailHistories;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\User", mappedBy="Entiti")
     */
    private $Users;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Country", inversedBy="Entitis")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=true)
     */
    private $Country;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->Accounts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->DetailHistories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->Users = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set entName
     *
     * @param string $entName
     * @return Entiti
     */
    public function setEntName($entName)
    {
        $this->entName = $entName;
    
        return $this;
    }

    /**
     * Get entName
     *
     * @return string 
     */
    public function getEntName()
    {
        return $this->entName;
    }

    /**
     * Set entVatNumber
     *
     * @param string $entVatNumber
     * @return Entiti
     */
    public function setEntVatNumber($entVatNumber)
    {
        $this->entVatNumber = $entVatNumber;
    
        return $this;
    }

    /**
     * Get entVatNumber
     *
     * @return string 
     */
    public function getEntVatNumber()
    {
        return $this->entVatNumber;
    }

    /**
     * Set entTel1
     *
     * @param string $entTel1
     * @return Entiti
     */
    public function setEntTel1($entTel1)
    {
        $this->entTel1 = $entTel1;
    
        return $this;
    }

    /**
     * Get entTel1
     *
     * @return string 
     */
    public function getEntTel1()
    {
        return $this->entTel1;
    }

    /**
     * Set entTel2
     *
     * @param string $entTel2
     * @return Entiti
     */
    public function setEntTel2($entTel2)
    {
        $this->entTel2 = $entTel2;
    
        return $this;
    }

    /**
     * Get entTel2
     *
     * @return string 
     */
    public function getEntTel2()
    {
        return $this->entTel2;
    }

    /**
     * Set entFax
     *
     * @param string $entFax
     * @return Entiti
     */
    public function setEntFax($entFax)
    {
        $this->entFax = $entFax;
    
        return $this;
    }

    /**
     * Get entFax
     *
     * @return string 
     */
    public function getEntFax()
    {
        return $this->entFax;
    }

    /**
     * Set entWebsite
     *
     * @param string $entWebsite
     * @return Entiti
     */
    public function setEntWebsite($entWebsite)
    {
        $this->entWebsite = $entWebsite;
    
        return $this;
    }

    /**
     * Get entWebsite
     *
     * @return string 
     */
    public function getEntWebsite()
    {
        return $this->entWebsite;
    }

    /**
     * Set entAdrs1
     *
     * @param string $entAdrs1
     * @return Entiti
     */
    public function setEntAdrs1($entAdrs1)
    {
        $this->entAdrs1 = $entAdrs1;
    
        return $this;
    }

    /**
     * Get entAdrs1
     *
     * @return string 
     */
    public function getEntAdrs1()
    {
        return $this->entAdrs1;
    }

    /**
     * Set entAdrs2
     *
     * @param string $entAdrs2
     * @return Entiti
     */
    public function setEntAdrs2($entAdrs2)
    {
        $this->entAdrs2 = $entAdrs2;
    
        return $this;
    }

    /**
     * Get entAdrs2
     *
     * @return string 
     */
    public function getEntAdrs2()
    {
        return $this->entAdrs2;
    }

    /**
     * Set entAdrs3
     *
     * @param string $entAdrs3
     * @return Entiti
     */
    public function setEntAdrs3($entAdrs3)
    {
        $this->entAdrs3 = $entAdrs3;
    
        return $this;
    }

    /**
     * Get entAdrs3
     *
     * @return string 
     */
    public function getEntAdrs3()
    {
        return $this->entAdrs3;
    }

    /**
     * Set entNp
     *
     * @param string $entNp
     * @return Entiti
     */
    public function setEntNp($entNp)
    {
        $this->entNp = $entNp;
    
        return $this;
    }

    /**
     * Get entNp
     *
     * @return string 
     */
    public function getEntNp()
    {
        return $this->entNp;
    }

    /**
     * Set entCity
     *
     * @param string $entCity
     * @return Entiti
     */
    public function setEntCity($entCity)
    {
        $this->entCity = $entCity;
    
        return $this;
    }

    /**
     * Get entCity
     *
     * @return string 
     */
    public function getEntCity()
    {
        return $this->entCity;
    }

    /**
     * Add Accounts
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Account $accounts
     * @return Entiti
     */
    public function addAccount(\HelloDi\DiDistributorsBundle\Entity\Account $accounts)
    {
        $this->Accounts[] = $accounts;
    
        return $this;
    }

    /**
     * Remove Accounts
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Account $accounts
     */
    public function removeAccount(\HelloDi\DiDistributorsBundle\Entity\Account $accounts)
    {
        $this->Accounts->removeElement($accounts);
    }

    /**
     * Get Accounts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAccounts()
    {
        return $this->Accounts;
    }

    /**
     * Add DetailHistories
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\DetailHistory $detailHistories
     * @return Entiti
     */
    public function addDetailHistorie(\HelloDi\DiDistributorsBundle\Entity\DetailHistory $detailHistories)
    {
        $this->DetailHistories[] = $detailHistories;
    
        return $this;
    }

    /**
     * Remove DetailHistories
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\DetailHistory $detailHistories
     */
    public function removeDetailHistorie(\HelloDi\DiDistributorsBundle\Entity\DetailHistory $detailHistories)
    {
        $this->DetailHistories->removeElement($detailHistories);
    }

    /**
     * Get DetailHistories
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDetailHistories()
    {
        return $this->DetailHistories;
    }

    /**
     * Add Users
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\User $users
     * @return Entiti
     */
    public function addUser(\HelloDi\DiDistributorsBundle\Entity\User $users)
    {
        $this->Users[] = $users;
    
        return $this;
    }

    /**
     * Remove Users
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\User $users
     */
    public function removeUser(\HelloDi\DiDistributorsBundle\Entity\User $users)
    {
        $this->Users->removeElement($users);
    }

    /**
     * Get Users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->Users;
    }

    /**
     * Set Country
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Country $country
     * @return Entiti
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
}