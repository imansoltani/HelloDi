<?php

namespace HelloDi\AggregatorBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use HelloDi\AccountingBundle\Entity\Transaction;
use HelloDi\CoreBundle\Entity\User;

/**
 * Pin
 *
 * @ORM\Table(name="pin")
 * @ORM\Entity
 */
class Pin
{
    const SALE = 1;
    const CREDIT_NOTE = 2;
    const DEAD_BEAT = 3;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    protected $date;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\CoreBundle\Entity\User", inversedBy="pins")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\AccountingBundle\Entity\Transaction")
     * @ORM\JoinColumn(name="trans_id", referencedColumnName="id", nullable=false)
     */
    protected $transaction;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\AccountingBundle\Entity\Transaction")
     * @ORM\JoinColumn(name="comm_trans_id", referencedColumnName="id", nullable=true)
     */
    protected $commissionerTransaction;

    /**
     * @ORM\ManyToMany(targetEntity="HelloDi\AggregatorBundle\Entity\Code", inversedBy="pins")
     * @ORM\JoinTable(name="pin_code")
     */
    protected $codes;

    /**
     * @ORM\Column(type="integer", nullable=false, name="`count`")
     */
    protected $count;

    /**
     * @ORM\Column(type="boolean", nullable=false, name="printed")
     */
    protected $printed = false;

    /**
     * @ORM\Column(type="smallint", nullable=false, name="type")
     */
    protected $type;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->codes = new ArrayCollection();
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
     * Set transaction
     *
     * @param Transaction $transaction
     * @return Pin
     */
    public function setTransaction(Transaction $transaction)
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * Get transaction
     *
     * @return Transaction
     */
    public function getTransaction()
    {
        return $this->transaction;
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
     * Add codes
     *
     * @param Code $codes
     * @return Pin
     */
    public function addCode(Code $codes)
    {
        $this->codes[] = $codes;
    
        return $this;
    }

    /**
     * Remove codes
     *
     * @param Code $codes
     */
    public function removeCode(Code $codes)
    {
        $this->codes->removeElement($codes);
    }

    /**
     * Get codes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCodes()
    {
        return $this->codes;
    }

    /**
     * Set count
     *
     * @param integer $count
     * @return Pin
     */
    public function setCount($count)
    {
        $this->count = $count;
    
        return $this;
    }

    /**
     * Get count
     *
     * @return integer 
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Set printed
     *
     * @param boolean $printed
     * @return Pin
     */
    public function setPrinted($printed)
    {
        $this->printed = $printed;
    
        return $this;
    }

    /**
     * Get printed
     *
     * @return boolean 
     */
    public function getPrinted()
    {
        return $this->printed;
    }

    /**
     * Set type
     *
     * @param integer $type
     * @return Pin
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return integer 
     */
    public function getType()
    {
        return $this->type;
    }
}