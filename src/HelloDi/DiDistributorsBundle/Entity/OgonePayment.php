<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="OgonePayment")
 */
class OgonePayment
{
    const OGONE_RESULT_ACCPETED     = 9;
    const OGONE_RESULT_CANCELED     = 1;
    const OGONE_RESULT_DECLINED     = 2;
    const OGONE_RESULT_EXCEPTION    = 92;

    const STATUS_ACCEPTED           = 'accepted';
    const STATUS_CANCELED           = 'canceled';
    const STATUS_DECLINED           = 'declined';
    const STATUS_PENDING            = 'pending';
    const STATUS_UNCERTAIN          = 'uncertain';
    const STATUS_UNKNOWN            = 'unknown';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="payment_amount", type="float" , nullable=false)
     */
    private $paymentAmount;

    /**
     * @ORM\Column(name="payment_currency_iso", type="string", length=3 ,nullable=true)
     */
    private $paymentCurrencyISO;

    /**
     * @ORM\Column(name="payment_status", type="string" , nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(name="created_at", type="datetime" , nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\User", inversedBy="OgonePayment")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $User;
    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Transaction", inversedBy="OgonePayment")
     * @ORM\JoinColumn(name="transaction_id", referencedColumnName="id", nullable=true)
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
     * Set paymentAmount
     *
     * @param float $paymentAmount
     * @return OgonePayment
     */
    public function setPaymentAmount($paymentAmount)
    {
        $this->paymentAmount = round($paymentAmount, 2);
    
        return $this;
    }

    public function getOgoneAmount()
    {
        return $this->paymentAmount * 100;
    }

    /**
     * Get paymentAmount
     *
     * @return float 
     */
    public function getPaymentAmount()
    {
        return $this->paymentAmount;
    }

    /**
     * Set paymentCurrencyISO
     *
     * @param string $paymentCurrencyISO
     * @return OgonePayment
     */
    public function setPaymentCurrencyISO($paymentCurrencyISO)
    {
        $this->paymentCurrencyISO = $paymentCurrencyISO;
    
        return $this;
    }

    /**
     * Get paymentCurrencyISO
     *
     * @return string 
     */
    public function getPaymentCurrencyISO()
    {
        return $this->paymentCurrencyISO;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return OgonePayment
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return OgonePayment
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set User
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\User $user
     * @return OgonePayment
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
     * Set Transactions
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Transaction $transactions
     * @return OgonePayment
     */
    public function setTransactions(\HelloDi\DiDistributorsBundle\Entity\Transaction $transactions)
    {
        $this->Transactions = $transactions;
    
        return $this;
    }

    /**
     * Get Transactions
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\Transaction 
     */
    public function getTransactions()
    {
        return $this->Transactions;
    }

    public function getOrderReference()
    {
        return $this->getCreatedAt()->format('ymdHi') . $this->id;
    }

    public function isProcessed()
    {
        return static::STATUS_PENDING !== $this->getStatus();
    }

    public function isAccepted()
    {
        return static::STATUS_ACCEPTED === $this->getStatus();
    }

    public function isCanceled()
    {
        return static::STATUS_CANCELED === $this->getStatus();
    }
}