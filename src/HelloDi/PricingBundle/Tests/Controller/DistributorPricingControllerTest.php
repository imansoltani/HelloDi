<?php

namespace HelloDi\PricingBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\PricingBundle\Controller\DistributorPricingController;
use HelloDi\PricingBundle\Entity\Price;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class DistributorPricingControllerTest extends WebTestCase
{
    /** @var Client $client */
    private $client = null;

    /** @var EntityManager */
    private $em;

    protected function setUp()
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

    protected function countPricesOfAccount(Account $account)
    {
        return $this->em->createQueryBuilder()
            ->select('count(price)')
            ->from('HelloDiPricingBundle:Price', 'price')
            ->where('price.account = :account')->setParameter('account', $account)
            ->getQuery()->getSingleScalarResult();
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

        $provider = $this->em->getRepository('HelloDiAggregatorBundle:Provider')->findOneBy(array('currency'=>'usd'));
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

    public function testCopyPrices()
    {
        $distributor = $this->em->getRepository('HelloDiDistributorBundle:Distributor')->findOneBy(array('currency'=>'usd'));
        $this->assertNotNull($distributor);

        $crawler = $this->client->request('GET', '/app/m/distributor/'.$distributor->getAccount()->getId().'/items/copy_prices');

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'Error in opening page.');

        $countDistributors = $this->em->createQueryBuilder()
            ->select('count(distributor)')
            ->from('HelloDiDistributorBundle:Distributor', 'distributor')
            ->where('distributor != :this')->setParameter('this', $distributor)
            ->andWhere("distributor.currency = :currency")->setParameter('currency', $distributor->getCurrency())
            ->getQuery()->getSingleScalarResult();

        $input_tags = $crawler->filter('form span#form_distributor input[type="radio"]');

        $this->assertEquals(count($input_tags), $countDistributors);

        $this->assertGreaterThan(0, $countDistributors);

        //------------
        $form = $crawler->selectButton('Update')->form();

        $firstDistributorsID = $this->em->createQueryBuilder()
            ->select('distributor.id')
            ->from('HelloDiDistributorBundle:Distributor', 'distributor')
            ->where('distributor != :this')->setParameter('this', $distributor)
            ->andWhere("distributor.currency = :currency")->setParameter('currency', $distributor->getCurrency())
            ->getQuery()->getSingleScalarResult();

        $form['form[distributor]'] = $firstDistributorsID;

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect('/app/m/distributor/'.$distributor->getAccount()->getId().'/items/'));
    }

    public function testCopyPricesP()
    {
        $distributors = $this->em->getRepository('HelloDiDistributorBundle:Distributor')->findBy(array(),null,2);
        $this->assertEquals(2, count($distributors));

        $items = $this->em->getRepository('HelloDiCoreBundle:Item')->findBy(array(),null,3);
        $this->assertEquals(3, count($items));

        //clear price of first dist
        foreach($distributors[0]->getAccount()->getPrices() as $price) {
            $this->em->remove($price);
            $distributors[0]->getAccount()->removePrice($price);
        }

        //create two price for first dist
        for ($i=0; $i<2; $i++) {
            $price = new Price();
            $price->setPrice(100*($i+1));
            $price->setItem($items[$i]);
            $price->setAccount($distributors[0]->getAccount());
            $distributors[0]->getAccount()->addPrice($price);
            $this->em->persist($price);
        }

        //clear price of second dist
        foreach($distributors[1]->getAccount()->getPrices() as $price) {
            $this->em->remove($price);
            $distributors[1]->getAccount()->removePrice($price);
        }

        $this->em->flush();

        $this->assertEquals(2, $this->countPricesOfAccount($distributors[0]->getAccount()));
        $this->assertEquals(0, $this->countPricesOfAccount($distributors[1]->getAccount()));

        $DistributorPricingController = new DistributorPricingController();
        $DistributorPricingController->setContainer($this->client->getContainer());
        $this->assertTrue($this->privateToPublic($DistributorPricingController,'copyPrices',array($distributors[0], $distributors[1])));


        $this->assertEquals(2, $this->countPricesOfAccount($distributors[0]->getAccount()));
        $this->assertEquals(2, $this->countPricesOfAccount($distributors[1]->getAccount()));

        //--------------------
        //clear price of second dist
        foreach($distributors[1]->getAccount()->getPrices() as $price) {
            $this->em->remove($price);
            $distributors[1]->getAccount()->removePrice($price);
        }

        //add a extra price for second dist
        $price = new Price();
        $price->setPrice(300);
        $price->setItem($items[2]);
        $price->setAccount($distributors[1]->getAccount());
        $distributors[1]->getAccount()->addPrice($price);
        $this->em->persist($price);
        $this->em->flush();

        $this->assertEquals(2, $this->countPricesOfAccount($distributors[0]->getAccount()));
        $this->assertEquals(1, $this->countPricesOfAccount($distributors[1]->getAccount()));

        $DistributorPricingController = new DistributorPricingController();
        $DistributorPricingController->setContainer($this->client->getContainer());
        $this->assertTrue($this->privateToPublic($DistributorPricingController,'copyPrices',array($distributors[0], $distributors[1])));

        $this->assertEquals(2, $this->countPricesOfAccount($distributors[0]->getAccount()));
        $this->assertEquals(2, $this->countPricesOfAccount($distributors[1]->getAccount()));
    }
}
