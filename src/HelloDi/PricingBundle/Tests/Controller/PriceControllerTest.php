<?php

namespace HelloDi\PricingBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PriceControllerTest extends WebTestCase
{
    public function testPricing()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/pricing');
    }

}
