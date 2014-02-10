<?php
namespace HelloDi\AccountingBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use HelloDi\DiDistributorsBundle\Entity\B2BLog;
use HelloDi\DiDistributorsBundle\Entity\Pin;

/**
 * @ORM\Entity
 * @ORM\Table(name="transaction")
 * @ORM\HasLifecycleCallbacks()
 */
class Transaction
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="decimal", nullable=true, precision=6, scale=2, name="tran_booking_value")
     */
    private $tranBookingValue;

    /**
     * @ORM\Column(type="decimal", nullable=false, name="tran_Amount", precision=6, scale=2)
     */
    private $tranAmount;

    /**
     * @ORM\Column(type="decimal", nullable=false, name="tran_fees", precision=6, scale=2)
     */
    private $tranFees = 0.0;

    /**
     * @ORM\Column(type="datetime", nullable=false, name="tran_date")
     */
    private $tranDate;

    /**
     * @ORM\Column(type="string", length=45, nullable=true, name="tran_description")
     */
    private $tranDescription;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\AccountingBundle\Entity\Account", inversedBy="Transactions")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     */
    private $Account;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\AccountingBundle\Entity\Transfer", mappedBy="originTransaction", cascade={"persist"})
     */
    private $originTransfer;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\AccountingBundle\Entity\Transfer", mappedBy="destinationTransaction", cascade={"persist"})
     */
    private $destinationTransfer;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\AccountingBundle\Entity\CreditLimit", mappedBy="transaction", cascade={"persist"})
     */
    private $creditLimit;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\AccountingBundle\Entity\OgonePayment", mappedBy="transaction", cascade={"persist"})
     */
    private $ogonePayment;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\B2BLog", mappedBy="sellerTransaction", cascade={"persist"})
     */
    private $sellerB2BLog;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\B2BLog", mappedBy="commissionerTransaction", cascade={"persist"})
     */
    private $commissionerB2BLog;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Pin", mappedBy="sellerTransaction", cascade={"persist"})
     */
    private $sellerPin;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Pin", mappedBy="commissionerTransaction", cascade={"persist"})
     */
    private $commissionerPin;

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
     * Set tranBookingValue
     *
     * @param float $tranBookingValue
     * @return Transaction
     */
    public function setTranBookingValue($tranBookingValue)
    {
        $this->tranBookingValue = $tranBookingValue;

        return $this;
    }

    /**
     * Get tranBookingValue
     *
     * @return float
     */
    public function getTranBookingValue()
    {
        return $this->tranBookingValue;
    }

    /**
     * Set tranFees
     *
     * @param float $tranFees
     * @return Transaction
     */
    public function setTranFees($tranFees)
    {
        $this->tranFees = $tranFees;

        return $this;
    }

    /**
     * Get tranFees
     *
     * @return float
     */
    public function getTranFees()
    {
        return $this->tranFees;
    }

    /**
     * Set tranDate
     *
     * @param \DateTime $tranDate
     * @return Transaction
     */
    public function setTranDate($tranDate)
    {
        $this->tranDate = $tranDate;

        return $this;
    }

    /**
     * Get tranDate
     *
     * @return \DateTime
     */
    public function getTranDate()
    {
        return $this->tranDate;
    }

    /**
     * Set tranDescription
     *
     * @param string $tranDescription
     * @return Transaction
     */
    public function setTranDescription($tranDescription)
    {
        $this->tranDescription = $tranDescription;

        return $this;
    }

    /**
     * Get tranDescription
     *
     * @return string
     */
    public function getTranDescription()
    {
        return $this->tranDescription;
    }

    /**
     * Set Account
     *
     * @param Account $account
     * @return Transaction
     */
    public function setAccount(Account $account)
    {
        $this->Account = $account;

        return $this;
    }

    /**
     * Get Account
     *
     * @return Account
     */
    public function getAccount()
    {
        return $this->Account;
    }

    /**
     * Set tranAmount
     *
     * @param float $tranAmount
     * @return Transaction
     */
    public function setTranAmount($tranAmount)
    {
        $this->tranAmount = $tranAmount;
    
        return $this;
    }

    /**
     * Get tranAmount
     *
     * @return float 
     */
    public function getTranAmount()
    {
        return $this->tranAmount;
    }

    /**
     * @ORM\PrePersist
     */

    public function updateAccountBalance()
    {
        $amount=$this->getTranAmount();
        $currentBalance=$this->getAccount()->getAccBalance();
        $this->getAccount()->setAccBalance($currentBalance+$amount);
    }

    /**
     * Set originTransfer
     *
     * @param Transfer $originTransfer
     * @return Transaction
     */
    public function setOriginTransfer(Transfer $originTransfer = null)
    {
        $this->originTransfer = $originTransfer;
    
        return $this;
    }

    /**
     * Get originTransfer
     *
     * @return Transfer
     */
    public function getOriginTransfer()
    {
        return $this->originTransfer;
    }

    /**
     * Set destinationTransfer
     *
     * @param Transfer $destinationTransfer
     * @return Transaction
     */
    public function setDestinationTransfer(Transfer $destinationTransfer = null)
    {
        $this->destinationTransfer = $destinationTransfer;
    
        return $this;
    }

    /**
     * Get destinationTransfer
     *
     * @return Transfer
     */
    public function getDestinationTransfer()
    {
        return $this->destinationTransfer;
    }

    /**
     * Set creditLimit
     *
     * @param CreditLimit $creditLimit
     * @return Transaction
     */
    public function setCreditLimit(CreditLimit $creditLimit = null)
    {
        $this->creditLimit = $creditLimit;
    
        return $this;
    }

    /**
     * Get creditLimit
     *
     * @return CreditLimit
     */
    public function getCreditLimit()
    {
        return $this->creditLimit;
    }

    /**
     * Set ogonePayment
     *
     * @param OgonePayment $ogonePayment
     * @return Transaction
     */
    public function setOgonePayment(OgonePayment $ogonePayment = null)
    {
        $this->ogonePayment = $ogonePayment;
    
        return $this;
    }

    /**
     * Get ogonePayment
     *
     * @return OgonePayment
     */
    public function getOgonePayment()
    {
        return $this->ogonePayment;
    }

    /**
     * Set sellerB2BLog
     *
     * @param B2BLog $sellerB2BLog
     * @return Transaction
     */
    public function setSellerB2BLog(B2BLog $sellerB2BLog = null)
    {
        $this->sellerB2BLog = $sellerB2BLog;
    
        return $this;
    }

    /**
     * Get sellerB2BLog
     *
     * @return B2BLog
     */
    public function getSellerB2BLog()
    {
        return $this->sellerB2BLog;
    }

    /**
     * Set commissionerB2BLog
     *
     * @param B2BLog $commissionerB2BLog
     * @return Transaction
     */
    public function setCommissionerB2BLog(B2BLog $commissionerB2BLog = null)
    {
        $this->commissionerB2BLog = $commissionerB2BLog;
    
        return $this;
    }

    /**
     * Get commissionerB2BLog
     *
     * @return B2BLog
     */
    public function getCommissionerB2BLog()
    {
        return $this->commissionerB2BLog;
    }

    /**
     * Set sellerPin
     *
     * @param Pin $sellerPin
     * @return Transaction
     */
    public function setSellerPin(Pin $sellerPin = null)
    {
        $this->sellerPin = $sellerPin;
    
        return $this;
    }

    /**
     * Get sellerPin
     *
     * @return Pin
     */
    public function getSellerPin()
    {
        return $this->sellerPin;
    }

    /**
     * Set commissionerPin
     *
     * @param Pin $commissionerPin
     * @return Transaction
     */
    public function setCommissionerPin(Pin $commissionerPin = null)
    {
        $this->commissionerPin = $commissionerPin;
    
        return $this;
    }

    /**
     * Get commissionerPin
     *
     * @return Pin
     */
    public function getCommissionerPin()
    {
        return $this->commissionerPin;
    }
}