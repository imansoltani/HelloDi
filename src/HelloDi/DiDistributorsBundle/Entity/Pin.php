<?php

namespace HelloDi\DiDistributorsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
    private $order;


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
     * @param \HelloDi\DiDistributorsBundle\Entity\User $user
     * @return Pin
     */
    public function setUser(\HelloDi\DiDistributorsBundle\Entity\User $user)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set sellerTransaction
     *
     * @param \HelloDi\AccountingBundle\Entity\Transaction $sellerTransaction
     * @return Pin
     */
    public function setSellerTransaction(\HelloDi\AccountingBundle\Entity\Transaction $sellerTransaction)
    {
        $this->sellerTransaction = $sellerTransaction;
    
        return $this;
    }

    /**
     * Get sellerTransaction
     *
     * @return \HelloDi\AccountingBundle\Entity\Transaction 
     */
    public function getSellerTransaction()
    {
        return $this->sellerTransaction;
    }

    /**
     * Set commissionerTransaction
     *
     * @param \HelloDi\AccountingBundle\Entity\Transaction $commissionerTransaction
     * @return Pin
     */
    public function setCommissionerTransaction(\HelloDi\AccountingBundle\Entity\Transaction $commissionerTransaction = null)
    {
        $this->commissionerTransaction = $commissionerTransaction;
    
        return $this;
    }

    /**
     * Get commissionerTransaction
     *
     * @return \HelloDi\AccountingBundle\Entity\Transaction 
     */
    public function getCommissionerTransaction()
    {
        return $this->commissionerTransaction;
    }

    /**
     * Set code
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Code $code
     * @return Pin
     */
    public function setCode(\HelloDi\DiDistributorsBundle\Entity\Code $code = null)
    {
        $this->code = $code;
    
        return $this;
    }

    /**
     * Get code
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\Code 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set order
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\OrderCode $order
     * @return Pin
     */
    public function setOrder(\HelloDi\DiDistributorsBundle\Entity\OrderCode $order = null)
    {
        $this->order = $order;
    
        return $this;
    }

    /**
     * Get order
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\OrderCode 
     */
    public function getOrder()
    {
        return $this->order;
    }
}