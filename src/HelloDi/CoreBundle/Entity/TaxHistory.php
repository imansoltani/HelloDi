<?php
namespace HelloDi\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use HelloDi\DistributorBundle\Entity\Distributor;

/**
 * @ORM\Entity
 * @ORM\Table(name="tax_history")
 */
class TaxHistory
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime",nullable=true, name="Tax_end")
     */
    protected $taxEnd;

    /**
     * @ORM\Column(type="float", nullable=false, name="vax")
     */
    protected $vat;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\CoreBundle\Entity\Tax", inversedBy="taxHistories")
     * @ORM\JoinColumn(name="tax_id", referencedColumnName="id", nullable=true)
     */
    protected $tax;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\DistributorBundle\Entity\Distributor", mappedBy="taxHistory")
     */
    protected $distributors;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->distributors = new ArrayCollection();
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
     * Set taxEnd
     *
     * @param \DateTime $taxEnd
     * @return TaxHistory
     */
    public function setTaxEnd($taxEnd)
    {
        $this->taxEnd = $taxEnd;

        return $this;
    }

    /**
     * Get taxEnd
     *
     * @return \DateTime
     */
    public function getTaxEnd()
    {
        return $this->taxEnd;
    }

    /**
     * Set vat
     *
     * @param float $vat
     * @return TaxHistory
     */
    public function setVat($vat)
    {
        $this->vat = $vat;

        return $this;
    }

    /**
     * Get vat
     *
     * @return float
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * Set tax
     *
     * @param Tax $tax
     * @return TaxHistory
     */
    public function setTax(Tax $tax = null)
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * Get tax
     *
     * @return Tax
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * Add distributors
     *
     * @param Distributor $distributors
     * @return TaxHistory
     */
    public function addDistributor(Distributor $distributors)
    {
        $this->distributors[] = $distributors;

        return $this;
    }

    /**
     * Remove distributors
     *
     * @param Distributor $distributors
     */
    public function removeDistributor(Distributor $distributors)
    {
        $this->distributors->removeElement($distributors);
    }

    /**
     * Get distributors
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDistributors()
    {
        return $this->distributors;
    }
}