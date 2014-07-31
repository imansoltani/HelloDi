<?php
namespace HelloDi\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use HelloDi\CoreBundle\Entity\Country;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class A_InsertCountries extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $em)
    {
        $countries = $this->container->getParameter('countries');

        foreach($countries as $country)
        {
            $countryEntity = new Country();
            $countryEntity->setId($country["id"]);
            $countryEntity->setName($country["Name"]);
            $countryEntity->setIso($country["Iso"]);
            $em->persist($countryEntity);
        }
        $em->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1;
    }
}