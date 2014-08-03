<?php
namespace HelloDi\CoreBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ticket_note")
 */
class TicketNote
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="text", nullable=true, name="description")
     */
    protected $description;

    /**
     * @ORM\Column(type="datetime", nullable=false, name="date")
     */
    protected $date;

    /**
     * @ORM\Column(type="smallint", nullable=false, name="view")
     */
    protected $view;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\CoreBundle\Entity\Ticket", inversedBy="ticketNotes")
     * @ORM\JoinColumn(name="ticket_id", referencedColumnName="id", nullable=false)
     */
    protected $ticket;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\CoreBundle\Entity\User", inversedBy="ticketNotes")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

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
     * Set description
     *
     * @param string $description
     * @return TicketNote
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return TicketNote
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
     * Set view
     *
     * @param integer $view
     * @return TicketNote
     */
    public function setView($view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Get view
     *
     * @return integer
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Set ticket
     *
     * @param Ticket $ticket
     * @return TicketNote
     */
    public function setTicket(Ticket $ticket)
    {
        $this->ticket = $ticket;

        return $this;
    }

    /**
     * Get ticket
     *
     * @return Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return TicketNote
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