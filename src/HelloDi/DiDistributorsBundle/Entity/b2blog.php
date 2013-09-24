<?php

namespace HelloDi\DiDistributorsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * b2blog
 *
 * @ORM\Table(name="b2blog")
 * @ORM\Entity
 */
class B2BLog
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="ClientTransactionID", type="string", length=50)
     */
    private $clientTransactionID;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="CarrierCode", type="string", length=50)
     */
    private $carrierCode;

    /**
     * @var string
     *
     * @ORM\Column(name="CountryCode", type="string", length=2)
     */
    private $countryCode;

    /**
     * @var integer
     *
     * @ORM\Column(name="Amount", type="integer")
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="MobileNumber", type="string", length=20)
     */
    private $mobileNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="TransactionID", type="string", length=20)
     */
    private $transactionID;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint")
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="ServiceNumber", type="string", length=50)
     */
    private $serviceNumber;

    /**
     * @var integer
     *
     * @ORM\Column(name="StoreDiscount", type="integer")
     */
    private $storeDiscount;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\User", inversedBy="B2BLogs")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $User;


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
     * Set clientTransactionID
     *
     * @param string $clientTransactionID
     * @return b2blog
     */
    public function setClientTransactionID($clientTransactionID)
    {
        $this->clientTransactionID = $clientTransactionID;
    
        return $this;
    }

    /**
     * Get clientTransactionID
     *
     * @return string 
     */
    public function getClientTransactionID()
    {
        return $this->clientTransactionID;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return b2blog
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
     * Set carrierCode
     *
     * @param string $carrierCode
     * @return b2blog
     */
    public function setCarrierCode($carrierCode)
    {
        $this->carrierCode = $carrierCode;
    
        return $this;
    }

    /**
     * Get carrierCode
     *
     * @return string 
     */
    public function getCarrierCode()
    {
        return $this->carrierCode;
    }

    /**
     * Set countryCode
     *
     * @param string $countryCode
     * @return b2blog
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
    
        return $this;
    }

    /**
     * Get countryCode
     *
     * @return string 
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * Set amount
     *
     * @param integer $amount
     * @return b2blog
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    
        return $this;
    }

    /**
     * Get amount
     *
     * @return integer 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set mobileNumber
     *
     * @param string $mobileNumber
     * @return b2blog
     */
    public function setMobileNumber($mobileNumber)
    {
        $this->mobileNumber = $mobileNumber;
    
        return $this;
    }

    /**
     * Get mobileNumber
     *
     * @return string 
     */
    public function getMobileNumber()
    {
        return $this->mobileNumber;
    }

    /**
     * Set transactionID
     *
     * @param string $transactionID
     * @return b2blog
     */
    public function setTransactionID($transactionID)
    {
        $this->transactionID = $transactionID;
    
        return $this;
    }

    /**
     * Get transactionID
     *
     * @return string 
     */
    public function getTransactionID()
    {
        return $this->transactionID;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return b2blog
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
     * Set serviceNumber
     *
     * @param string $serviceNumber
     * @return b2blog
     */
    public function setServiceNumber($serviceNumber)
    {
        $this->serviceNumber = $serviceNumber;
    
        return $this;
    }

    /**
     * Get serviceNumber
     *
     * @return string 
     */
    public function getServiceNumber()
    {
        return $this->serviceNumber;
    }

    /**
     * Set storeDiscount
     *
     * @param integer $storeDiscount
     * @return b2blog
     */
    public function setStoreDiscount($storeDiscount)
    {
        $this->storeDiscount = $storeDiscount;
    
        return $this;
    }

    /**
     * Get storeDiscount
     *
     * @return integer 
     */
    public function getStoreDiscount()
    {
        return $this->storeDiscount;
    }

    /**
     * Set User
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\User $user
     * @return B2BLog
     */
    public function setUser(\HelloDi\DiDistributorsBundle\Entity\User $user = null)
    {
        $this->User = $user;
    
        return $this;
    }

    /**
     * Get User
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->User;
    }
}