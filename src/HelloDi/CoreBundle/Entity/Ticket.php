<?php
namespace HelloDi\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use HelloDi\DistributorBundle\Entity\Distributor;
use HelloDi\RetailerBundle\Entity\Retailer;

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
     * @ORM\Column(type="datetime", nullable=false, name="start")
     */
    private $start;

    /**
     * @ORM\Column(type="datetime", nullable=true, name="end")
     */
    private $end;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $type;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $status;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $inChange;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\CoreBundle\Entity\TicketNote", mappedBy="ticket")
     */
    private $ticketNotes;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\DistributorBundle\Entity\Distributor", inversedBy="tickets")
     * @ORM\JoinColumn(name="distributor_id", referencedColumnName="id", nullable=true)
     */
    private $distributor;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\RetailerBundle\Entity\Retailer", inversedBy="tickets")
     * @ORM\JoinColumn(name="retailer_id", referencedColumnName="id", nullable=true)
     */
    private $retailer;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\CoreBundle\Entity\User", inversedBy="tickets")
     * @ORM\JoinColumn(name="last_user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ticketNotes = new ArrayCollection();
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
     * Set start
     *
     * @param \DateTime $start
     * @return Ticket
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start
     *
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set end
     *
     * @param \DateTime $end
     * @return Ticket
     */
    public function setEnd($end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Get end
     *
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
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
     * Set status
     *
     * @param integer $status
     * @return Ticket
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set inChange
     *
     * @param integer $inChange
     * @return Ticket
     */
    public function setInChange($inChange)
    {
        $this->inChange = $inChange;

        return $this;
    }

    /**
     * Get inChange
     *
     * @return integer
     */
    public function getInChange()
    {
        return $this->inChange;
    }

    /**
     * Add ticketNotes
     *
     * @param TicketNote $ticketNotes
     * @return Ticket
     */
    public function addTicketNote(TicketNote $ticketNotes)
    {
        $this->ticketNotes[] = $ticketNotes;

        return $this;
    }

    /**
     * Remove ticketNotes
     *
     * @param TicketNote $ticketNotes
     */
    public function removeTicketNote(TicketNote $ticketNotes)
    {
        $this->ticketNotes->removeElement($ticketNotes);
    }

    /**
     * Get ticketNotes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTicketNotes()
    {
        return $this->ticketNotes;
    }

    /**
     * Set distributor
     *
     * @param Distributor $distributor
     * @return Ticket
     */
    public function setDistributor(Distributor $distributor = null)
    {
        $this->distributor = $distributor;

        return $this;
    }

    /**
     * Get distributor
     *
     * @return Distributor
     */
    public function getDistributor()
    {
        return $this->distributor;
    }

    /**
     * Set retailer
     *
     * @param Retailer $retailer
     * @return Ticket
     */
    public function setRetailer(Retailer $retailer = null)
    {
        $this->retailer = $retailer;

        return $this;
    }

    /**
     * Get retailer
     *
     * @return Retailer
     */
    public function getRetailer()
    {
        return $this->retailer;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return Ticket
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
}