<?php

namespace HelloDi\AccountingBundle\Tests\Controller;

use HelloDi\AccountingBundle\Entity\Account;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    /**
    * @var \Doctrine\ORM\EntityManager
    */
    private $em;

    /**
     * @var \HelloDi\AccountingBundle\Controller\DefaultController
     */
    public $accounting;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->em = static::$kernel->getContainer()->get('doctrine.orm.default_entity_manager');
        $this->accounting = static::$kernel->getContainer()->get('accounting');
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->em->close();
    }

    public function privateToPublic($class, $function, array $argumentForFunction = array())
    {
        $method = new \ReflectionMethod($class, $function);
        $method->setAccessible(TRUE);

        return $method->invokeArgs($class,$argumentForFunction);
    }

    public function testCheckAvailableBalance()
    {
        $account = $this->getMock('\HelloDi\AccountingBundle\Entity\Account');
        $account->expects($this->exactly(3))->method('getAccBalance')->will($this->returnValue(1000));
        $account->expects($this->exactly(3))->method('getAccCreditLimit')->will($this->returnValue(1000));
        $account->expects($this->exactly(3))->method('getReserve')->will($this->returnValue(1000));

        $this->assertTrue ($this->privateToPublic($this->accounting,'checkAvailableBalance',array(1000,$account)));
        $this->assertTrue ($this->privateToPublic($this->accounting,'checkAvailableBalance',array(999,$account)));
        $this->assertFalse($this->privateToPublic($this->accounting,'checkAvailableBalance',array(1001,$account)));
    }

    public function testCreateTransaction()
    {
        $account = $this->getMock('\HelloDi\AccountingBundle\Entity\Account');
        $account->expects($this->once())->method('getId')->will($this->returnValue(1));

        $transaction = $this->privateToPublic($this->accounting, 'createTransaction',array(1000,$account,"test description",2.0));

        $this->assertEquals(1000,$transaction->getTranAmount());
        $this->assertEquals(1,$transaction->getAccount()->getId());
        $this->assertEquals((new \DateTime())->getTimestamp(),$transaction->getTranDate()->getTimestamp(),null,3);
        $this->assertEquals("test description",$transaction->getTranDescription());
        $this->assertEquals(2.0,$transaction->getTranFees());
    }

    public function testProcessTransfer()
    {
        $userWithOutAccount = $this->em->getRepository("HelloDiDiDistributorsBundle:User")->findOneBy(array("username"=>"userwithoutaccount4"));

        $Account1 = $this->em->getRepository("HelloDiAccountingBundle:Account")->findOneBy(array("accName"=>"acc1"));
        $Account1->setAccBalance(2000);

        $this->em->flush();

        $transfer = $this->accounting->processTransfer(1000.1,$userWithOutAccount,$Account1,null,"transfer");

        $this->assertNotNull($transfer);
        $this->assertNull($transfer->getOriginTransaction());
        $this->assertEquals($userWithOutAccount->getId(),$transfer->getUser()->getId());
        $this->assertEquals("transfer",$transfer->getDestinationTransaction()->getTranDescription());
        $this->assertEquals(2000+1000.1,$Account1->getAccBalance());

        //---------------

        $userWithAccount2 = $this->em->getRepository("HelloDiDiDistributorsBundle:User")->findOneBy(array("username"=>"userwithaccount2"));
        $userWithAccount2->getAccount()->setAccBalance(3000.5);

        $Account3 = $this->em->getRepository("HelloDiAccountingBundle:Account")->findOneBy(array("accName"=>"acc3"));
        $Account3->setAccBalance(2000);

        $this->em->flush();

        $transfer2 = $this->accounting->processTransfer(1000.1,$userWithAccount2,$Account3,"transfer from","transfer to");

        $this->assertNotNull($transfer2);
        $this->assertNotNull($transfer2->getOriginTransaction());
        $this->assertEquals($userWithAccount2->getId(),$transfer2->getUser()->getId());
        $this->assertEquals("transfer from",$transfer2->getOriginTransaction()->getTranDescription());
        $this->assertEquals("transfer to",$transfer2->getDestinationTransaction()->getTranDescription());
        $this->assertEquals(3000.5-1000.1,$userWithAccount2->getAccount()->getAccBalance());
        $this->assertEquals(2000+1000.1,$Account3->getAccBalance());

        $transfer3 = $this->accounting->processTransfer(1000.1,$userWithAccount2,$Account3,"transfer from","transfer to");
        $this->assertNull($transfer3);
    }
}
