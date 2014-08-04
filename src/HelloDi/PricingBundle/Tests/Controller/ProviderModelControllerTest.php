<?php

namespace HelloDi\PricingBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\CoreBundle\Entity\Provider;
use HelloDi\PricingBundle\Entity\Price;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use HelloDi\PricingBundle\Controller\ProviderModelController;

class ProviderModelControllerTest extends WebTestCase
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

    public function testIndex()
    {
        $crawler = $this->client->request('GET', '/app/m/provider/model/');

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'Error in opening page.');

        $models_count = count($this->em->getRepository("HelloDiPricingBundle:Model")->findBy(array("account" => null)));

        $tr_tags = $crawler->filter('table#example tbody tr');

        $this->assertEquals($models_count, $tr_tags->count());
    }

    public function testAdd()
    {
        $crawler = $this->client->request('GET', '/app/m/provider/model/add');

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'Error in opening page.');

        $items_count = count($this->em->getRepository('HelloDiCoreBundle:Item')->findAll());

        $tr_tags = $crawler->filter('table#example tbody tr');

        $this->assertEquals($items_count, $tr_tags->count());

        //-------------

        $form = $crawler->selectButton('add')->form();

        $form['name'] = 'Model1';
        $form['currency'] = 'USD';
        $form['json'] = '{}';

        $this->client->submit($form);

        $this->assertFalse($this->client->getResponse()->isRedirect('/app/m/provider/model/'), 'json is empty array and do not valid');

        //-------------

        $form = $crawler->selectButton('add')->form();

        $form['name'] = 'Model1';
        $form['currency'] = 'USD';
        $form['json'] = '{"2000":"15"}';

        $this->client->submit($form);

        $this->assertFalse($this->client->getResponse()->isRedirect('/app/m/provider/model/'), 'an item_id in json is invalid');

        //-------------

        $form = $crawler->selectButton('add')->form();

        $form['name'] = 'Model1';
        $form['currency'] = 'CHF';
        $form['json'] = '{"2000":"15"}';

        $this->client->submit($form);

        $this->assertFalse($this->client->getResponse()->isRedirect('/app/m/provider/model/'), 'currency of item is different.');

        //-------------
        $models_count_before = count($this->em->getRepository("HelloDiPricingBundle:Model")->findBy(array("account" => null)));

        $item = $this->em->getRepository('HelloDiCoreBundle:Item')->findOneBy(array('currency'=>'usd'));
        $this->assertNotNull($item);

        $form = $crawler->selectButton('add')->form();

        $form['name'] = 'Model1';
        $form['currency'] = 'USD';
        $form['json'] = '{"'.$item->getId().'":"15"}';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect('/app/m/provider/model/'));

        $model = $this->em->getRepository('HelloDiPricingBundle:Model')->findOneBy(array(),array('id'=>'desc'));

        $this->assertNotNull($model);
        $this->assertEquals('Model1', $model->getName());
        $this->assertEquals('USD', $model->getCurrency());
        $this->assertNull($model->getAccount());
        $this->assertEquals('{"'.$item->getId().'":"15"}', $model->getJson());

        $models_dir = __DIR__."/../../../../../web/uploads/models/";

        $this->assertTrue(file_exists($models_dir.$model->getId().'.json'));
        $this->assertEquals('{"'.$item->getId().'":"15"}',file_get_contents($models_dir.$model->getId().'.json'));

        //---------
        $crawler = $this->client->followRedirect();

        $tr_tags = $crawler->filter('table#example tbody tr');

        $models_count_after = count($this->em->getRepository("HelloDiPricingBundle:Model")->findBy(array("account" => null)));

        $this->assertEquals($models_count_after, $tr_tags->count());

        $this->assertEquals($models_count_after, $models_count_before + 1);
    }

    public function testEdit()
    {
        $model = $this->em->getRepository('HelloDiPricingBundle:Model')->findOneBy(array('currency'=>'usd'));
        $this->assertNotNull($model);

        $crawler = $this->client->request('GET', '/app/m/provider/model/'.$model->getId().'/edit');

        $item = $this->em->getRepository('HelloDiCoreBundle:Item')->findOneBy(array('currency'=>'usd'));
        $this->assertNotNull($item);

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'Error in opening page. error:'.$this->client->getResponse()->getStatusCode());

        $items_count = count($this->em->getRepository('HelloDiCoreBundle:Item')->findAll());

        $tr_tags = $crawler->filter('table#example tbody tr');

        $this->assertEquals($items_count, $tr_tags->count());

        //-------------

        $form = $crawler->selectButton('Update')->form();

        $form['name'] = 'Model1';
        $form['currency'] = 'USD';
        $form['json'] = '{}';

        $this->client->submit($form);

        $this->assertFalse($this->client->getResponse()->isRedirect('/app/m/provider/model/'), 'json is empty array and do not valid');

        //-------------

        $form = $crawler->selectButton('Update')->form();

        $form['name'] = 'Model1';
        $form['currency'] = 'USD';
        $form['json'] = '{"2000":"15"}';

        $this->client->submit($form);

        $this->assertFalse($this->client->getResponse()->isRedirect('/app/m/provider/model/'), 'an item_id in json is invalid');

        //-------------

        $form = $crawler->selectButton('Update')->form();

        $form['name'] = 'Model1';
        $form['currency'] = 'CHF';
        $form['json'] = '{"2000":"15"}';

        $this->client->submit($form);

        $this->assertFalse($this->client->getResponse()->isRedirect('/app/m/provider/model/'), 'currency of item is different.');

        //-------------

        $form = $crawler->selectButton('Update')->form();

        $form['name'] = 'Model1';
        $form['currency'] = 'USD';
        $form['json'] = '{"'.$item->getId().'":"15"}';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect('/app/m/provider/model/'));

        $model = $this->em->getRepository('HelloDiPricingBundle:Model')->findOneBy(array(),array('id'=>'desc'));

        $this->assertNotNull($model);
        $this->assertEquals('Model1', $model->getName());
        $this->assertEquals('USD', $model->getCurrency());
        $this->assertNull($model->getAccount());
        $this->assertEquals('{"'.$item->getId().'":"15"}', $model->getJson());

        $models_dir = __DIR__."/../../../../../web/uploads/models/";

        $this->assertTrue(file_exists($models_dir.$model->getId().'.json'));
        $this->assertEquals('{"'.$item->getId().'":"15"}',file_get_contents($models_dir.$model->getId().'.json'));
    }

    public function testDelete() {
        $model = $this->em->getRepository('HelloDiPricingBundle:Model')->findOneBy(array('currency'=>'usd'));
        $this->assertNotNull($model);

        $models_count_before = count($this->em->getRepository("HelloDiPricingBundle:Model")->findBy(array("account" => null)));

        $this->client->request('GET', '/app/m/provider/model/'.$model->getId().'/delete');

        $this->assertTrue($this->client->getResponse()->isRedirect('/app/m/provider/model/'));

        $crawler = $this->client->followRedirect();

        $tr_tags = $crawler->filter('table#example tbody tr');

        $models_count_after = count($this->em->getRepository("HelloDiPricingBundle:Model")->findBy(array("account" => null)));

        $this->assertEquals($models_count_after, $tr_tags->count());

        $this->assertEquals($models_count_after, $models_count_before - 1);
    }

    public function testSetModel()
    {
        $provider = $this->em->getRepository('HelloDiCoreBundle:Provider')->findOneBy(array('currency'=>'usd'));
        $this->assertNotNull($provider);

        $crawler =  $this->client->request('GET', '/app/m/provider/model/set_model/'.$provider->getAccount()->getId());

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'Error in opening page.');

        $models_count = count($this->em->getRepository("HelloDiPricingBundle:Model")->findBy(array("account" => null)));

        $radio_tags = $crawler->filter('form #form_model input[type="radio"]');

        $this->assertEquals($models_count, $radio_tags->count());

        //-------------
        $form = $crawler->selectButton('form[update]')->form();

        $model = $this->em->getRepository('HelloDiPricingBundle:Model')->findOneBy(array('currency'=>'usd'));
        $this->assertNotNull($model);

        $form['form[model]'] = $model->getId();

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect('/app/m/provider/'.$provider->getAccount()->getId().'/items'));

        //-------------
        $this->assertEquals($provider->getCurrency(), $model->getCurrency());

        $model_prices = json_decode($model->getJson(),true);

        $provider_prices = $provider->getAccount()->getPrices();

        $this->assertEquals(count($model_prices), count($provider_prices));
    }

    public function testUpdatePricesFromModel()
    {
        $provider = $this->em->getRepository('HelloDiCoreBundle:Provider')->findOneBy(array('currency'=>'usd'));
        $this->assertNotNull($provider);

        $model = $this->em->getRepository('HelloDiPricingBundle:Model')->findOneBy(array('currency'=>'usd'));
        $this->assertNotNull($model);

        $provider->getAccount()->setModel($model);
        $model->addAccount($provider->getAccount());
        $this->em->flush();

        $ProviderModelController = new ProviderModelController();
        $ProviderModelController->setContainer($this->client->getContainer());

        $this->assertTrue($this->privateToPublic($ProviderModelController,'updatePricesFromModel',array($model,$provider->getAccount())));

        $this->assertEquals($provider->getCurrency(), $model->getCurrency());

        $model_prices = json_decode($model->getJson(),true);

        /** @var Price[] $provider_prices */
        $provider_prices = $provider->getAccount()->getPrices();

        $this->assertEquals(count($model_prices), count($provider_prices), "count(model_prices)=".count($model_prices)." count(provider_prices)=".count($provider_prices));

        $is_equal = true;
        foreach($provider_prices as $price)
            if ($price->getPrice() != $model_prices[$price->getItem()->getId()]){
                $is_equal = false;
                break;
            }

        $this->assertTrue($is_equal, 'An Item in Provider exist that his price is not equal');

        //--------------------
        /** @var Provider[] $providers */
        $providers = $this->em->getRepository('HelloDiCoreBundle:Provider')->findBy(array('currency'=>'usd'),null, 2);

        foreach($providers as $provider) {
            $provider->getAccount()->setModel($model);
            $model->addAccount($provider->getAccount());
        }

        $this->em->flush();

        $this->assertTrue($this->privateToPublic($ProviderModelController,'updatePricesFromModel',array($model)));

        foreach($providers as $provider) {
            $this->assertEquals($provider->getCurrency(), $model->getCurrency());

            /** @var Price[] $provider_prices */
            $provider_prices = $provider->getAccount()->getPrices();

            $this->assertEquals(count($model_prices), count($provider_prices), "Account[".$provider->getAccount()->getId()."]: count(model_prices)=".count($model_prices)." count(provider_prices)=".count($provider_prices));

            $is_equal = true;
            foreach($provider_prices as $price)
                if ($price->getPrice() != $model_prices[$price->getItem()->getId()]){
                    $is_equal = false;
                    break;
                }
            $this->assertTrue($is_equal, 'An Item in Provider exist that his price is not equal');
        }
    }
}
