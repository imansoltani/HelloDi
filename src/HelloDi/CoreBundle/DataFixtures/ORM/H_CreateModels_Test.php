<?php
namespace HelloDi\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use HelloDi\CoreBundle\Entity\Item;
use HelloDi\CoreBundle\Entity\Operator;
use HelloDi\PricingBundle\Entity\Model;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class H_CreateModels_Test implements FixtureInterface, ContainerAwareInterface
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

        $item = $em->getRepository('HelloDiCoreBundle:Item')->findOneBy(array('currency'=>'usd'));

        $model = new Model();
        $model->setName('model_provider');
        $model->setCurrency('USD');
        $model->setAccount(null);
        $model->setJson(json_encode(array($item->getId()=>15)));
        $em->persist($model);

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