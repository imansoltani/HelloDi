<?php

namespace HelloDi\AggregatorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HelloDi\AccountingBundle\Entity\Transaction;
use HelloDi\CoreBundle\Entity\Item;
use HelloDi\CoreBundle\Entity\User;

/**
 * TopUp
 *
 * @ORM\Table(name="topup")
 * @ORM\Entity
 */
class TopUp
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="client_transaction_id", type="string", length=50)
     */
    protected $clientTransactionID;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    protected $date;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="decimal", scale=2)
     */
    protected $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="mobile_number", type="string", length=20)
     */
    protected $mobileNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="sender_mobile_number", type="string", length=20, nullable=true)
     */
    protected $senderMobileNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="sender_email", type="string", length=50, nullable=true)
     */
    protected $senderEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="Transaction_id", type="string", length=20, nullable=true)
     */
    protected $transactionID;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=true)
     */
    protected $status;

    /**
     * @var integer
     *
     * @ORM\Column(name="status_code", type="string", length=20, nullable=true)
     */
    protected $statusCode;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\CoreBundle\Entity\Item", inversedBy="topUps")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id", nullable=false)
     */
    protected $item;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\AccountingBundle\Entity\Transaction")
     * @ORM\JoinColumn(name="sell_trans_id", referencedColumnName="id", nullable=true)
     */
    protected $sellerTransaction;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\AccountingBundle\Entity\Transaction")
     * @ORM\JoinColumn(name="comm_trans_id", referencedColumnName="id", nullable=true)
     */
    protected $commissionerTransaction;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\AccountingBundle\Entity\Transaction")
     * @ORM\JoinColumn(name="prov_trans_id", referencedColumnName="id", nullable=true)
     */
    protected $providerTransaction;

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
     * @return TopUp
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
     * @return TopUp
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
     * @return TopUp
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
     * @return TopUp
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
     * @return TopUp
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
     * @return TopUp
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
     * @return TopUp
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
     * @return TopUp
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
     * @return TopUp
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
     * @return TopUp
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
     * @return TopUp
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
     * @return TopUp
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
     * @return TopUp
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

    /**
     * Set providerTransaction
     *
     * @param Transaction $providerTransaction
     * @return TopUp
     */
    public function setProviderTransaction(Transaction $providerTransaction = null)
    {
        $this->providerTransaction = $providerTransaction;
    
        return $this;
    }

    /**
     * Get providerTransaction
     *
     * @return Transaction
     */
    public function getProviderTransaction()
    {
        return $this->providerTransaction;
    }
}