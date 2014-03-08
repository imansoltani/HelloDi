<?php

namespace HelloDi\AccountingBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    private $em;
    public $accounting;

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->em = static::$kernel->getContainer()->get('doctrine.orm.default_entity_manager');
        $this->accounting = static::$kernel->getContainer()->get('accounting');
    }

    public function testCheckAvailableBalance()
    {
        $account = $this->getMock('\HelloDi\AccountingBundle\Entity\Account');
        $account->expects($this->once())->method('getAccBalance')->will($this->returnValue(1000));
        $account->expects($this->once())->method('getAccCreditLimit')->will($this->returnValue(1000));
        $account->expects($this->once())->method('getReserve')->will($this->returnValue(1000));


        $method = new \ReflectionMethod($this->accounting, 'checkAvailableBalance');
        $method->setAccessible(TRUE);

        $this->assertTrue($method->invoke($this->accounting,500,$account));

//        $this->assertTrue($this->accounting->checkAvailableBalance(1000,$account));
    }
}
