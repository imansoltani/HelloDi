<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use HelloDi\AccountingBundle\Entity\Account;

/** 
 * @ORM\Entity
 * @ORM\Table(name="ticket")
 */
class Ticket
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** 
     * @ORM\Column(type="string", length=45, nullable=false, name="subject")
     */
    private $subject;

    /** 
     * @ORM\Column(type="datetime", nullable=false, name="ticket_start")
     */
    private $ticketStart;

    /** 
     * @ORM\Column(type="datetime", nullable=true, name="ticket_end")
     */
    private $ticketEnd;

    /** 
     * @ORM\Column(type="integer", nullable=false)
     */
    private $type;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $Status;
    
    /** 
     * @ORM\Column(type="integer", nullable=true)
     */
    private $inchange;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\TicketNote", mappedBy="Ticket")
     */
    private $TicketNotes;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\AccountingBundle\Entity\Account", inversedBy="ticketsDist")
     * @ORM\JoinColumn(name="account_dist_id", referencedColumnName="id", nullable=true)
     */
    private $accountDist;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\AccountingBundle\Entity\Account", inversedBy="ticketsRetailer")
     * @ORM\JoinColumn(name="account_retailer_id", referencedColumnName="id", nullable=true)
     */
    private $accountRetailer;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\User", inversedBy="Tickets")
     * @ORM\JoinColumn(name="last_user_id", referencedColumnName="id", nullable=false)
     */
    private $lastUser;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->TicketNotes = new ArrayCollection();
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
     * Set subject
     *
     * @param string $subject
     * @return Ticket
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    
        return $this;
    }

    /**
     * Get subject
     *
     * @return string 
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set ticketStart
     *
     * @param \DateTime $ticketStart
     * @return Ticket
     */
    public function setTicketStart($ticketStart)
    {
        $this->ticketStart = $ticketStart;
    
        return $this;
    }

    /**
     * Get ticketStart
     *
     * @return \DateTime 
     */
    public function getTicketStart()
    {
        return $this->ticketStart;
    }

    /**
     * Set ticketEnd
     *
     * @param \DateTime $ticketEnd
     * @return Ticket
     */
    public function setTicketEnd($ticketEnd)
    {
        $this->ticketEnd = $ticketEnd;
    
        return $this;
    }

    /**
     * Get ticketEnd
     *
     * @return \DateTime 
     */
    public function getTicketEnd()
    {
        return $this->ticketEnd;
    }

    /**
     * Set type
     *
     * @param integer $type
     * @return Ticket
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

    /**
     * Add TicketNotes
     *
     * @param TicketNote $ticketNotes
     * @return Ticket
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
     * Set Status
     *
     * @param integer $status
     * @return Ticket
     */
    public function setStatus($status)
    {
        $this->Status = $status;
    
        return $this;
    }

    /**
     * Get Status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->Status;
    }

    /**
     * Set Accountdist
     *
     * @param Account $accountDist
     * @return Ticket
     */
    public function setAccountDist(Account $accountDist)
    {
        $this->accountDist = $accountDist;
    
        return $this;
    }

    /**
     * Get Accountdist
     *
     * @return Account
     */
    public function getAccountDist()
    {
        return $this->accountDist;
    }

    /**
     * Set AccountRetailer
     *
     * @param Account $accountRetailer
     * @return Ticket
     */
    public function setAccountRetailer(Account $accountRetailer)
    {
        $this->accountRetailer = $accountRetailer;
    
        return $this;
    }

    /**
     * Get Accountretailer
     *
     * @return Account
     */
    public function getAccountRetailer()
    {
        return $this->accountRetailer;
    }

    /**
     * Set inchange
     *
     * @param integer $inchange
     * @return Ticket
     */
    public function setInchange($inchange)
    {
        $this->inchange = $inchange;
    
        return $this;
    }

    /**
     * Get inchange
     *
     * @return integer 
     */
    public function getInchange()
    {
        return $this->inchange;
    }

    /**
     * Set lastUser
     *
     * @param User $lastUser
     * @return Ticket
     */
    public function setLastUser(User $lastUser)
    {
        $this->lastUser = $lastUser;
    
        return $this;
    }

    /**
     * Get lastUser
     *
     * @return User
     */
    public function getLastUser()
    {
        return $this->lastUser;
    }
}