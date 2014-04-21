<?php

namespace HelloDi\DiDistributorsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HelloDi\AccountingBundle\Entity\Transaction;

/**
 * Pin
 *
 * @ORM\Table(name="pin")
 * @ORM\Entity
 */
class Pin
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\User", inversedBy="pins")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\AccountingBundle\Entity\Transaction", inversedBy="sellerPin")
     * @ORM\JoinColumn(name="sell_trans_id", referencedColumnName="id", nullable=false)
     */
    private $sellerTransaction;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\AccountingBundle\Entity\Transaction", inversedBy="commissionerPin")
     * @ORM\JoinColumn(name="comm_trans_id", referencedColumnName="id", nullable=true)
     */
    private $commissionerTransaction;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Code", inversedBy="pins")
     * @ORM\JoinColumn(name="code_id", referencedColumnName="id", nullable=true)
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\OrderCode", inversedBy="pins")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=true)
     */
    private $orderCode;

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
     * Set date
     *
     * @param \DateTime $date
     * @return Pin
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
     * Set user
     *
     * @param User $user
     * @return Pin
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
     * Set sellerTransaction
     *
     * @param Transaction $sellerTransaction
     * @return Pin
     */
    public function setSellerTransaction(Transaction $sellerTransaction)
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
     * @return Pin
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
     * Set code
     *
     * @param Code $code
     * @return Pin
     */
    public function setCode(Code $code = null)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return Code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set orderCode
     *
     * @param OrderCode $orderCode
     * @return Pin
     */
    public function setOrderCode(OrderCode $orderCode = null)
    {
        $this->orderCode = $orderCode;

        return $this;
    }

    /**
     * Get orderCode
     *
     * @return OrderCode
     */
    public function getOrderCode()
    {
        return $this->orderCode;
    }
}