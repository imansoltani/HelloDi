<?php
namespace HelloDi\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use HelloDi\AccountingBundle\Entity\Account;

/**
 * @ORM\Entity
 * @ORM\Table(name="entity")
 */
class Entity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=45, nullable=false, name="name")
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=45, nullable=true, name="vat_number")
     */
    protected $vatNumber;

    /**
     * @ORM\Column(type="string", length=15, nullable=true, name="tel1")
     */
    protected $tel1;

    /**
     * @ORM\Column(type="string", length=15, nullable=true, name="tel2")
     */
    protected $tel2;

    /**
     * @ORM\Column(type="string", length=15, nullable=true, name="fax")
     */
    protected $fax;

    /**
     * @ORM\Column(type="string", length=45, nullable=true, name="website")
     */
    protected $website;

    /**
     * @ORM\Column(type="string", length=45, nullable=false)
     */
    protected $address1;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    protected $address2;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    protected $address3;

    /**
     * @ORM\Column(type="string", length=45, nullable=false, name="np")
     */
    protected $NP;

    /**
     * @ORM\Column(type="string", length=45, nullable=false)
     */
    protected $city;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\AccountingBundle\Entity\Account", mappedBy="entity")
     */
    protected $accounts;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\CoreBundle\Entity\User", mappedBy="entity")
     */
    protected $users;

    /**
     * @ORM\Column(type="string", length=2, nullable=false)
     */
    protected $country;

    /**
     * @ORM\Column(type="boolean", name="vat")
     */
    protected $vat = true;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->accounts = new ArrayCollection();
        $this->users = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Entity
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set vatNumber
     *
     * @param string $vatNumber
     * @return Entity
     */
    public function setVatNumber($vatNumber)
    {
        $this->vatNumber = $vatNumber;

        return $this;
    }

    /**
     * Get vatNumber
     *
     * @return string
     */
    public function getVatNumber()
    {
        return $this->vatNumber;
    }

    /**
     * Set tel1
     *
     * @param string $tel1
     * @return Entity
     */
    public function setTel1($tel1)
    {
        $this->tel1 = $tel1;

        return $this;
    }

    /**
     * Get tel1
     *
     * @return string
     */
    public function getTel1()
    {
        return $this->tel1;
    }

    /**
     * Set tel2
     *
     * @param string $tel2
     * @return Entity
     */
    public function setTel2($tel2)
    {
        $this->tel2 = $tel2;

        return $this;
    }

    /**
     * Get tel2
     *
     * @return string
     */
    public function getTel2()
    {
        return $this->tel2;
    }

    /**
     * Set fax
     *
     * @param string $fax
     * @return Entity
     */
    public function setFax($fax)
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * Get fax
     *
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set website
     *
     * @param string $website
     * @return Entity
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set address1
     *
     * @param string $address1
     * @return Entity
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;

        return $this;
    }

    /**
     * Get address1
     *
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * Set address2
     *
     * @param string $address2
     * @return Entity
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * Get address2
     *
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * Set address3
     *
     * @param string $address3
     * @return Entity
     */
    public function setAddress3($address3)
    {
        $this->address3 = $address3;

        return $this;
    }

    /**
     * Get address3
     *
     * @return string
     */
    public function getAddress3()
    {
        return $this->address3;
    }

    /**
     * Set NP
     *
     * @param string $nP
     * @return Entity
     */
    public function setNP($nP)
    {
        $this->NP = $nP;

        return $this;
    }

    /**
     * Get NP
     *
     * @return string
     */
    public function getNP()
    {
        return $this->NP;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Entity
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Add accounts
     *
     * @param Account $accounts
     * @return Entity
     */
    public function addAccount(Account $accounts)
    {
        $this->accounts[] = $accounts;

        return $this;
    }

    /**
     * Remove accounts
     *
     * @param Account $accounts
     */
    public function removeAccount(Account $accounts)
    {
        $this->accounts->removeElement($accounts);
    }

    /**
     * Get accounts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAccounts()
    {
        return $this->accounts;
    }

    /**
     * Add users
     *
     * @param User $users
     * @return Entity
     */
    public function addUser(User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param User $users
     */
    public function removeUser(User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return Entity
     */
    public function setCountry($country)
    {
        $this->country = strtoupper($country);
    
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
     * Set vat
     *
     * @param boolean $vat
     * @return Entity
     */
    public function setVat($vat)
    {
        $this->vat = $vat;
    
        return $this;
    }

    /**
     * Get vat
     *
     * @return boolean 
     */
    public function getVat()
    {
        return $this->vat;
    }
}