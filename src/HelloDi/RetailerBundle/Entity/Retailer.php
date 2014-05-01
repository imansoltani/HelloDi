<?php

namespace HelloDi\RetailerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\DistributorBundle\Entity\Distributor;

/**
 * Retailer
 *
 * @ORM\Table(name="retailer")
 * @ORM\Entity(repositoryClass="HelloDi\RetailerBundle\Entity\RetailerRepository")
 */
class Retailer
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
     * @ORM\ManyToOne(targetEntity="HelloDi\DistributorBundle\Entity\Distributor", inversedBy="retailers")
     * @ORM\JoinColumn(name="distributor_id", referencedColumnName="id", nullable=false)
     */
    private $distributor;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\AccountingBundle\Entity\Account")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     */
    private $account;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\CoreBundle\Entity\Ticket", mappedBy="retailer")
     */
    private $tickets;

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
     * Set distributor
     *
     * @param Distributor $distributor
     * @return Retailer
     */
    public function setDistributor(Distributor $distributor)
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
     * Set account
     *
     * @param Account $account
     * @return Retailer
     */
    public function setAccount(Account $account)
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
     * @return string
     */
    public function getNameWithCurrency()
    {
        return $this->getAccount()->getName() . ' | ' . $this->getAccount()->getBalance() . ' ( ' . $this->getDistributor()->getCurrency() . ' )';
    }
}