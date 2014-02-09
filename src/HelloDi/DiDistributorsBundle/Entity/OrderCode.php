<?php

namespace HelloDi\DiDistributorsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderCode
 *
 * @ORM\Table(name="ordercode")
 * @ORM\Entity
 */
class OrderCode
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
     * @ORM\Column(type="string", length=2, nullable=false)
     */
    private $lang;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\AccountingBundle\Entity\Transaction", mappedBy="Order")
     */
    private $Transactions;

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
     * Constructor
     */
    public function __construct()
    {
        $this->Transactions = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add Transactions
     *
     * @param \HelloDi\AccountingBundle\Entity\Transaction $transactions
     * @return OrderCode
     */
    public function addTransaction(\HelloDi\AccountingBundle\Entity\Transaction $transactions)
    {
        $this->Transactions[] = $transactions;
    
        return $this;
    }

    /**
     * Remove Transactions
     *
     * @param \HelloDi\AccountingBundle\Entity\Transaction $transactions
     */
    public function removeTransaction(\HelloDi\AccountingBundle\Entity\Transaction $transactions)
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
     * Set lang
     *
     * @param string $lang
     * @return OrderCode
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    
        return $this;
    }

    /**
     * Get lang
     *
     * @return string 
     */
    public function getLang()
    {
        return $this->lang;
    }
}