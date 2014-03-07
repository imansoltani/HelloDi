<?php
namespace HelloDi\AccountingBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;

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
     * @ORM\Column(type="string", length=45, nullable=false, name="tran_description")
     */
    private $tranDescription = "";

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\AccountingBundle\Entity\Account", inversedBy="Transactions")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     */
    private $Account;

    public function __construct()
    {
        $this->tranDate = new \DateTime();
    }

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
}