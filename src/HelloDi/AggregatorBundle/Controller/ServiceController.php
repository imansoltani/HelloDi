<?php

namespace HelloDi\AggregatorBundle\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\AccountingBundle\Container\TransactionContainer;
use HelloDi\AccountingBundle\Controller\DefaultController;
use HelloDi\CoreBundle\Entity\Code;
use HelloDi\CoreBundle\Entity\Input;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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

    public function testFileCodes(Input $input, $delimiter = ';')
    {
        $codes = array_map('current', $this->em->createQueryBuilder()
            ->select('code.serialNumber')
            ->from('HelloDiCoreBundle:Code', 'code')
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

        $input->setProviderTransaction($result[0]);
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
}
