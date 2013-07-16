<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;
use FOS\UserBundle\Entity\User as BaseUser;

/** 
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 * @ORM\AttributeOverrides({
 * @ORM\AttributeOverride(name="email", column=@ORM\Column(type="string", name="email", length=255, unique=false, nullable=true)),
 * @ORM\AttributeOverride(name="emailCanonical", column=@ORM\Column(type="string", name="email_canonical", length=255, unique=false, nullable=true))
 * })
 */
class User extends BaseUser
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    protected $roles = array();
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
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Transaction", mappedBy="User")
     */
    private $Transactions;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Entiti", inversedBy="Users")
     * @ORM\JoinColumn(name="entiti_id", referencedColumnName="id", nullable=false)
     */
    private $Entiti;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Account", inversedBy="Users")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=true)
     */
    private $Account;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\OgonePayment", mappedBy="User")
     */
    private $OgonePayment;


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->Inputs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->removeElement = new \Doctrine\Common\Collections\ArrayCollection();
        $this->Tickets = new \Doctrine\Common\Collections\ArrayCollection();
        $this->TicketNotes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->Transactions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->OgonePayment = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param \HelloDi\DiDistributorsBundle\Entity\Input $inputs
     * @return User
     */
    public function addInput(\HelloDi\DiDistributorsBundle\Entity\Input $inputs)
    {
        $this->Inputs[] = $inputs;
    
        return $this;
    }

    /**
     * Remove Inputs
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Input $inputs
     */
    public function removeInput(\HelloDi\DiDistributorsBundle\Entity\Input $inputs)
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
     * @param \HelloDi\DiDistributorsBundle\Entity\Ticket $tickets
     * @return User
     */
    public function addTicket(\HelloDi\DiDistributorsBundle\Entity\Ticket $tickets)
    {
        $this->Tickets[] = $tickets;
    
        return $this;
    }

    /**
     * Remove Tickets
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Ticket $tickets
     */
    public function removeTicket(\HelloDi\DiDistributorsBundle\Entity\Ticket $tickets)
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
     * @param \HelloDi\DiDistributorsBundle\Entity\TicketNote $ticketNotes
     * @return User
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
     * Add Transactions
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Transaction $transactions
     * @return User
     */
    public function addTransaction(\HelloDi\DiDistributorsBundle\Entity\Transaction $transactions)
    {
        $this->Transactions[] = $transactions;
    
        return $this;
    }

    /**
     * Remove Transactions
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Transaction $transactions
     */
    public function removeTransaction(\HelloDi\DiDistributorsBundle\Entity\Transaction $transactions)
    {
        $this->Transactions->removeElement($transactions);
    }

    /**
     * Get Transactions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTransactions()
    {
        return $this->Transactions;
    }

    /**
     * Set Entiti
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Entiti $entiti
     * @return User
     */
    public function setEntiti(\HelloDi\DiDistributorsBundle\Entity\Entiti $entiti)
    {
        $this->Entiti = $entiti;
    
        return $this;
    }

    /**
     * Get Entiti
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\Entiti 
     */
    public function getEntiti()
    {
        return $this->Entiti;
    }

    /**
     * Set Account
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Account $account
     * @return User
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
     * Add Ogone
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\OgonePayment $ogone
     * @return User
     */
    public function addOgone(\HelloDi\DiDistributorsBundle\Entity\OgonePayment $ogone)
    {
        $this->OgonePayment[] = $ogone;
    
        return $this;
    }

    /**
     * Remove Ogone
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\OgonePayment $ogone
     */
    public function removeOgone(OgonePayment $ogone)
    {
        $this->OgonePayment->removeElement($ogone);
    }

    /**
     * Get Ogone
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOgone()
    {
        return $this->OgonePayment;
    }

    /**
     * Add OgonePayment
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\OgonePayment $ogonePayment
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
     * @param \HelloDi\DiDistributorsBundle\Entity\OgonePayment $ogonePayment
     */
    public function removeOgonePayment(\HelloDi\DiDistributorsBundle\Entity\OgonePayment $ogonePayment)
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
}