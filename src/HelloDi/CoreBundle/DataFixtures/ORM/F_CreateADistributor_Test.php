<?php
namespace HelloDi\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\CoreBundle\Entity\Entity;
use HelloDi\CoreBundle\Entity\User;
use HelloDi\DistributorBundle\Entity\Distributor;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class G_CreateADistributor_Test implements FixtureInterface, ContainerAwareInterface
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

        $country = $em->getRepository('HelloDiCoreBundle:Country')->findOneBy(array('iso'=>'US'));

        for($i=1; $i<=2; $i++) {
            $entity = new Entity();
            $entity->setName('dist'.$i);
            $entity->setVatNumber(1);
            $entity->setAddress1('');
            $entity->setNP(1);
            $entity->setCity('');
            $entity->setCountry($country);
            $em->persist($entity);

            $account = new Account();
            $account->setDefaultLanguage('en');
            $account->setName('dist'.$i);
            $account->setType(Account::DISTRIBUTOR);
            $account->setEntity($entity);
            $account->setCreationDate(new \DateTime());
            $em->persist($account);

            $distributor = new Distributor();
            $distributor->setCurrency('USD');
            $distributor->setTimeZone("Asia/Tehran");
            $distributor->setAccount($account);
            $em->persist($distributor);

            $dist_admin = new User();
            $dist_admin->setUsername('dist_admin'.$i);
            $dist_admin->setEmail('dist_admin'.$i.'@helloDi.com');
            $dist_admin->setPlainPassword('123456');
            $dist_admin->addRole('ROLE_DISTRIBUTOR_ADMIN');
            $dist_admin->setFirstName('dist_admin'.$i);
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
        return 6;
    }
}