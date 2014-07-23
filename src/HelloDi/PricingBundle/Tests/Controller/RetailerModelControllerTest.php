<?php

namespace HelloDi\PricingBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\DistributorBundle\Entity\Distributor;
use HelloDi\PricingBundle\Controller\RetailerModelController;
use HelloDi\PricingBundle\Entity\Price;
use HelloDi\RetailerBundle\Entity\Retailer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class RetailerModelControllerTest extends WebTestCase
{
    /** @var Client $client */
    private $client = null;

    /** @var EntityManager $em */
    private $em;

    /** @var Distributor $distributor */
    private $distributor;

    public function setUp()
    {
        $this->client = static::createClient(array(), array(
                'PHP_AUTH_USER' => 'dist_admin1',
                'PHP_AUTH_PW'   => '123456',
            ));

        $this->em = $this->client->getContainer()->get('doctrine.orm.default_entity_manager');

        $user = $this->em->getRepository('HelloDiCoreBundle:User')->findOneBy(array('username' => 'dist_admin1'));

        $this->distributor = $this->em->getRepository('HelloDiDistributorBundle:Distributor')->findOneBy(array('account'=>$user->getAccount()));
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

    public function testIndex()
    {
        $crawler = $this->client->request('GET', '/app/d/retailer/model/');

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'Error in opening page.');

        $models_count = count($this->em->getRepository("HelloDiPricingBundle:Model")->findBy(array("account" => $this->distributor->getAccount())));

        $tr_tags = $crawler->filter('table#example tbody tr');

        $this->assertEquals($models_count, $tr_tags->count());
    }

    public function testAdd()
    {
        $crawler = $this->client->request('GET', '/app/d/retailer/model/add');

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'Error in opening page.');

        $prices_count = count($this->em->createQueryBuilder()
            ->select('price')
            ->from('HelloDiPricingBundle:Price','price')
            ->where('price.account = :account')->setParameter('account', $this->distributor->getAccount())
            ->getQuery()->getResult());

        $tr_tags = $crawler->filter('table#example tbody tr');

        $this->assertEquals($prices_count, $tr_tags->count());

        //-------------

        $form = $crawler->selectButton('add')->form();

        $form['name'] = 'Model1';
        $form['json'] = '{}';

        $this->client->submit($form);

        $this->assertFalse($this->client->getResponse()->isRedirect('/app/d/retailer/model/'), 'json is empty array and do not valid');

        //-------------

        $form = $crawler->selectButton('add')->form();

        $form['name'] = 'Model1';
        $form['json'] = '{"2000":"15"}';

        $this->client->submit($form);

        $this->assertFalse($this->client->getResponse()->isRedirect('/app/d/retailer/model/'), 'an price_id in json is invalid');

        //-------------
        $price = $this->em->getRepository('HelloDiPricingBundle:Price')->findOneBy(array('account'=>$this->distributor->getAccount()));
        $this->assertNotNull($price);

        $form = $crawler->selectButton('add')->form();

        $form['name'] = 'Model1';
        $form['json'] = '{"'.$price->getId().'":"'.($price->getPrice()-10).'"}';

        $this->client->submit($form);

        $this->assertFalse($this->client->getResponse()->isRedirect('/app/d/retailer/model/'), 'an price_id is lower than.');

        //-------------
        $models_count_before = count($this->em->getRepository("HelloDiPricingBundle:Model")->findBy(array("account" => $this->distributor->getAccount())));

        $form = $crawler->selectButton('add')->form();

        $form['name'] = 'Model1';
        $form['json'] = '{"'.$price->getId().'":"'.($price->getPrice()+10).'"}';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect('/app/d/retailer/model/'));

        $model = $this->em->getRepository('HelloDiPricingBundle:Model')->findOneBy(array("account" => $this->distributor->getAccount()),array('id'=>'desc'));

        $this->assertNotNull($model);
        $this->assertEquals('Model1', $model->getName());
        $this->assertNull($model->getCurrency());
        $this->assertEquals($this->distributor->getAccount(), $model->getAccount());
        $this->assertEquals('{"'.$price->getId().'":"'.($price->getPrice()+10).'"}', $model->getJson());

        $models_dir = __DIR__."/../../../../../web/uploads/models/";

        $this->assertTrue(file_exists($models_dir.$model->getId().'.json'));
        $this->assertEquals('{"'.$price->getId().'":"'.($price->getPrice()+10).'"}',file_get_contents($models_dir.$model->getId().'.json'));

        //---------
        $crawler = $this->client->followRedirect();

        $tr_tags = $crawler->filter('table#example tbody tr');

        $models_count_after = count($this->em->getRepository("HelloDiPricingBundle:Model")->findBy(array("account" => $this->distributor->getAccount())));

        $this->assertEquals($models_count_after, $tr_tags->count());

        $this->assertEquals($models_count_after, $models_count_before + 1);
    }

    public function testEdit()
    {
        $model = $this->em->getRepository('HelloDiPricingBundle:Model')->findOneBy(array("account" => $this->distributor->getAccount()));
        $this->assertNotNull($model);

        $crawler = $this->client->request('GET', '/app/d/retailer/model/'.$model->getId().'/edit');

        $price = $this->em->getRepository('HelloDiPricingBundle:Price')->findOneBy(array('account'=>$this->distributor->getAccount()));
        $this->assertNotNull($price);

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'Error in opening page. error:'.$this->client->getResponse()->getStatusCode());

        $prices_count = count($this->em->createQueryBuilder()
            ->select('price')
            ->from('HelloDiPricingBundle:Price','price')
            ->where('price.account = :account')->setParameter('account', $this->distributor->getAccount())
            ->getQuery()->getResult());

        $tr_tags = $crawler->filter('table#example tbody tr');

        $this->assertEquals($prices_count, $tr_tags->count());

        //-------------

        $form = $crawler->selectButton('Update')->form();

        $form['name'] = 'Model1';
        $form['json'] = '{}';

        $this->client->submit($form);

        $this->assertFalse($this->client->getResponse()->isRedirect('/app/d/retailer/model/'), 'json is empty array and do not valid');

        //-------------

        $form = $crawler->selectButton('Update')->form();

        $form['name'] = 'Model1';
        $form['json'] = '{"2000":"15"}';

        $this->client->submit($form);

        $this->assertFalse($this->client->getResponse()->isRedirect('/app/d/retailer/model/'), 'an price_id in json is invalid');

        //-------------

        $form = $crawler->selectButton('Update')->form();

        $form['name'] = 'Model1';
        $form['json'] = '{"'.$price->getId().'":"'.($price->getPrice()-10).'"}';

        $this->client->submit($form);

        $this->assertFalse($this->client->getResponse()->isRedirect('/app/d/retailer/model/'), 'an price_id is lower than.');

        //-------------

        $form = $crawler->selectButton('Update')->form();

        $form['name'] = 'Model1';
        $form['json'] = '{"'.$price->getId().'":"'.($price->getPrice()+10).'"}';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect('/app/d/retailer/model/'));

        $model = $this->em->getRepository('HelloDiPricingBundle:Model')->findOneBy(array('account'=>$this->distributor->getAccount()),array('id'=>'desc'));

        $this->assertNotNull($model);
        $this->assertEquals('Model1', $model->getName());
        $this->assertNull($model->getCurrency());
        $this->assertEquals($this->distributor->getAccount(), $model->getAccount());
        $this->assertEquals('{"'.$price->getId().'":"'.($price->getPrice()+10).'"}', $model->getJson());

        $models_dir = __DIR__."/../../../../../web/uploads/models/";

        $this->assertTrue(file_exists($models_dir.$model->getId().'.json'));
        $this->assertEquals('{"'.$price->getId().'":"'.($price->getPrice()+10).'"}',file_get_contents($models_dir.$model->getId().'.json'));
    }

    public function testDelete() {
        $model = $this->em->getRepository('HelloDiPricingBundle:Model')->findOneBy(array("account" => $this->distributor->getAccount()));
        $this->assertNotNull($model);

        $models_count_before = count($this->em->getRepository("HelloDiPricingBundle:Model")->findBy(array("account" => $this->distributor->getAccount())));

        $this->client->request('GET', '/app/d/retailer/model/'.$model->getId().'/delete');

        $this->assertTrue($this->client->getResponse()->isRedirect('/app/d/retailer/model/'));

        $crawler = $this->client->followRedirect();

        $tr_tags = $crawler->filter('table#example tbody tr');

        $models_count_after = count($this->em->getRepository("HelloDiPricingBundle:Model")->findBy(array("account" => $this->distributor->getAccount())));

        $this->assertEquals($models_count_after, $tr_tags->count());

        $this->assertEquals($models_count_after, $models_count_before - 1);
    }

    public function testSetModel()
    {
        /** @var Retailer $retailer */
        $retailer = $this->distributor->getRetailers()[0];
        $this->assertNotNull($retailer);

        $crawler =  $this->client->request('GET', '/app/d/retailer/model/set_model/'.$retailer->getAccount()->getId());

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'Error in opening page.');

        $models_count = count($this->em->getRepository("HelloDiPricingBundle:Model")->findBy(array("account" => $this->distributor->getAccount())));

        $radio_tags = $crawler->filter('form #form_model input[type="radio"]');

        $this->assertEquals($models_count, $radio_tags->count());

        //-------------
        $form = $crawler->selectButton('form[update]')->form();

        $model = $this->em->getRepository('HelloDiPricingBundle:Model')->findOneBy(array('account'=>$this->distributor->getAccount()));
        $this->assertNotNull($model);

        $form['form[model]'] = $model->getId();

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect('/app/d/retailer/'.$retailer->getAccount()->getId().'/items'));

        //-------------
        $model_prices = json_decode($model->getJson(),true);

        $retailer_prices = $retailer->getAccount()->getPrices();

        $this->assertEquals(count($model_prices), count($retailer_prices));
    }

    public function testUpdatePricesFromModel()
    {
        /** @var Retailer $retailer */
        $retailer = $this->distributor->getRetailers()[0];
        $this->assertNotNull($retailer);

        $model = $this->em->getRepository('HelloDiPricingBundle:Model')->findOneBy(array('account'=>$this->distributor->getAccount()));
        $this->assertNotNull($model);

        $retailer->getAccount()->setModel($model);
        $model->addAccount($retailer->getAccount());
        $this->em->flush();

        $RetailerModelController = new RetailerModelController();
        $RetailerModelController->setContainer($this->client->getContainer());

        $this->assertTrue($this->privateToPublic($RetailerModelController,'updatePricesFromModel',array($model,$retailer->getAccount())));

        $model_prices = json_decode($model->getJson(),true);

        /** @var Price[] $retailer_prices */
        $retailer_prices = $retailer->getAccount()->getPrices();

        $this->assertEquals(count($model_prices), count($retailer_prices), "count(model_prices)=".count($model_prices)." count(retailer_prices)=".count($retailer_prices));

        $is_equal = true;
        foreach($retailer_prices as $price)
            if ($price->getPrice() != $model_prices[$price->getId()]){
                $is_equal = false;
                break;
            }

        $this->assertTrue($is_equal, 'A Price in Retailer exist that his price is not equal');

        //--------------------
        /** @var Retailer[] $retailers */
        $retailers = $this->distributor->getRetailers();

        foreach($retailers as $retailer) {
            $retailer->getAccount()->setModel($model);
            $model->addAccount($retailer->getAccount());
        }

        $this->em->flush();

        $this->assertTrue($this->privateToPublic($RetailerModelController,'updatePricesFromModel',array($model)));

        foreach($retailers as $retailer) {
            /** @var Price[] $retailer_prices */
            $retailer_prices = $retailer->getAccount()->getPrices();

            $this->assertEquals(count($model_prices), count($retailer_prices), "Account[".$retailer->getAccount()->getId()."]: count(model_prices)=".count($model_prices)." count(retailer_prices)=".count($retailer_prices));

            $is_equal = true;
            foreach($retailer_prices as $price)
                if ($price->getPrice() != $model_prices[$price->getId()]){
                    $is_equal = false;
                    break;
                }
            $this->assertTrue($is_equal, 'A Price in Retailer exist that his price is not equal');
        }
    }
}
