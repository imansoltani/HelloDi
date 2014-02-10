<?php
namespace HelloDi\AccountingBundle\Entity;

use HelloDi\DiDistributorsBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="HelloDi\AccountingBundle\Entity\OgonePaymentRepository")
 * @ORM\Table(name="ogone_payment")
 */

class OgonePayment
{
    const OGONE_RESULT_ACCPETED     = 5;
    const OGONE_RESULT_CANCELED     = 1;
    const OGONE_RESULT_DECLINED     = 2;
    const OGONE_RESULT_EXCEPTION    = 52;

    const STATUS_ACCEPTED           = 'accepted';
    const STATUS_CANCELED           = 'canceled';
    const STATUS_DECLINED           = 'declined';
    const STATUS_PENDING            = 'pending';
    const STATUS_UNCERTAIN          = 'uncertain';
    const STATUS_UNKNOWN            = 'unknown';

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="payment_amount", type="float")
     * @Assert\Range(min=100)
     * @Assert\NotBlank
     */
    private $paymentAmount;

    /**
     * @ORM\Column(name="payment_currency_iso", type="string", length=3)
     * @Assert\NotBlank
     */
    private $paymentCurrencyISO;

    /**
     * @ORM\Column(name="payment_status", type="string")
     * @Assert\NotBlank
     */
    private $status;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(name="ogone_ref", type="string", length=10, nullable=true)
     */
    private $ogoneRef;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\User", inversedBy="OgonePayment")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    private $User;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\AccountingBundle\Entity\Transaction", inversedBy="ogonePayment")
     * @ORM\JoinColumn(name="transaction_id", referencedColumnName="id", nullable=true)
     */
    private $transaction;


    public function __construct()
    {
        $this->createdAt = new \DateTime;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPaymentAmount()
    {
        return $this->paymentAmount;
    }

    public function setPaymentAmount($paymentAmount)
    {
       $this->paymentAmount = round($paymentAmount, 2);
    }

    public function getOgoneAmount()
    {
        return $this->paymentAmount * 100;
    }

    public function getPaymentCurrencyISO()
    {
        return $this->paymentCurrencyISO;
    }

    public function setPaymentCurrencyISO($paymentCurrencyISO)
    {
        $this->paymentCurrencyISO = $paymentCurrencyISO;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->User;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->User = $user;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getOgoneRef()
    {
        return $this->ogoneRef;
    }

    public function setOgoneRef($ogoneRef)
    {
        $this->ogoneRef = $ogoneRef;
    }

    public function getOrderReference()
    {
        return $this->getCreatedAt()->format('ymdHi') . $this->id;
    }

    /**
     * @return Transaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * @param Transaction $transaction
     */
    public function setTransaction(Transaction $transaction)
    {
        $this->transaction = $transaction;
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