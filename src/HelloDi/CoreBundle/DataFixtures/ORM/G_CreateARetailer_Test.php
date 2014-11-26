<?php
namespace HelloDi\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\CoreBundle\Entity\Entity;
use HelloDi\CoreBundle\Entity\User;
use HelloDi\DistributorBundle\Entity\Distributor;
use HelloDi\RetailerBundle\Entity\Retailer;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class G_CreateARetailer_Test  extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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

        $distributor = $em->getRepository('HelloDiDistributorBundle:Distributor')->findOneBy(array());

        for($i=1; $i<=2; $i++) {
            $entity = new Entity();
            $entity->setName('ret'.$i);
            $entity->setVatNumber(1);
            $entity->setAddress1('');
            $entity->setNP(1);
            $entity->setCity('');
            $entity->setCountry('US');
            $em->persist($entity);

            $account = new Account();
            $account->setDefaultLanguage('en');
            $account->setName('ret'.$i);
            $account->setType(Account::RETAILER);
            $account->setEntity($entity);
            $account->setCreationDate(new \DateTime());
            $em->persist($account);

            $retailer = new Retailer();
            $retailer->setDistributor($distributor);
            $retailer->setAccount($account);
            $retailer->setVat(true);
            $em->persist($retailer);

            $dist_admin = new User();
            $dist_admin->setUsername('ret_admin'.$i);
            $dist_admin->setEmail('ret_admin'.$i.'@helloDi.com');
            $dist_admin->setPlainPassword('123456');
            $dist_admin->addRole('ROLE_RETAILER_ADMIN');
            $dist_admin->setFirstName('ret_admin'.$i);
            $dist_admin->setLanguage('en');
            $dist_admin->setEntity($entity);
            $dist_admin->setAccount($account);
            $dist_admin->setEnabled(true);
            $em->persist($dist_admin);
        }

        $em->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 7;
    }
}