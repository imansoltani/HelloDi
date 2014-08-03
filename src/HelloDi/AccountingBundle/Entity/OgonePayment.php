<?php
namespace HelloDi\AccountingBundle\Entity;

use HelloDi\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="HelloDi\AccountingBundle\Entity\OgonePaymentRepository")
 * @ORM\Table(name="ogone_payment")
 */
class OgonePayment
{
    const OGONE_RESULT_ACCEPTED = 5;
    const OGONE_RESULT_CANCELED = 1;
    const OGONE_RESULT_DECLINED = 2;
    const OGONE_RESULT_EXCEPTION = 52;

    const STATUS_ACCEPTED = 'accepted';
    const STATUS_CANCELED = 'canceled';
    const STATUS_DECLINED = 'declined';
    const STATUS_PENDING = 'pending';
    const STATUS_UNCERTAIN = 'uncertain';
    const STATUS_UNKNOWN = 'unknown';

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="amount", type="float")
     * @Assert\Range(min=100)
     * @Assert\NotBlank
     */
    protected $paymentAmount;

    /**
     * @ORM\Column(name="currency_iso", type="string", length=3)
     * @Assert\NotBlank
     */
    protected $paymentCurrencyISO;

    /**
     * @ORM\Column(name="status", type="string")
     * @Assert\NotBlank
     */
    protected $status;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="ogone_ref", type="string", length=10, nullable=true)
     */
    protected $ogoneRef;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\CoreBundle\Entity\User", inversedBy="ogonePayment")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    protected $user;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\AccountingBundle\Entity\Transaction")
     * @ORM\JoinColumn(name="transaction_id", referencedColumnName="id", nullable=true)
     */
    protected $transaction;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getPaymentAmount()
    {
        return $this->paymentAmount;
    }

    /**
     * @param float $paymentAmount
     */
    public function setPaymentAmount($paymentAmount)
    {
        $this->paymentAmount = round($paymentAmount, 2);
    }

    /**
     * @return float
     */
    public function getOgoneAmount()
    {
        return $this->paymentAmount * 100;
    }

    /**
     * @return string
     */
    public function getPaymentCurrencyISO()
    {
        return $this->paymentCurrencyISO;
    }

    /**
     * @param string $paymentCurrencyISO
     */
    public function setPaymentCurrencyISO($paymentCurrencyISO)
    {
        $this->paymentCurrencyISO = $paymentCurrencyISO;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return string
     */
    public function getOgoneRef()
    {
        return $this->ogoneRef;
    }

    /**
     * @param string $ogoneRef
     */
    public function setOgoneRef($ogoneRef)
    {
        $this->ogoneRef = $ogoneRef;
    }

    /**
     * @return string
     */
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

    /**
     * @return bool
     */
    public function isProcessed()
    {
        return static::STATUS_PENDING !== $this->getStatus();
    }

    /**
     * @return bool
     */
    public function isAccepted()
    {
        return static::STATUS_ACCEPTED === $this->getStatus();
    }

    /**
     * @return bool
     */
    public function isCanceled()
    {
        return static::STATUS_CANCELED === $this->getStatus();
    }
}