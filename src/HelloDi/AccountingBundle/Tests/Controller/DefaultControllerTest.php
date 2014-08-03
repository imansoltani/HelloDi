<?php

namespace HelloDi\AccountingBundle\Tests\Controller;

use HelloDi\AccountingBundle\Container\TransactionContainer;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\AccountingBundle\Entity\Transaction;
use HelloDi\CoreBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    /** @var \HelloDi\AccountingBundle\Controller\DefaultController */
    public $accounting;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $client = static::createClient();

        $this->em = $client->getContainer()->get('doctrine.orm.default_entity_manager');
        $this->accounting = $client->getContainer()->get('accounting');
    }

    /**
     * {@inheritDoc}
     */
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

    public function testIsAmountAcceptable()
    {
        $this->privateToPublic($this->accounting,'isAmountAcceptable',array(1000));
        $this->setExpectedException('Exception');
        $this->privateToPublic($this->accounting,'isAmountAcceptable',array(-1000));
    }

    public function testCheckAvailableBalance()
    {
        $account = $this->getMock('\HelloDi\AccountingBundle\Entity\Account');
        $account->expects($this->exactly(3))->method('getBalance')->will($this->returnValue(1000));
        $account->expects($this->exactly(3))->method('getCreditLimitAmount')->will($this->returnValue(1000));
        $account->expects($this->exactly(3))->method('getReserve')->will($this->returnValue(1000));

        $this->assertTrue ($this->privateToPublic($this->accounting,'checkAvailableBalance',array(1000,$account)));
        $this->assertTrue ($this->privateToPublic($this->accounting,'checkAvailableBalance',array(999,$account)));
        $this->assertFalse($this->privateToPublic($this->accounting,'checkAvailableBalance',array(1001,$account)));
    }

    public function testCreateTransaction()
    {
        $account = $this->getMock('\HelloDi\AccountingBundle\Entity\Account');
        $account->expects($this->once())->method('getId')->will($this->returnValue(1));

        /** @var Transaction $transaction */
        $transaction = $this->privateToPublic($this->accounting, 'createTransaction',array(1000,$account,"test description",2.0));

        $this->assertEquals(1000,$transaction->getAmount());
        $this->assertEquals(1,$transaction->getAccount()->getId());
        $this->assertEquals((new \DateTime())->getTimestamp(),$transaction->getDate()->getTimestamp(),null,3);
        $this->assertEquals("test description",$transaction->getDescription());
        $this->assertEquals(2.0,$transaction->getFees());
    }

    public function testProcessTransfer()
    {
        /** @var User $userWithOutAccount */
        $userWithOutAccount = $this->em->getRepository("HelloDiCoreBundle:User")->findOneBy(array("username"=>"master_admin"));

        /** @var Account $Account1 */
        $Account1 = $this->em->getRepository("HelloDiAccountingBundle:Account")->findOneBy(array("name"=>"dist1"));
        $Account1->setBalance(2000);

        $this->em->flush();

        $transfer = $this->accounting->processTransfer(1000.1,$userWithOutAccount,$Account1,null,"transfer");

        $this->assertNotNull($transfer);
        $this->assertNull($transfer->getOriginTransaction());
        $this->assertEquals($userWithOutAccount->getId(),$transfer->getUser()->getId());
        $this->assertEquals("transfer",$transfer->getDestinationTransaction()->getDescription());
        $this->assertEquals(2000+1000.1,$Account1->getBalance());

        //---------------

        /** @var User $userWithAccount2 */
        $userWithAccount2 = $this->em->getRepository("HelloDiCoreBundle:User")->findOneBy(array("username"=>"dist_admin1"));
        $userWithAccount2->getAccount()->setBalance(3000.5);
        $userWithAccount2->getAccount()->setReserve(1000.5);

        /** @var Account $Account3 */
        $Account3 = $this->em->getRepository("HelloDiAccountingBundle:Account")->findOneBy(array("name"=>"B2B Server"));
        $Account3->setBalance(2000);

        $this->em->flush();

        $transfer2 = $this->accounting->processTransfer(1000.1,$userWithAccount2,$Account3,"transfer from","transfer to");

        $this->assertNotNull($transfer2);
        $this->assertNotNull($transfer2->getOriginTransaction());
        $this->assertEquals($userWithAccount2->getId(),$transfer2->getUser()->getId());
        $this->assertEquals("transfer from",$transfer2->getOriginTransaction()->getDescription());
        $this->assertEquals("transfer to",$transfer2->getDestinationTransaction()->getDescription());
        $this->assertEquals(3000.5-1000.1,$userWithAccount2->getAccount()->getBalance());
        $this->assertEquals(1000.5,$userWithAccount2->getAccount()->getReserve());
        $this->assertEquals(2000+1000.1,$Account3->getBalance());

        //---------------
        // 3000.5-1000.5-1000.1 = 999.9 < 1000 ; account hasn't enough money.
        $transfer3 = $this->accounting->processTransfer(1000,$userWithAccount2,$Account3,"transfer from","transfer to");
        $this->assertEquals(null,$transfer3);
    }

    public function testNewCreditLimit()
    {
        /** @var User $userWithOutAccount */
        $userWithOutAccount = $this->em->getRepository("HelloDiCoreBundle:User")->findOneBy(array("username"=>"master_admin"));

        /** @var Account $Account1 */
        $Account1 = $this->em->getRepository("HelloDiAccountingBundle:Account")->findOneBy(array("name"=>"dist1"));
        $Account1->setCreditLimitAmount(2000);

        $this->em->flush();

        $creditLimit = $this->accounting->newCreditLimit(1000.1,$userWithOutAccount,$Account1);

        $this->assertNotNull($creditLimit);
        $this->assertEquals($userWithOutAccount->getId(),$creditLimit->getUser()->getId());
        $this->assertEquals($Account1->getId(),$creditLimit->getAccount()->getId());
        $this->assertEquals(1000.1,$creditLimit->getAmount());
        $this->assertEquals((new \DateTime())->getTimestamp(),$creditLimit->getDate()->getTimestamp(),null,10);
        $this->assertEquals(null,$creditLimit->getTransaction());

        $this->assertEquals(1000.1,$Account1->getCreditLimitAmount());
        //---------------

        /** @var User $userWithAccount2 */
        $userWithAccount2 = $this->em->getRepository("HelloDiCoreBundle:User")->findOneBy(array("username"=>"dist_admin1"));
        $userWithAccount2->getAccount()->setBalance(2000);

        /** @var Account $Account3 */
        $Account3 = $this->em->getRepository("HelloDiAccountingBundle:Account")->findOneBy(array("name"=>"B2B Server"));
        $Account3->setCreditLimitAmount(0);

        $this->em->flush();

        $creditLimit2 = $this->accounting->newCreditLimit(500.1,$userWithAccount2,$Account3);

        $this->assertNotNull($creditLimit2);
        $this->assertNotNull($creditLimit2->getTransaction());
        $this->assertEquals($userWithAccount2->getId(),$creditLimit2->getUser()->getId());
        $this->assertEquals(500.1,$creditLimit2->getAmount());
        $this->assertEquals((new \DateTime())->getTimestamp(),$creditLimit2->getDate()->getTimestamp(),null,10);

        $this->assertEquals(500.1,$Account3->getCreditLimitAmount());
        $this->assertEquals(2000 - 500.1,$userWithAccount2->getAccount()->getBalance());

        //------------------
        $creditLimit3 = $this->accounting->newCreditLimit(500,$userWithAccount2,$Account3);

        $this->assertNotNull($creditLimit3);
        $this->assertEquals(null,$creditLimit3->getTransaction());
        $this->assertEquals($userWithAccount2->getId(),$creditLimit3->getUser()->getId());
        $this->assertEquals(500,$creditLimit3->getAmount());
        $this->assertEquals((new \DateTime())->getTimestamp(),$creditLimit3->getDate()->getTimestamp(),null,10);

        $this->assertEquals(500,$Account3->getCreditLimitAmount());
        $this->assertEquals(2000 - 500.1,$userWithAccount2->getAccount()->getBalance());
        //----------------

        $creditLimit4 = $this->accounting->newCreditLimit(1500,$userWithAccount2,$Account3);

        $this->assertEquals(null,$creditLimit4);

        $this->assertEquals(500,$Account3->getCreditLimitAmount());
        $this->assertEquals(2000 - 500.1,$userWithAccount2->getAccount()->getBalance());
    }

    public function testReserveAmount()
    {
        /** @var Account $Account1 */
        $Account1 = $this->em->getRepository("HelloDiAccountingBundle:Account")->findOneBy(array("name"=>"dist1"));
        $Account1->setBalance(2000);
        $Account1->setCreditLimitAmount(500);
        $Account1->setReserve(1000.1);
        $this->em->flush();

        $result1 = $this->accounting->reserveAmount(1000,$Account1,true);
        $this->assertTrue($result1);

        $result2 = $this->accounting->reserveAmount(500,$Account1,true);
        $this->assertFalse($result2);

        $result3 = $this->accounting->reserveAmount(1000,$Account1,false);
        $this->assertTrue($result3);

        $result4 = $this->accounting->reserveAmount(1000,$Account1,true);
        $this->assertTrue($result4);
    }

    public function testProcessTransaction()
    {
        /** @var Account $Account1 */
        $Account1 = $this->em->getRepository("HelloDiAccountingBundle:Account")->findOneBy(array("name"=>"dist1"));

        /** @var Account $Account2 */
        $Account2 = $this->em->getRepository("HelloDiAccountingBundle:Account")->findOneBy(array("name"=>"B2B Server"));

        $Account1->setCreditLimitAmount(0);
        $Account1->setReserve(0);

        $Account2->setCreditLimitAmount(0);
        $Account2->setReserve(0);

        $Account1->setBalance(2000);
        $this->em->flush();

        $array1 = array();
        $array1 []= new TransactionContainer($Account1,1000,"for test", 1.1);

        $result1 = $this->accounting->processTransaction($array1);

        $this->assertNotFalse($result1);
        $this->assertEquals(2000+1000,$Account1->getBalance());

        //-----------------------------------------------------------------
        $Account2->setBalance(2000);
        $this->em->flush();

        $array2 = array();
        $array2 []= new TransactionContainer($Account2,-1000,"for test", 1.1);

        $result2 = $this->accounting->processTransaction($array2);

        $this->assertNotFalse($result2);
        $this->assertEquals(2000-1000,$Account2->getBalance());

        //-----------------------------------------------------------------
        $Account2->setBalance(2000);
        $this->em->flush();

        $array2 = array();
        $array2 []= new TransactionContainer($Account2,-2000.1,"for test", 1.1);

        $result2 = $this->accounting->processTransaction($array2);

        $this->assertFalse($result2);
        $this->assertEquals(2000,$Account2->getBalance());

        //-----------------------------------------------------------------
        $Account1->setBalance(2000);
        $Account2->setBalance(0);
        $this->em->flush();

        $array3 = array();
        $array3 []= new TransactionContainer($Account1,1000,"for test", 1.1);
        $array3 []= new TransactionContainer($Account2,500,"for test", 1.0);
        $array3 []= new TransactionContainer($Account1,500,"for test", 1.0);
        $array3 []= new TransactionContainer($Account2,-500,"for test", 1.0);
        $array3 []= new TransactionContainer($Account2,500,"for test", 1.0);
        $array3 []= new TransactionContainer($Account1,-400,"for test", 1.0);

        $result3 = $this->accounting->processTransaction($array3);

        $this->assertNotFalse($result3);
        $this->assertEquals(2000+1000+500-400,$Account1->getBalance());
        $this->assertEquals(0+500-500+500,$Account2->getBalance());

        //--------------------------------------
        $Account1->setBalance(2000);
        $Account2->setBalance(0);
        $this->em->flush();

        $array4 = array();
        $array4 []= new TransactionContainer($Account1,1000,"for test", 1.1);
        $array4 []= new TransactionContainer($Account2,500,"for test", 1.0);
        $array4 []= new TransactionContainer($Account1,500,"for test", 1.0);
        $array4 []= new TransactionContainer($Account2,-500,"for test", 1.0);
        $array4 []= new TransactionContainer($Account2,500,"for test", 1.0);
        $array4 []= new TransactionContainer($Account1,-400,"for test", 1.0);
        $array4 []= new TransactionContainer($Account2,-500.1,"for test", 1.0);

        //account1 = 2000+1000+500-400 = 3100 > 0
        //account2 = 0+500-500+500-500.1 = -0.1 < 0

        $result4 = $this->accounting->processTransaction($array4);

        $this->assertFalse($result4);
        $this->assertEquals(0,$Account2->getBalance());
        $this->assertEquals(2000,$Account1->getBalance());
    }


}
