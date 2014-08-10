<?php

namespace HelloDi\AggregatorBundle\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\AccountingBundle\Container\TransactionContainer;
use HelloDi\AccountingBundle\Controller\DefaultController;
use HelloDi\AggregatorBundle\Entity\Code;
use HelloDi\AggregatorBundle\Entity\Input;
use HelloDi\AggregatorBundle\Entity\Pin;
use HelloDi\AggregatorBundle\Entity\Provider;
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

        if(!$input || !$input->getFileName())
            return;

        $input_db = $this->em->getRepository('HelloDiAggregatorBundle:Input')->findOneBy(array('fileName'=>$input->getFileName()));

        if(!$input_db && file_exists($input->getAbsolutePath()))
            unlink($input->getAbsolutePath());

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
        $pin->setDate(new \DateTime());
        $this->em->persist($pin);

        $this->em->flush();

        return $pin;
    }

    public function creditNoteCodes(Pin $pin)
    {

    }
}
