<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

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
    private $importance;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\TicketNote", mappedBy="Ticket")
     */
    private $TicketNotes;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Account", inversedBy="Tickets")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     */
    private $Account;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\User", inversedBy="Tickets")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $User;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->TicketNotes = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set importance
     *
     * @param integer $importance
     * @return Ticket
     */
    public function setImportance($importance)
    {
        $this->importance = $importance;
    
        return $this;
    }

    /**
     * Get importance
     *
     * @return integer 
     */
    public function getImportance()
    {
        return $this->importance;
    }

    /**
     * Add TicketNotes
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\TicketNote $ticketNotes
     * @return Ticket
     */
    public function addTicketNote(\HelloDi\DiDistributorsBundle\Entity\TicketNote $ticketNotes)
    {
        $this->TicketNotes[] = $ticketNotes;
    
        return $this;
    }

    /**
     * Remove TicketNotes
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\TicketNote $ticketNotes
     */
    public function removeTicketNote(\HelloDi\DiDistributorsBundle\Entity\TicketNote $ticketNotes)
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
     * Set Account
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Account $account
     * @return Ticket
     */
    public function setAccount(\HelloDi\DiDistributorsBundle\Entity\Account $account)
    {
        $this->Account = $account;
    
        return $this;
    }

    /**
     * Get Account
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\Account 
     */
    public function getAccount()
    {
        return $this->Account;
    }

    /**
     * Set User
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\User $user
     * @return Ticket
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
}