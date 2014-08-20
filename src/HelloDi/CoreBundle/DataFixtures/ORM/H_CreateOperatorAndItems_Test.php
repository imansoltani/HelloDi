<?php
namespace HelloDi\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use HelloDi\CoreBundle\Entity\Item;
use HelloDi\CoreBundle\Entity\Operator;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class H_CreateOperatorAndItems_Test  extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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

        $currencies = array('USD', 'CHF');

        $operator = new Operator();
        $operator->setName('op1');
        $em->persist($operator);

        for ($i=1; $i<=4; $i++)
        {
            $item = new Item();
            $item->setName('item'.$i);
            $item->setAlertMinStock(0);
            $item->setCountry('US');
            $item->setCurrency($currencies[$i%2]);
            $item->setType(Item::DMTU);
            $item->setFaceValue(10+$i);
            $item->setDateInsert(new \DateTime());
            $item->setCode("code_item".$i);
            $item->setOperator($operator);
            $em->persist($item);
        }

        $em->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 8;
    }
}