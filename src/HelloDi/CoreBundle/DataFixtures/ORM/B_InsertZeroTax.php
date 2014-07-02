<?php
namespace HelloDi\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use HelloDi\CoreBundle\Entity\Tax;
use HelloDi\CoreBundle\Entity\TaxHistory;

class B_InsertZeroTax implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $em)
    {
        $tax = new Tax();
        $tax->setCountry(null);
        $tax->setTax(0);
        $em->persist($tax);

        $taxHistory = new TaxHistory();
        $taxHistory->setVat(0);
        $taxHistory->setTaxEnd(null);
        $taxHistory->setTax($tax);

        $em->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2;
    }
}