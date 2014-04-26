<?php
namespace HelloDi\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use HelloDi\CoreBundle\Entity\Entity;
use HelloDi\CoreBundle\Entity\User;

class CreateFirstMasterAdmin implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $em)
    {
        $country = $em->getRepository('HelloDiCoreBundle:Country')->findOneBy(array('iso'=>'SZ'));

        $entity = new Entity();
        $entity->setName('MasterAdmin');
        $entity->setVatNumber(1);
        $entity->setAddress1('');
        $entity->setNP(1);
        $entity->setCity('');
        $entity->setCountry($country);
        $em->persist($entity);

        $user_admin = new User();
        $user_admin->setUsername('master_admin');
        $user_admin->setEmail('master_admin@helloDi.com');
        $user_admin->setPlainPassword('123');
        $user_admin->addRole('ROLE_MASTER_ADMIN');
        $user_admin->setFirstName('master_admin');
        $user_admin->setLanguage('en');
        $user_admin->setEntity($entity);
        $user_admin->setEnabled(true);
        $em->persist($user_admin);

        $em->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 3;
    }
}