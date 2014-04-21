<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\AccountingBundle\Entity\CreditLimit;
use HelloDi\AccountingBundle\Entity\Transfer;
use HelloDi\UserBundle\Entity\User as BaseUser;
use HelloDi\AccountingBundle\Entity\OgonePayment;

/** 
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** 
     * @ORM\Column(type="string", length=45, nullable=false, name="firstName")
     */
    private $firstName;

    /** 
     * @ORM\Column(type="string", length=45, nullable=true, name="lastName")
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=45, nullable=true, name="mobile")
     */
    private $mobile;

    /** 
     * @ORM\Column(type="string", length=2, nullable=false, name="language")
     */
    private $language;

    /** 
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Input", mappedBy="User")
     */
    private $Inputs;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Ticket", mappedBy="User")
     */
    private $Tickets;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\TicketNote", mappedBy="User")
     */
    private $TicketNotes;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\AccountingBundle\Entity\Transfer", mappedBy="user")
     */
    private $transfers;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Entity", inversedBy="users")
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id", nullable=false)
     */
    private $entity;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\AccountingBundle\Entity\Account", inversedBy="users")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=true)
     */
    private $account;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\AccountingBundle\Entity\OgonePayment", mappedBy="user")
     */
    private $ogonePayment;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\B2BLog", mappedBy="User")
     */
    private $B2BLogs;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\AccountingBundle\Entity\CreditLimit", mappedBy="user")
     */
    private $creditLimits;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Pin", mappedBy="user")
     */
    private $pins;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->Inputs = new ArrayCollection();
        $this->removeElement = new ArrayCollection();
        $this->Tickets = new ArrayCollection();
        $this->TicketNotes = new ArrayCollection();
        $this->transfers = new ArrayCollection();
        $this->OgonePayment = new ArrayCollection();
        $this->B2BLogs = new ArrayCollection();
        $this->creditLimits = new ArrayCollection();
        $this->pins = new ArrayCollection();
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
     * Set firstName
     *
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    
        return $this;
    }

    /**
     * Get firstName
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    
        return $this;
    }

    /**
     * Get lastName
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set mobile
     *
     * @param string $mobile
     * @return User
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
    
        return $this;
    }

    /**
     * Get mobile
     *
     * @return string 
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Set language
     *
     * @param string $language
     * @return User
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    
        return $this;
    }

    /**
     * Get language
     *
     * @return string 
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set status
     *
     * @param boolean $status
     * @return User
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return boolean 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Add Inputs
     *
     * @param Input $inputs
     * @return User
     */
    public function addInput(Input $inputs)
    {
        $this->Inputs[] = $inputs;
    
        return $this;
    }

    /**
     * Remove Inputs
     *
     * @param Input $inputs
     */
    public function removeInput(Input $inputs)
    {
        $this->Inputs->removeElement($inputs);
    }

    /**
     * Get Inputs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInputs()
    {
        return $this->Inputs;
    }

    /**
     * Add Tickets
     *
     * @param Ticket $tickets
     * @return User
     */
    public function addTicket(Ticket $tickets)
    {
        $this->Tickets[] = $tickets;
    
        return $this;
    }

    /**
     * Remove Tickets
     *
     * @param Ticket $tickets
     */
    public function removeTicket(Ticket $tickets)
    {
        $this->Tickets->removeElement($tickets);
    }

    /**
     * Get Tickets
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTickets()
    {
        return $this->Tickets;
    }

    /**
     * Add TicketNotes
     *
     * @param TicketNote $ticketNotes
     * @return User
     */
    public function addTicketNote(TicketNote $ticketNotes)
    {
        $this->TicketNotes[] = $ticketNotes;
    
        return $this;
    }

    /**
     * Remove TicketNotes
     *
     * @param TicketNote $ticketNotes
     */
    public function removeTicketNote(TicketNote $ticketNotes)
    {
        $this->TicketNotes->removeElement($ticketNotes);
    }

    /**
     * Get TicketNotes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTicketNotes()
    {
        return $this->TicketNotes;
    }

    /**
     * Set Entity
     *
     * @param Entity $entity
     * @return User
     */
    public function setEntity(Entity $entity)
    {
        $this->entity = $entity;
    
        return $this;
    }

    /**
     * Get Entity
     *
     * @return Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set Account
     *
     * @param Account $account
     * @return User
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;
    
        return $this;
    }

    /**
     * Get Account
     *
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Add OgonePayment
     *
     * @param OgonePayment $ogonePayment
     * @return User
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
     * Add B2BLogs
     *
     * @param B2BLog $b2BLogs
     * @return User
     */
    public function addB2BLog(B2BLog $b2BLogs)
    {
        $this->B2BLogs[] = $b2BLogs;
    
        return $this;
    }

    /**
     * Remove B2BLogs
     *
     * @param B2BLog $b2BLogs
     */
    public function removeB2BLog(B2BLog $b2BLogs)
    {
        $this->B2BLogs->removeElement($b2BLogs);
    }

    /**
     * Get B2BLogs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getB2BLogs()
    {
        return $this->B2BLogs;
    }

    /**
     * Add transfers
     *
     * @param Transfer $transfers
     * @return User
     */
    public function addTransfer(Transfer $transfers)
    {
        $this->transfers[] = $transfers;
    
        return $this;
    }

    /**
     * Remove transfers
     *
     * @param Transfer $transfers
     */
    public function removeTransfer(Transfer $transfers)
    {
        $this->transfers->removeElement($transfers);
    }

    /**
     * Get transfers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTransfers()
    {
        return $this->transfers;
    }

    /**
     * Add creditLimits
     *
     * @param CreditLimit $creditLimits
     * @return User
     */
    public function addCreditLimit(CreditLimit $creditLimits)
    {
        $this->creditLimits[] = $creditLimits;
    
        return $this;
    }

    /**
     * Remove creditLimits
     *
     * @param CreditLimit $creditLimits
     */
    public function removeCreditLimit(CreditLimit $creditLimits)
    {
        $this->creditLimits->removeElement($creditLimits);
    }

    /**
     * Get creditLimits
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCreditLimits()
    {
        return $this->creditLimits;
    }

    /**
     * Add Pins
     *
     * @param Pin $pins
     * @return User
     */
    public function addPin(Pin $pins)
    {
        $this->pins[] = $pins;
    
        return $this;
    }

    /**
     * Remove Pins
     *
     * @param Pin $pins
     */
    public function removePin(Pin $pins)
    {
        $this->pins->removeElement($pins);
    }

    /**
     * Get Pins
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPins()
    {
        return $this->pins;
    }
}