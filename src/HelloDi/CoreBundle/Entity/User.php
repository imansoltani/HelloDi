<?php
namespace HelloDi\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\AccountingBundle\Entity\CreditLimit;
use HelloDi\AccountingBundle\Entity\OgonePayment;
use HelloDi\AccountingBundle\Entity\Transfer;
use HelloDi\AggregatorBundle\Entity\Input;
use HelloDi\AggregatorBundle\Entity\Pin;
use HelloDi\UserBundle\Model\User as BaseUser;

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
     * @ORM\Column(type="string", length=45, nullable=false, name="first_name")
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string", length=45, nullable=true, name="last_name")
     */
    protected $lastName;

    /**
     * @ORM\Column(type="string", length=45, nullable=true, name="mobile")
     */
    protected $mobile;

    /**
     * @ORM\Column(type="string", length=2, nullable=false, name="language")
     */
    protected $language;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\AggregatorBundle\Entity\Input", mappedBy="user")
     */
    protected $inputs;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\CoreBundle\Entity\Ticket", mappedBy="user")
     */
    protected $tickets;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\CoreBundle\Entity\TicketNote", mappedBy="user")
     */
    protected $ticketNotes;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\AccountingBundle\Entity\Transfer", mappedBy="user")
     */
    protected $transfers;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\CoreBundle\Entity\Entity", inversedBy="users")
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id", nullable=false)
     */
    protected $entity;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\AccountingBundle\Entity\Account", inversedBy="users")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=true)
     */
    protected $account;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\AccountingBundle\Entity\OgonePayment", mappedBy="user")
     */
    protected $ogonePayment;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\AccountingBundle\Entity\CreditLimit", mappedBy="user")
     */
    protected $creditLimits;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\AggregatorBundle\Entity\Pin", mappedBy="user")
     */
    protected $pins;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->inputs = new ArrayCollection();
        $this->removeElement = new ArrayCollection();
        $this->tickets = new ArrayCollection();
        $this->ticketNotes = new ArrayCollection();
        $this->transfers = new ArrayCollection();
        $this->ogonePayment = new ArrayCollection();
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
     * Add inputs
     *
     * @param Input $inputs
     * @return User
     */
    public function addInput(Input $inputs)
    {
        $this->inputs[] = $inputs;

        return $this;
    }

    /**
     * Remove inputs
     *
     * @param Input $inputs
     */
    public function removeInput(Input $inputs)
    {
        $this->inputs->removeElement($inputs);
    }

    /**
     * Get inputs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInputs()
    {
        return $this->inputs;
    }

    /**
     * Add tickets
     *
     * @param Ticket $tickets
     * @return User
     */
    public function addTicket(Ticket $tickets)
    {
        $this->tickets[] = $tickets;

        return $this;
    }

    /**
     * Remove tickets
     *
     * @param Ticket $tickets
     */
    public function removeTicket(Ticket $tickets)
    {
        $this->tickets->removeElement($tickets);
    }

    /**
     * Get tickets
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * Add ticketNotes
     *
     * @param TicketNote $ticketNotes
     * @return User
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
     * Set entity
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
     * Get entity
     *
     * @return Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set account
     *
     * @param Account $account
     * @return User
     */
    public function setAccount(Account $account = null)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Add ogonePayment
     *
     * @param OgonePayment $ogonePayment
     * @return User
     */
    public function addOgonePayment(OgonePayment $ogonePayment)
    {
        $this->ogonePayment[] = $ogonePayment;

        return $this;
    }

    /**
     * Remove ogonePayment
     *
     * @param OgonePayment $ogonePayment
     */
    public function removeOgonePayment(OgonePayment $ogonePayment)
    {
        $this->ogonePayment->removeElement($ogonePayment);
    }

    /**
     * Get ogonePayment
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOgonePayment()
    {
        return $this->ogonePayment;
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
     * Add pins
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
     * Remove pins
     *
     * @param Pin $pins
     */
    public function removePin(Pin $pins)
    {
        $this->pins->removeElement($pins);
    }

    /**
     * Get pins
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPins()
    {
        return $this->pins;
    }

    /**
     * Set role
     *
     * @param string $role
     * @return $this
     */
    public function setRole($role)
    {
        $this->setRoles(array($role));

        return $this;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->getRoles()[0];
    }
}