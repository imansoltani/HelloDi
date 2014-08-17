<?php

namespace HelloDi\AggregatorBundle\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\AccountingBundle\Container\TransactionContainer;
use HelloDi\AccountingBundle\Controller\DefaultController;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\AggregatorBundle\Entity\Code;
use HelloDi\AggregatorBundle\Entity\Input;
use HelloDi\AggregatorBundle\Entity\Pin;
use HelloDi\AggregatorBundle\Entity\Provider;
use HelloDi\CoreBundle\Entity\Item;
use HelloDi\CoreBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class ServiceController
 * @package HelloDi\AggregatorBundle\Controller
 */
class ServiceController extends Controller
{
    /**
     * @var EntityManager
     */
    private $em;
    /**
     * @var DefaultController
     */
    private $accounting;

    /**
     * constructor
     */
    public function __construct(EntityManager $em, DefaultController $accounting)
    {
        $this->em = $em;
        $this->accounting = $accounting;
    }

    /**
     * @param Input $input
     * @param string $delimiter
     * @return int count
     * @throws \Exception
     */
    public function testFileCodes(Input $input, $delimiter = ';')
    {
        $codes = array_map('current', $this->em->createQueryBuilder()
            ->select('code.serialNumber')
            ->from('HelloDiAggregatorBundle:Code', 'code')
            ->innerJoin('code.input', 'input')
            ->where('input.provider = :provider')->setParameter('provider', $input->getProvider())
            ->andWhere('code.item = :item')->setParameter('item', $input->getItem())
            ->getQuery()->getScalarResult());

        $count = 0;

        $file = fopen($input->getAbsolutePath(), 'r');

        $headers = explode($delimiter, trim(fgets($file)));
        if(!$headers || count($headers) <= 1)
            throw new \Exception("file is empty.");

        $serial_col_num = array_search('SerialNumber', $headers);
        $pin_col_num = array_search('PinCode', $headers);
        if($pin_col_num === false || $serial_col_num === false)
            throw new \Exception("SerialNumber and PinCode not exist in header.");

        $read = array();

        while ($line = fgets($file)) {
            $lineArray = explode($delimiter, trim($line));

            if(in_array($lineArray[$serial_col_num], $codes))
                throw new \Exception("Code exist in db in line ".($count+2)." of file.");

            if(in_array($lineArray[$serial_col_num], $read))
                throw new \Exception("duplicate code in file in line ".($count+2).".");

            $read []= $lineArray[$serial_col_num];

            $count++;
        }

        return $count;
    }

    /**
     * @param Input $input
     * @param string $delimiter
     * @return Input
     * @throws \Exception
     */
    public function buyingCodes(Input $input, $delimiter = ';')
    {
        $price = $this->em->getRepository('HelloDiPricingBundle:Price')->findOneBy(array(
                'account'=>$input->getProvider()->getAccount(),
                'item' => $input->getItem()
            ));

        try {
            $count = $this->testFileCodes($input, $delimiter);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $result = $this->accounting->processTransaction(array(new TransactionContainer(
                $input->getProvider()->getAccount(),
                $price->getPrice() * $count,
                'buy batch codes.'
            )), false);

        if($result == false)
            throw new \Exception("Account hasn't enough Balance.");

        $input->setProviderTransaction($result[0]);
        $input->setCount($count);
        $this->em->persist($input);

        $file = fopen($input->getAbsolutePath(), 'r');

        $headers = explode($delimiter, trim(fgets($file)));

        $serial_col_num = array_search('SerialNumber', $headers);
        $pin_col_num = array_search('PinCode', $headers);

        while ($line = fgets($file)) {
            $lineArray = explode($delimiter, trim($line));

            $code = new Code();
            $code->setItem($input->getItem());
            $code->setInput($input);
            $code->setStatus(Code::AVAILABLE);
            $code->setPin($lineArray[$pin_col_num]);
            $code->setSerialNumber($lineArray[$serial_col_num]);
            $this->em->persist($code);
        }

        $this->em->flush();

        return $input;
    }

    /**
     * @param Session $session
     */
    public function clearUploadInSession(Session $session)
    {
        /** @var Input $input */
        $input = $session->get('last_upload');

        if($input) $input->removeUpload();

        $session->remove('last_upload');
        $session->remove('last_upload_delimiter');
    }

    /**
     * @param Pin $pin
     * @return Pin
     * @throws \Exception
     */
    public function deadBeatCodes(Pin $pin)
    {
        $count = 0;
        $sum_price = 0;

        /** @var Provider $provider */
        $provider = null;

        foreach($pin->getCodes() as $code) {
            /** @var Code $code */
            if($code->getStatus() != Code::AVAILABLE)
                throw new \Exception("All Codes must be Available.");

            if(!$provider)
                $provider = $code->getInput()->getProvider();
            elseif($provider != $code->getInput()->getProvider())
                throw new \Exception("All Codes must have same Provider.");

            $code->setStatus(Code::UNAVAILABLE);

            $sum_price += $code->getInput()->getProviderTransaction()->getAmount() / $code->getInput()->getCount();

            $count++;
        }

        $result = $this->accounting->processTransaction(array(new TransactionContainer(
                $provider->getAccount(),
                -$sum_price,
                'deadbeat batch codes.'
            )), false);

        if($result == false)
            throw new \Exception("Account hasn't enough Balance.");

        $pin->setTransaction($result[0]);

        $pin->setCount($count);
        $pin->setType(Pin::DEAD_BEAT);
        $pin->setDate(new \DateTime());
        $this->em->persist($pin);

        $this->em->flush();

        return $pin;
    }

    /**
     * @param Pin $pin
     * @param Account $distributorAccount
     * @return Pin
     * @throws \Exception
     */
    public function creditNoteCodes(Pin $pin, Account $distributorAccount)
    {
        $count = 0;
        $sum_price_retailer = 0;
        $sum_price_distributor = 0;

        /** @var Account $retailerAccount */
        $retailerAccount = null;

        /** @var Item $item */
        $item = null;

        foreach($pin->getCodes() as $code) {
            /** @var Code $code */
            if($code->getStatus() != Code::UNAVAILABLE)
                throw new \Exception("All Codes must be Unavailable.");

            if(!$item) {
                $item = $code->getItem();
            }
            elseif($item != $code->getItem())
                throw new \Exception("All Codes must have same item.");

            $last_pin_for_code_id = $this->em->createQueryBuilder()
                ->select('pin.id')
                ->from('HelloDiAggregatorBundle:Pin', 'pin')
                ->innerJoin('pin.codes', 'code')
                ->where('code = :code')->setParameter('code', $code)
                ->orderBy('pin.id', 'desc')
                ->setMaxResults(1)
                ->getQuery()->getSingleScalarResult();

            /** @var Pin $last_pin_for_code */
            $last_pin_for_code = $this->em->createQueryBuilder()
                ->select('pin')
                ->from('HelloDiAggregatorBundle:Pin', 'pin')
                ->where('pin.id = :id')->setParameter('id', $last_pin_for_code_id)
                ->andWhere('pin.type = :type')->setParameter('type', Pin::SALE)
                ->innerJoin('pin.commissionerTransaction', 'dist_transaction')
                ->andWhere('dist_transaction.account = :dist_acc')->setParameter('dist_acc', $distributorAccount)
                ->innerJoin('pin.transaction', 'ret_transaction')
                ->getQuery()->getOneOrNullResult();

            if(!$last_pin_for_code)
                throw new \Exception("A code is not sell or sale is not for this distributor.");
            else {
                if(!$retailerAccount) {
                    $retailerAccount = $last_pin_for_code->getTransaction()->getAccount();
                }
                elseif($retailerAccount != $last_pin_for_code->getTransaction()->getAccount())
                    throw new \Exception("All Codes must have same Retailer.");
            }

            $code->setStatus(Code::AVAILABLE);

            $sum_price_retailer += $last_pin_for_code->getTransaction()->getAmount() / $last_pin_for_code->getCount();
            $sum_price_distributor += $last_pin_for_code->getCommissionerTransaction()->getAmount() / $last_pin_for_code->getCount();

            $count++;
        }

        $result = $this->accounting->processTransaction(array(
                new TransactionContainer(
                    $retailerAccount,
                    -$sum_price_retailer,
                    'creditNote batch codes.'
                ),
                new TransactionContainer(
                    $distributorAccount,
                    -$sum_price_distributor,
                    'creditNote batch codes commission.'
                )
            ), false);

        if($result == false)
            throw new \Exception("Account hasn't enough Balance.");

        $pin->setTransaction($result[0]);
        $pin->setCommissionerTransaction($result[1]);

        $pin->setCount($count);
        $pin->setType(Pin::CREDIT_NOTE);
        $pin->setDate(new \DateTime());
        $this->em->persist($pin);

        $this->em->flush();

        return $pin;
    }

    /**
     * @param User $user
     * @param Item $item
     * @param $count
     * @return Pin
     * @throws \Exception
     */
    public function sellCodes(User $user, Item $item, $count)
    {
        $retailer = $this->em->getRepository('HelloDiRetailerBundle:Retailer')->findOneBy(array('account'=> $user->getAccount()));
        if(!$retailer)
            throw new \Exception("Retailer not found for user.");

        $distributor = $retailer->getDistributor();

        $price_retailer = $this->em->getRepository('HelloDiPricingBundle:Price')->findOneBy(array('account'=>$retailer->getAccount(), 'item'=>$item));
        if(!$price_retailer)
            throw new \Exception("Distributor hasn't this item.");

        $price_distributor = $this->em->getRepository('HelloDiPricingBundle:Price')->findOneBy(array('account'=>$distributor->getAccount(), 'item'=>$item));
        if(!$price_distributor)
            throw new \Exception("Retailer hasn't this item.");

        $commission = $price_retailer->getPrice() - $price_distributor->getPrice();

//        $tax_history = $this->em->getRepository('HelloDiCoreBundle:TaxHistory')->findOneBy(array('tax'=>$price_distributor->getTax(),'taxEnd'=>null));

        $pin = new Pin();
        $pin->setCount($count);
        $pin->setDate(new \DateTime());
        $pin->setType(Pin::SALE);
        $pin->setUser($user);

        $result = $this->accounting->processTransaction(array(
                new TransactionContainer(
                    $retailer->getAccount(),
                    -$count * $price_retailer->getPrice(),
                    'sell batch codes.'
                ),
                new TransactionContainer(
                    $distributor->getAccount(),
                    $count * $commission,
                    'sell batch codes commission.'
                )
            ), false);

        if($result == false)
            throw new \Exception("Retailer hasn't enough balance.");

        $pin->setTransaction($result[0]);
        $pin->setCommissionerTransaction($result[1]);

        $codes = $this->em->createQueryBuilder()
            ->select('code')
            ->from('HelloDiAggregatorBundle:Code', 'code')
            ->where('code.status = :status')->setParameter('status', Code::AVAILABLE)
            ->andWhere('code.item = :item')->setParameter('item', $item)
            ->setMaxResults($count)
            ->getQuery()->getResult();

        if(count($codes) < $count)
            throw new \Exception("There is no enough Code for the Item.");

        foreach($codes as $code) {
            /** @var Code $code */
            $code->setStatus(Code::UNAVAILABLE);
            $pin->addCode($code);
        }

        $this->em->persist($pin);
        $this->em->flush();

        return $pin;
    }
}
