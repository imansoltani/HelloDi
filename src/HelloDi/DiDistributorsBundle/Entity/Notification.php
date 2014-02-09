<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/** 
 * @ORM\Entity
 * @ORM\Table(name="notification")
 */
class Notification
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /** 
     * @ORM\Column(type="datetime", nullable=false, name="date")
     */
    private $Date;

    /** 
     * @ORM\Column(type="integer", nullable=false)
     */
    private $Type;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $Value;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\AccountingBundle\Entity\Account", inversedBy="Notification")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id",nullable=true)
     */
    private $Account;


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
     * Set Date
     *
     * @param \DateTime $date
     * @return Notification
     */
    public function setDate($date)
    {
        $this->Date = $date;
    
        return $this;
    }

    /**
     * Get Date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->Date;
    }

    /**
     * Set Type
     *
     * @param integer $type
     * @return Notification
     */
    public function setType($type)
    {
        $this->Type = $type;
    
        return $this;
    }

    /**
     * Get Type
     *
     * @return integer 
     */
    public function getType()
    {
        return $this->Type;
    }

    /**
     * Set Account
     *
     * @param \HelloDi\AccountingBundle\Entity\Account $account
     * @return Notification
     */
    public function setAccount(\HelloDi\AccountingBundle\Entity\Account $account = null)
    {
        $this->Account = $account;
    
        return $this;
    }

    /**
     * Get Account
     *
     * @return \HelloDi\AccountingBundle\Entity\Account
     */
    public function getAccount()
    {
        return $this->Account;
    }

    /**
     * Set Value
     *
     * @param string $value
     * @return Notification
     */
    public function setValue($value)
    {
        $this->Value = $value;
    
        return $this;
    }

    /**
     * Get Value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->Value;
    }
}