<?php

namespace HelloDi\DiDistributorsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HelloDi\AccountingBundle\Entity\Transaction;

/**
 * b2blog
 *
 * @ORM\Table(name="b2b_log")
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
     * @ORM\Column(name="client_transaction_id", type="string", length=50)
     */
    private $clientTransactionID;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="decimal", scale=2)
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="mobile_number", type="string", length=20)
     */
    private $mobileNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="sender_mobile_number", type="string", length=20, nullable=true)
     */
    private $senderMobileNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="sender_email", type="string", length=50, nullable=true)
     */
    private $senderEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="Transaction_id", type="string", length=20, nullable=true)
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
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\User", inversedBy="b2bLogs")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Item", inversedBy="b2bLogs")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id", nullable=false)
     */
    private $item;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\AccountingBundle\Entity\Transaction", inversedBy="sellerB2BLog")
     * @ORM\JoinColumn(name="sell_trans_id", referencedColumnName="id", nullable=true)
     */
    private $sellerTransaction;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\AccountingBundle\Entity\Transaction", inversedBy="commissionerB2BLog")
     * @ORM\JoinColumn(name="comm_trans_id", referencedColumnName="id", nullable=true)
     */
    private $commissionerTransaction;

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
     * @return B2BLog
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
     * @return B2BLog
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
     * @param float $amount
     * @return B2BLog
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set mobileNumber
     *
     * @param string $mobileNumber
     * @return B2BLog
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
     * Set senderMobileNumber
     *
     * @param string $senderMobileNumber
     * @return B2BLog
     */
    public function setSenderMobileNumber($senderMobileNumber)
    {
        $this->senderMobileNumber = $senderMobileNumber;

        return $this;
    }

    /**
     * Get senderMobileNumber
     *
     * @return string
     */
    public function getSenderMobileNumber()
    {
        return $this->senderMobileNumber;
    }

    /**
     * Set senderEmail
     *
     * @param string $senderEmail
     * @return B2BLog
     */
    public function setSenderEmail($senderEmail)
    {
        $this->senderEmail = $senderEmail;

        return $this;
    }

    /**
     * Get senderEmail
     *
     * @return string
     */
    public function getSenderEmail()
    {
        return $this->senderEmail;
    }

    /**
     * Set transactionID
     *
     * @param string $transactionID
     * @return B2BLog
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
     * @return B2BLog
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
     * Set user
     *
     * @param User $user
     * @return B2BLog
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set item
     *
     * @param Item $item
     * @return B2BLog
     */
    public function setItem(Item $item)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item
     *
     * @return Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set sellerTransaction
     *
     * @param Transaction $sellerTransaction
     * @return B2BLog
     */
    public function setSellerTransaction(Transaction $sellerTransaction = null)
    {
        $this->sellerTransaction = $sellerTransaction;

        return $this;
    }

    /**
     * Get sellerTransaction
     *
     * @return Transaction
     */
    public function getSellerTransaction()
    {
        return $this->sellerTransaction;
    }

    /**
     * Set commissionerTransaction
     *
     * @param Transaction $commissionerTransaction
     * @return B2BLog
     */
    public function setCommissionerTransaction(Transaction $commissionerTransaction = null)
    {
        $this->commissionerTransaction = $commissionerTransaction;

        return $this;
    }

    /**
     * Get commissionerTransaction
     *
     * @return Transaction
     */
    public function getCommissionerTransaction()
    {
        return $this->commissionerTransaction;
    }
}