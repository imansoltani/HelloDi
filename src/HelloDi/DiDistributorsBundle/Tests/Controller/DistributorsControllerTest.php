<?php

namespace HelloDi\DiDistributorsBundle\Tests\Controller;

use HelloDi\DiDistributorsBundle\Controller\DistributorsController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use HelloDi\DiDistributorsBundle\Entity\Account;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Tests\RequestContentProxy;
use Symfony\Component\HttpFoundation\Request;

class DistributorsControllerTest extends WebTestCase
{
//    public function testIndex()
//    {
//        $client = static::createClient();
//
//        $crawler = $client->request('GET', '/hello/Fabien');
//
//        $this->assertTrue($crawler->filter('html:contains("Hello Fabien")')->count() > 0);
//    }

    public function testFundingTransfer()
    {


//        $dist = new DistributorsController();

        $client = static::createClient();

        $crawler = $client->request('Post',
        'app/m/entities'
        ,array('id'=>2)
        );

    print  $crawler->filter('a')->first()->attr('href');
//
//        $crawlerbutton = $crawler->selectButton('transfer');
//        $form=$crawlerbutton->form(array(
//        'Amount'=> 100,
//        'Description'=>'For Dist',
//        'Communications'=>'For Retailer!'
//
//        ));
//     $client->submit($form);

//die($dist->FundingTransferAction(2));

    }

    }
