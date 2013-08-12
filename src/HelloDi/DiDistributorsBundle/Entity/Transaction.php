<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as CTRL;

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
     * @ORM\Column(type="decimal", nullable=true)
     */

    private $tranBookingValue;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */

    private $tranType;

    /**
     * @ORM\Column(type="decimal", nullable=true, name="tran_credit")
     */

    private $tranCredit;

    /**
     * @ORM\Column(type="decimal", nullable=true, name="tran_Amount")
     */
    private $tranAmount;

    /**
     * @ORM\Column(type="decimal", nullable=true, name="tran_debit")
     */
    private $tranDebit;

    /**
     * @ORM\Column(type="decimal", nullable=false, name="tran_fees")
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
     * @ORM\Column(type="string", length=30, nullable=false)
     */
    private $tranAction;

    /**
     * @ORM\Column(type="decimal", nullable=true, name="tran_balance")
     */
    private $tranBalance;

    /**
     * @ORM\Column(type="float", nullable=true, name="tax")
     */
    private $tax;


    /**
     * @ORM\Column(type="decimal", nullable=true, name="Buying_Price")
     */
    private $BuyingPrice;


    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Account", inversedBy="Transactions")
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
     * Set tranCredit
     *
     * @param float $tranCredit
     * @return Transaction
     */
    public function setTranCredit($tranCredit)
    {
        $this->tranCredit = $tranCredit;

        return $this;
    }

    /**
     * Get tranCredit
     *
     * @return float
     */
    public function getTranCredit()
    {
        return $this->tranCredit;
    }

    /**
     * Set tranDebit
     *
     * @param float $tranDebit
     * @return Transaction
     */
    public function setTranDebit($tranDebit)
    {
        $this->tranDebit = $tranDebit;

        return $this;
    }

    /**
     * Get tranDebit
     *
     * @return float
     */
    public function getTranDebit()
    {
        return $this->tranDebit;
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
     * @param \HelloDi\DiDistributorsBundle\Entity\Account $account
     * @return Transaction
     */
    public function setAccount(\HelloDi\DiDistributorsBundle\Entity\Account $account)
    {
        $this->Account = $account;

        return $this;
    }

    /**
     * Get Account
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\Account
     */
    public function getAccount()
    {
        return $this->Account;
    }

    /**
     * Set User
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\User $user
     * @return Transaction
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
     * Set Code
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Code $code
     * @return Transaction
     */
    public function setCode(\HelloDi\DiDistributorsBundle\Entity\Code $code)
    {
        $this->Code = $code;

        return $this;
    }

    /**
     * Get Code
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\Code
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

//    public function __construct($entityManager)
//    {
//        $this->entityManager = $entityManager;
//    }

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
        $this->OgonePayment = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add OgonePayment
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\OgonePayment $ogonePayment
     * @return Transaction
     */
    public function addOgonePayment(\HelloDi\DiDistributorsBundle\Entity\OgonePayment $ogonePayment)
    {
        $this->OgonePayment[] = $ogonePayment;
    
        return $this;
    }

    /**
     * Remove OgonePayment
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\OgonePayment $ogonePayment
     */
    public function removeOgonePayment(\HelloDi\DiDistributorsBundle\Entity\OgonePayment $ogonePayment)
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
     * @param \HelloDi\DiDistributorsBundle\Entity\OrderCode $order
     * @return Transaction
     */
    public function setOrder(\HelloDi\DiDistributorsBundle\Entity\OrderCode $order = null)
    {
        $this->Order = $order;
    
        return $this;
    }

    /**
     * Get Order
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\OrderCode 
     */
    public function getOrder()
    {
        return $this->Order;
    }

    /**
     * Set tax
     *
     * @param float $tax
     * @return Transaction
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
    
        return $this;
    }

    /**
     * Get tax
     *
     * @return float 
     */
    public function getTax()
    {
        return $this->tax;
    }





    /**
     * Set BuyingPrice
     *
     * @param decimal $buyingPrice
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
     * @return decimal
     */
    public function getBuyingPrice()
    {
        return $this->BuyingPrice;
    }
}