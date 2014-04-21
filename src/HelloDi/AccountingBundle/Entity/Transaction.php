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
     * @ORM\Column(type="decimal", nullable=true, precision=6, scale=2, name="booking_value")
     */
    private $bookingValue;

    /**
     * @ORM\Column(type="decimal", nullable=false, name="amount", precision=6, scale=2)
     */
    private $amount;

    /**
     * @ORM\Column(type="decimal", nullable=false, name="fees", precision=6, scale=2)
     */
    private $fees = 0.0;

    /**
     * @ORM\Column(type="datetime", nullable=false, name="date")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=45, nullable=false, name="description")
     */
    private $description = "";

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\AccountingBundle\Entity\Account", inversedBy="transactions")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     */
    private $account;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->date = new \DateTime();
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
     * Set bookingValue
     *
     * @param string $bookingValue
     * @return Transaction
     */
    public function setBookingValue($bookingValue)
    {
        $this->bookingValue = $bookingValue;

        return $this;
    }

    /**
     * Get bookingValue
     *
     * @return string
     */
    public function getBookingValue()
    {
        return $this->bookingValue;
    }

    /**
     * Set amount
     *
     * @param string $amount
     * @return Transaction
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set fees
     *
     * @param string $fees
     * @return Transaction
     */
    public function setFees($fees)
    {
        $this->fees = $fees;

        return $this;
    }

    /**
     * Get fees
     *
     * @return string
     */
    public function getFees()
    {
        return $this->fees;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Transaction
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
     * Set description
     *
     * @param string $description
     * @return Transaction
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set account
     *
     * @param Account $account
     * @return Transaction
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @ORM\PrePersist
     */
    public function updateAccountBalance()
    {
        $amount = $this->getAmount();
        $currentBalance = $this->getAccount()->getBalance();
        /** @var float $amount */
        /** @var float $currentBalance */
        $this->getAccount()->setBalance($currentBalance + $amount);
    }
}