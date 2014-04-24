<?php
namespace HelloDi\AccountingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HelloDi\CoreBundle\Entity\User;

/**
 * Transfer
 *
 * @ORM\Table(name="transfer")
 * @ORM\Entity
 */
class Transfer
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
     * @ORM\ManyToOne(targetEntity="HelloDi\CoreBundle\Entity\User", inversedBy="transfers")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\AccountingBundle\Entity\Transaction", inversedBy="originTransfer")
     * @ORM\JoinColumn(name="origin_trans_id", referencedColumnName="id", nullable=true)
     */
    private $originTransaction;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\AccountingBundle\Entity\Transaction", inversedBy="destinationTransfer")
     * @ORM\JoinColumn(name="destination_trans_id", referencedColumnName="id", nullable=false)
     */
    private $destinationTransaction;

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
     * Set user
     *
     * @param User $user
     * @return Transfer
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
     * Set originTransaction
     *
     * @param Transaction $originTransaction
     * @return Transfer
     */
    public function setOriginTransaction(Transaction $originTransaction = null)
    {
        $this->originTransaction = $originTransaction;

        return $this;
    }

    /**
     * Get originTransaction
     *
     * @return Transaction
     */
    public function getOriginTransaction()
    {
        return $this->originTransaction;
    }

    /**
     * Set destinationTransaction
     *
     * @param Transaction $destinationTransaction
     * @return Transfer
     */
    public function setDestinationTransaction(Transaction $destinationTransaction)
    {
        $this->destinationTransaction = $destinationTransaction;

        return $this;
    }

    /**
     * Get destinationTransaction
     *
     * @return Transaction
     */
    public function getDestinationTransaction()
    {
        return $this->destinationTransaction;
    }
}