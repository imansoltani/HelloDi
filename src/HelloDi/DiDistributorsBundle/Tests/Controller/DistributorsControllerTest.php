<?php

namespace HelloDi\DiDistributorsBundle\Tests\Controller;

use HelloDi\DiDistributorsBundle\Controller\DistributorsController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use HelloDi\DiDistributorsBundle\Entity\Account;

class DistributorsControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/hello/Fabien');

        $this->assertTrue($crawler->filter('html:contains("Hello Fabien")')->count() > 0);
    }

    public function FundingTransferAction()
    {
        $dist = new DistributorsController();
//        $result = $dist->FundingTransferAction();

        // assert that your calculator added the numbers correctly!
//        $this->assertEquals(42, $result);
    }

}
