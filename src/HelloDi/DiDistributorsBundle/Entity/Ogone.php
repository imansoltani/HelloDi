<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ogone")
 */
class Ogone
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint", nullable=true, name="ogn_status")
     */
    private $ognStatus;

    /**
     * @ORM\Column(type="decimal", nullable=true, name="ogn_amount")
     */
    private $ognAmount;

    /**
     * @ORM\Column(type="string", length=3, nullable=false, name="ogn_currency")
     */
    private $ognCurrency;
    /**
     * @ORM\Column(type="integer", nullable=false, name="ogn_refrence")
     */
    private $ognRefrence;

    /**
     * @ORM\Column(type="date", nullable=false, name="ogn_date")
     */
    private $ognDate;
    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\User", inversedBy="Ogone")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $User;
    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Transaction", inversedBy="Ogone")
     * @ORM\JoinColumn(name="transaction_id", referencedColumnName="id", nullable=false)
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
     * Set ognStatus
     *
     * @param integer $ognStatus
     * @return Ogone
     */
    public function setOgnStatus($ognStatus)
    {
        $this->ognStatus = $ognStatus;
    
        return $this;
    }

    /**
     * Get ognStatus
     *
     * @return integer 
     */
    public function getOgnStatus()
    {
        return $this->ognStatus;
    }

    /**
     * Set ognAmount
     *
     * @param float $ognAmount
     * @return Ogone
     */
    public function setOgnAmount($ognAmount)
    {
        $this->ognAmount = $ognAmount;
    
        return $this;
    }

    /**
     * Get ognAmount
     *
     * @return float 
     */
    public function getOgnAmount()
    {
        return $this->ognAmount;
    }

    /**
     * Set ognCurrency
     *
     * @param string $ognCurrency
     * @return Ogone
     */
    public function setOgnCurrency($ognCurrency)
    {
        $this->ognCurrency = $ognCurrency;
    
        return $this;
    }

    /**
     * Get ognCurrency
     *
     * @return string 
     */
    public function getOgnCurrency()
    {
        return $this->ognCurrency;
    }

    /**
     * Set ognRefrence
     *
     * @param string $ognRefrence
     * @return Ogone
     */
    public function setOgnRefrence($ognRefrence)
    {
        $this->ognRefrence = $ognRefrence;
    
        return $this;
    }

    /**
     * Get ognRefrence
     *
     * @return string 
     */
    public function getOgnRefrence()
    {
        return $this->ognRefrence;
    }

    /**
     * Set ognDate
     *
     * @param \DateTime $ognDate
     * @return Ogone
     */
    public function setOgnDate($ognDate)
    {
        $this->ognDate = $ognDate;
    
        return $this;
    }

    /**
     * Get ognDate
     *
     * @return \DateTime 
     */
    public function getOgnDate()
    {
        return $this->ognDate;
    }

    /**
     * Set User
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\User $user
     * @return Ogone
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
     * @return Ogone
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
}