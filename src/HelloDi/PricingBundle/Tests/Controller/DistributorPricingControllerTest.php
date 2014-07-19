<?php

namespace HelloDi\PricingBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\PricingBundle\Entity\Price;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class DistributorPricingControllerTest extends WebTestCase
{
    /** @var Client $client */
    private $client = null;

    /** @var EntityManager */
    private $em;

    public function setUp()
    {
        $this->client = static::createClient(array(), array(
                'PHP_AUTH_USER' => 'master_admin',
                'PHP_AUTH_PW'   => '123456',
            ));

        $this->em = $this->client->getContainer()->get('doctrine.orm.default_entity_manager');
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->em->close();
    }

    protected function privateToPublic($class, $function, array $argumentForFunction = array())
    {
        $method = new \ReflectionMethod($class, $function);
        $method->setAccessible(TRUE);

        return $method->invokeArgs($class,$argumentForFunction);
    }

    public function testPricing()
    {
        $distributor = $this->em->getRepository('HelloDiDistributorBundle:Distributor')->findOneBy(array('currency'=>'usd'));
        $this->assertNotNull($distributor);

        $this->client->request('GET', '/app/m/distributor/'.$distributor->getAccount()->getId().'/items/');

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'Error in opening page.');
    }

    public function testUpdatePrice()
    {
        $distributor = $this->em->getRepository('HelloDiDistributorBundle:Distributor')->findOneBy(array('currency'=>'usd'));
        $this->assertNotNull($distributor);

        $item = $this->em->getRepository('HelloDiCoreBundle:Item')->findOneBy(array('currency'=>'usd'));
        $this->assertNotNull($item);

        $provider = $this->em->getRepository('HelloDiCoreBundle:Provider')->findOneBy(array('currency'=>'usd'));
        $this->assertNotNull($provider);

        //-------------
        $this->client->request(
            'POST',
            '/app/m/distributor/'.$distributor->getAccount()->getId().'/items/update',
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals('0-Item ID is incorrect.', $this->client->getResponse()->getContent());

        //------------
        $this->client->request(
            'POST',
            '/app/m/distributor/'.$distributor->getAccount()->getId().'/items/update',
            array('item_id'=>1, 'price'=>-1),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals('0-Price is incorrect.', $this->client->getResponse()->getContent());

        //------------
        $this->client->request(
            'POST',
            '/app/m/distributor/'.$distributor->getAccount()->getId().'/items/update',
            array('item_id'=>900000,'price'=>''),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals("0-Couldn't find the Item.", $this->client->getResponse()->getContent());

        //++++++++++++++++
        $qb = $this->em->createQueryBuilder()
            ->select('price')
            ->from('HelloDiPricingBundle:Price', 'price')
            ->innerJoin('price.account', 'account')
            ->where('account.type = :provider')->setParameter('provider', Account::PROVIDER)
            ->andWhere('price.item = :item')->setParameter('item', $item)
            ->getQuery();

        foreach($qb->getResult() as $price)
            $this->em->remove($price);
        $this->em->flush();

        $this->assertEquals(0, count($qb->getResult()));

        $priceDistributor = $this->em->getRepository('HelloDiPricingBundle:Price')->findOneBy(array('account'=>$distributor->getAccount(),'item'=>$item));
        if($priceDistributor) {
            $this->em->remove($priceDistributor);
            $this->em->flush();
        }

        $this->assertNull($this->em->getRepository('HelloDiPricingBundle:Price')->findOneBy(array('account'=>$distributor->getAccount(),'item'=>$item)));

        //-------------
        $this->client->request(
            'POST',
            '/app/m/distributor/'.$distributor->getAccount()->getId().'/items/update',
            array('item_id'=>$item->getId(),'price'=>200),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals("0-No provider has this item.", $this->client->getResponse()->getContent());

        //+++++++++++++++++
        $priceProvider = new Price();
        $priceProvider->setAccount($provider->getAccount());
        $provider->getAccount()->addPrice($priceProvider);
        $priceProvider->setItem($item);
        $priceProvider->setPrice(200);
        $this->em->persist($priceProvider);
        $this->em->flush();

        $this->assertNotNull($this->em->getRepository('HelloDiPricingBundle:Price')->findOneBy(array('account'=>$provider->getAccount(),'item'=>$item)));
        $this->assertNull($this->em->getRepository('HelloDiPricingBundle:Price')->findOneBy(array('account'=>$distributor->getAccount(),'item'=>$item)));

        //-----------------
        $this->client->request(
            'POST',
            '/app/m/distributor/'.$distributor->getAccount()->getId().'/items/update',
            array('item_id'=>$item->getId(),'price'=>200),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals('1-created', $this->client->getResponse()->getContent());

        $priceDistributor = $this->em->getRepository('HelloDiPricingBundle:Price')->findOneBy(array('account'=>$distributor->getAccount(),'item'=>$item));
        $this->assertNotNull($priceDistributor);
        $this->assertEquals($distributor->getAccount(), $priceDistributor->getAccount());
        $this->assertEquals($item, $priceDistributor->getItem());
        $this->assertEquals(200, $priceDistributor->getPrice());

        $this->em->clear('HelloDi\PricingBundle\Entity\Price');
        //-----------------
        $this->client->request(
            'POST',
            '/app/m/distributor/'.$distributor->getAccount()->getId().'/items/update',
            array('item_id'=>$item->getId(),'price'=>300),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals('1-updated', $this->client->getResponse()->getContent());

        $priceDistributor = $this->em->getRepository('HelloDiPricingBundle:Price')->findOneBy(array('account'=>$distributor->getAccount(),'item'=>$item));
        $this->assertNotNull($priceDistributor);
        $this->assertEquals($distributor->getAccount(), $priceDistributor->getAccount());
        $this->assertEquals($item, $priceDistributor->getItem());
        $this->assertEquals(300, $priceDistributor->getPrice());

        //-----------------
        $this->client->request(
            'POST',
            '/app/m/distributor/'.$distributor->getAccount()->getId().'/items/update',
            array('item_id'=>$item->getId(),'price'=>''),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals('1-removed', $this->client->getResponse()->getContent());

        $priceDistributor = $this->em->getRepository('HelloDiPricingBundle:Price')->findOneBy(array('account'=>$distributor->getAccount(),'item'=>$item));
        $this->assertNull($priceDistributor);
    }
}
