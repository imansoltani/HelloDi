<?php
namespace HelloDi\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use HelloDi\CoreBundle\Entity\Item;
use HelloDi\PricingBundle\Entity\Model;
use HelloDi\PricingBundle\Entity\Price;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class I_CreateModels_Test  extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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

        $modelProvider = new Model();
        $modelProvider->setName('model_provider');
        $modelProvider->setCurrency('USD');
        $modelProvider->setAccount(null);
        $modelProvider->setJson(json_encode(array($item->getId()=>15)));
        $em->persist($modelProvider);

        //-------------

        $provider = $em->getRepository('HelloDiAggregatorBundle:Provider')->findOneBy(array('currency'=>'USD'));

        $user = $em->getRepository('HelloDiCoreBundle:User')->findOneBy(array('username' => 'dist_admin1'));
        $distributor = $em->getRepository('HelloDiDistributorBundle:Distributor')->findOneBy(array('account'=>$user->getAccount()));

        $priceProv = new Price();
        $priceProv->setPrice(15);
        $priceProv->setItem($item);
        $priceProv->setAccount($provider->getAccount());
        $provider->getAccount()->addPrice($priceProv);
        $em->persist($priceProv);

        $priceDist = new Price();
        $priceDist->setPrice(20);
        $priceDist->setItem($item);
        $priceDist->setAccount($distributor->getAccount());
        $provider->getAccount()->addPrice($priceDist);
        $em->persist($priceDist);

        $em->flush();

        $modelRetailer = new Model();
        $modelRetailer->setName('model_retailer');
        $modelRetailer->setCurrency(null);
        $modelRetailer->setAccount($distributor->getAccount());
        $distributor->getAccount()->addModel($modelRetailer);
        $modelRetailer->setJson(json_encode(array($priceDist->getId()=>25)));
        $em->persist($modelRetailer);

        $em->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 9;
    }
}