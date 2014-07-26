<?php

namespace HelloDi\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @ORM\ManyToOne(targetEntity="HelloDi\CoreBundle\Entity\User", inversedBy="pins")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\AccountingBundle\Entity\Transaction")
     * @ORM\JoinColumn(name="trans_id", referencedColumnName="id", nullable=false)
     */
    private $transaction;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\AccountingBundle\Entity\Transaction")
     * @ORM\JoinColumn(name="comm_trans_id", referencedColumnName="id", nullable=true)
     */
    private $commissionerTransaction;

    /**
     * @ORM\ManyToMany(targetEntity="HelloDi\CoreBundle\Entity\Code", inversedBy="pins")
     */
    private $codes;

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
}