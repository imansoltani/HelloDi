<?php

namespace HelloDi\DiDistributorsBundle\Tests\Controller;

use HelloDi\DiDistributorsBundle\Controller\DistributorsController;
use HelloDi\DiDistributorsBundle\Entity\Transaction;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use HelloDi\DiDistributorsBundle\Entity\Account;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Tests\RequestContentProxy;
use Symfony\Component\HttpFoundation\Request;


class DistributorsControllerTest extends WebTestCase
{



    /**
     * @var \Doctrine\ORM\EntityManager
     */
    public  $em;
    public  $route;


    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
        ;
        $this->route=static::$kernel->getContainer()
            ->get('router');
        ;

    }




    public  function  testf(){



        $client = static::createClient();
        $crawler= $client->request('get',
            '/test'
        );

     $link=$crawler->filter('a')->selectLink('aboutme');
        $link->link();

      $crawler->filter('h1')->addHtmlContent('salamkazem');
        print $crawler->filter('h1')->html();



    }
//}

    public function testFundingTransfer()
    {


       $AccountDist=$this->em->getRepository('HelloDiDiDistributorsBundle:Account')->find(1);
       $AccountRetailer=$this->em->getRepository('HelloDiDiDistributorsBundle:Account')->find(5);

       $tranretailer=new Transaction();
       $tranDist=new Transaction();


        $tranretailer->setTranAmount(100);

        $tranretailer->setTranAction('tran');

        $tranretailer->setAccount($AccountRetailer);

        $tranDist->setTranAmount(-100);

        $tranDist->setTranAction('tran');

        $tranDist->setAccount($AccountDist);


        print $AccountDist->getAccBalance().'=='.$AccountRetailer->getAccBalance().'  '.'Result===>>>>>     ';

        $client = static::createClient();
        $helper = $client->getContainer()->get('hello_di_di_distributors.balancechecker');

     if($helper->isBalanceEnoughForMoney($AccountDist,100))
     {
         $this->em->persist($tranDist);
         $this->em->persist($tranretailer);

     }

        $AccountDist=$this->em->getRepository('HelloDiDiDistributorsBundle:Account')->find(1);
        $AccountRetailer=$this->em->getRepository('HelloDiDiDistributorsBundle:Account')->find(5);

        if($this->assertGreaterThan($AccountDist->getAccBalance(),$AccountRetailer->getAccBalance()))
             die($AccountDist->getAccBalance().' >'.$AccountRetailer->getAccBalance().'       Balance Parent More Than Balance Child');
        else
            die($AccountDist->getAccBalance().' < '.$AccountRetailer->getAccBalance().'      Balance Child More Than Balance Parent');


    }
}

  //  }
//


//        /$this->assertEquals(13,count($entiti));



//        $dist = new DistributorsController();

//        $client = static::createClient();
//        $client->enableProfiler();
//         $client->request('get',
//            '/test'
//            ,array('id'=>2)
//        );
//
//
//
//
//        $profile=$client->getProfile();
//         print   $profile->getCollector('time')->getName();

//
//    print  $crawler->filter('a')->first()->attr('href');
////
//        $crawlerbutton = $crawler->selectButton('transfer');
//        $form=$crawlerbutton->form(array(
//        'Amount'=> 100,
//        'Description'=>'For Dist',
//        'Communications'=>'For Retailer!'
//
//        ));
//     $client->submit($form);

//die($dist->FundingTransferAction(2));



//    public function testIndex()
//    {
//        $client = static::createClient();
//
//        $crawler = $client->request('GET', '/hello/Fabien');
//
//        $this->assertTrue($crawler->filter('html:contains("Hello Fabien")')->count() > 0);
//    }