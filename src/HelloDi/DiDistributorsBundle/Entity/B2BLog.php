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
     * @var integer
     *
     * @ORM\Column(name="Amount", type="decimal", scale=2)
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
     * @ORM\Column(name="TransactionID", type="string", length=20, nullable=true)
     */
    private $transactionID;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=true)
     */
    private $status;

    /**
     * @var integer
     *
     * @ORM\Column(name="status_code", type="string", length=20, nullable=true)
     */
    private $statusCode;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\User", inversedBy="B2BLogs")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $User;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Item", inversedBy="B2BLogs")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id", nullable=false)
     */
    private $Item;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Transaction", mappedBy="B2BLog")
     */
    private $Transactions;

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
     * Set User
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\User $user
     * @return B2BLog
     */
    public function setUser(\HelloDi\DiDistributorsBundle\Entity\User $user)
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

    /**
     * Set statusCode
     *
     * @param string $statusCode
     * @return B2BLog
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Get statusCode
     *
     * @return string
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Set Item
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Item $item
     * @return B2BLog
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
     * Constructor
     */
    public function __construct()
    {
        $this->Transactions = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add Transactions
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Transaction $transactions
     * @return B2BLog
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
}
