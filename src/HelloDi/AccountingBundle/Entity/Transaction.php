<?php
namespace HelloDi\AccountingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use HelloDi\DiDistributorsBundle\Entity\B2BLog;
use HelloDi\DiDistributorsBundle\Entity\Code;
use HelloDi\DiDistributorsBundle\Entity\OrderCode;
use HelloDi\DiDistributorsBundle\Entity\TaxHistory;
use HelloDi\DiDistributorsBundle\Entity\User;

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
     * @ORM\Column(type="decimal", nullable=true, scale=2, name="tran_booking_value")
     */
    private $tranBookingValue;

    /**
     * @ORM\Column(type="integer", nullable=true, name="tran_type")
     */
    private $tranType;

    /**
     * @ORM\Column(type="decimal", nullable=true, name="tran_Amount", scale=2)
     */
    private $tranAmount;

    /**
     * @ORM\Column(type="decimal", nullable=false, name="tran_fees", scale=2)
     */
    private $tranFees;

    /**
     * @ORM\Column(type="string", length=3, nullable=false, name="tran_currency")
     */
    private $tranCurrency;

    /**
     * @ORM\Column(type="date", nullable=false, name="tran_date")
     */
    private $tranDate;

    /**
     * @ORM\Column(type="datetime", nullable=true, name="tran_insert")
     */
    private $tranInsert;

    /**
     * @ORM\Column(type="string", length=45, nullable=true, name="tran_description")
     */
    private $tranDescription;

    /**
     * @ORM\Column(type="string", length=30, nullable=false, name="tran_action")
     */
    private $tranAction;

    /**
     * @ORM\Column(type="decimal", nullable=true, name="tran_balance", scale=2)
     */
    private $tranBalance;

    /**
     * @ORM\Column(type="decimal", nullable=true, name="buying_price", scale=2)
     */
    private $BuyingPrice;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\TaxHistory", inversedBy="Transactions")
     * @ORM\JoinColumn(name="tax_history_id", referencedColumnName="id", nullable=true)
     */
    private $TaxHistory;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\AccountingBundle\Entity\Account", inversedBy="Transactions")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     */
    private $Account;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\User", inversedBy="Transactions")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $User;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Code", inversedBy="Transactions")
     * @ORM\JoinColumn(name="code_id", referencedColumnName="id", nullable=true)
     */
    private $Code;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\OrderCode", inversedBy="Transactions")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=true)
     */
    private $Order;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\B2BLog", inversedBy="Transactions")
     * @ORM\JoinColumn(name="b2blog_id", referencedColumnName="id", nullable=true)
     */
    private $B2BLog;

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
     * Set tranCurrency
     *
     * @param string $tranCurrency
     * @return Transaction
     */
    public function setTranCurrency($tranCurrency)
    {
        $this->tranCurrency = $tranCurrency;

        return $this;
    }

    /**
     * Get tranCurrency
     *
     * @return string
     */
    public function getTranCurrency()
    {
        return $this->tranCurrency;
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
     * Set tranInsert
     *
     * @param \DateTime $tranInsert
     * @return Transaction
     */
    public function setTranInsert($tranInsert)
    {
        $this->tranInsert = $tranInsert;

        return $this;
    }

    /**
     * Get tranInsert
     *
     * @return \DateTime
     */
    public function getTranInsert()
    {
        return $this->tranInsert;
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
     * Set tranAction
     *
     * @param string $tranAction
     * @return Transaction
     */
    public function setTranAction($tranAction)
    {
        $this->tranAction = $tranAction;

        return $this;
    }

    /**
     * Get tranAction
     *
     * @return string
     */
    public function getTranAction()
    {
        return $this->tranAction;
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
     * Set User
     *
     * @param User $user
     * @return Transaction
     */
    public function setUser(User $user)
    {
        $this->User = $user;

        return $this;
    }

    /**
     * Get User
     *
     * @return User
     */
    public function getUser()
    {
        return $this->User;
    }

    /**
     * Set Code
     *
     * @param Code $code
     * @return Transaction
     */
    public function setCode(Code $code)
    {
        $this->Code = $code;

        return $this;
    }

    /**
     * Get Code
     *
     * @return Code
     */
    public function getCode()
    {
        return $this->Code;
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
     * Constructor
     */
    public function __construct()
    {
        $this->OgonePayment = new ArrayCollection();
    }
    
    /**
     * Add OgonePayment
     *
     * @param OgonePayment $ogonePayment
     * @return Transaction
     */
    public function addOgonePayment(OgonePayment $ogonePayment)
    {
        $this->OgonePayment[] = $ogonePayment;
    
        return $this;
    }

    /**
     * Remove OgonePayment
     *
     * @param OgonePayment $ogonePayment
     */
    public function removeOgonePayment(OgonePayment $ogonePayment)
    {
        $this->OgonePayment->removeElement($ogonePayment);
    }

    /**
     * Get OgonePayment
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOgonePayment()
    {
        return $this->OgonePayment;
    }

    /**
     * Set tranType
     *
     * @param integer $tranType
     * @return Transaction
     */
    public function setTranType($tranType)
    {
        $this->tranType = $tranType;
    
        return $this;
    }

    /**
     * Get tranType
     *
     * @return integer 
     */
    public function getTranType()
    {
        return $this->tranType;
    }

    /**
     * Set tranBalance
     *
     * @param float $tranBalance
     * @return Transaction
     */
    public function setTranBalance($tranBalance)
    {
        $this->tranBalance = $tranBalance;
    
        return $this;
    }

    /**
     * Get tranBalance
     *
     * @return float 
     */
    public function getTranBalance()
    {
        return $this->tranBalance;
    }

    /**
     * Set Order
     *
     * @param OrderCode $order
     * @return Transaction
     */
    public function setOrder(OrderCode $order = null)
    {
        $this->Order = $order;
    
        return $this;
    }

    /**
     * Get Order
     *
     * @return OrderCode
     */
    public function getOrder()
    {
        return $this->Order;
    }

    /**
     * Set BuyingPrice
     *
     * @param float $buyingPrice
     * @return Transaction
     */
    public function setBuyingPrice($buyingPrice)
    {
        $this->BuyingPrice = $buyingPrice;
    
        return $this;
    }

    /**
     * Get BuyingPrice
     *
     * @return float
     */
    public function getBuyingPrice()
    {
        return $this->BuyingPrice;
    }

    /**
     * Set TaxHistory
     *
     * @param TaxHistory $taxHistory
     * @return Transaction
     */
    public function setTaxHistory(TaxHistory $taxHistory = null)
    {
        $this->TaxHistory = $taxHistory;
    
        return $this;
    }

    /**
     * Get TaxHistory
     *
     * @return TaxHistory
     */
    public function getTaxHistory()
    {
        return $this->TaxHistory;
    }

    /**
     * Set B2BLog
     *
     * @param B2BLog $b2BLog
     * @return Transaction
     */
    public function setB2BLog(B2BLog $b2BLog = null)
    {
        $this->B2BLog = $b2BLog;
    
        return $this;
    }

    /**
     * Get B2BLog
     *
     * @return B2BLog
     */
    public function getB2BLog()
    {
        return $this->B2BLog;
    }
}