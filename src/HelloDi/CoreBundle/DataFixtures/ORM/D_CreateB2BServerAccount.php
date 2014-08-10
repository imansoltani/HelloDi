<?php
namespace HelloDi\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\CoreBundle\Entity\Entity;
use HelloDi\AggregatorBundle\Entity\Provider;

class D_CreateB2BServerAccount extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $em)
    {
        $country = $em->getRepository('HelloDiCoreBundle:Country')->findOneBy(array('iso'=>'US'));

        $entity = new Entity();
        $entity->setName('B2B Server');
        $entity->setVatNumber(1);
        $entity->setAddress1('');
        $entity->setNP(1);
        $entity->setCity('');
        $entity->setCountry($country);
        $em->persist($entity);

        $account = new Account();
        $account->setDefaultLanguage('en');
        $account->setName('B2B Server');
        $account->setType(Account::PROVIDER);
        $account->setEntity($entity);
        $account->setCreationDate(new \DateTime());
        $em->persist($account);

        $provider = new Provider();
        $provider->setCurrency('USD');
        $provider->setAccount($account);
        $em->persist($provider);

        $em->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 4;
    }
}