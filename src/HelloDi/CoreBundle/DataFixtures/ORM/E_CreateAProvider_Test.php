<?php
namespace HelloDi\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\CoreBundle\Entity\Entity;
use HelloDi\AggregatorBundle\Entity\Provider;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class E_CreateAProvider_Test  extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        if("test" != $this->container->get('kernel')->getEnvironment()) return;

        $entity = new Entity();
        $entity->setName('prov1');
        $entity->setVatNumber(1);
        $entity->setAddress1('');
        $entity->setNP(1);
        $entity->setCity('');
        $entity->setCountry('US');
        $em->persist($entity);

        $account = new Account();
        $account->setDefaultLanguage('en');
        $account->setName('prov1');
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
        return 5;
    }
}