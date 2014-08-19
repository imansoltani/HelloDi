<?php

namespace HelloDi\AggregatorBundle\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\AggregatorBundle\Entity\Provider;
use HelloDi\CoreBundle\Entity\Country;
use HelloDi\CoreBundle\Entity\Item;
use HelloDi\CoreBundle\Entity\ItemDesc;
use HelloDi\CoreBundle\Entity\Operator;
use HelloDi\PricingBundle\Entity\Price;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class TopUpController
 * @package HelloDi\AggregatorBundle\Controller
 */
class TopUpController extends Controller
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * constructor
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param File $file
     * @param int $delimiter
     * @param Provider $provider
     * @param ItemDesc[] $itemDescriptions
     * @throws \Exception
     * @return string
     */
    public function importItemsAndPricesFromFile(File $file, $delimiter, Provider $provider, array $itemDescriptions)
    {
        ini_set('max_execution_time', 80);

        $file = fopen($file, 'r');

        $headers = explode($delimiter, strtoupper(trim(fgets($file))));
        if(!$headers || count($headers) <= 1)
            throw new \Exception("file can't read.");

        $col_num = array(
            'CARRIER CODE' => -1,
            'COUNTRY CODE' => -1,
            'DENOMINATION' => -1,
            'DISCOUNT' => -1,
            'TOP UP CURRENCY' => -1,
            'TOP UP VALUE' => -1
        );

        /** @var Country[] $countriesArray */
        $countriesArray = $this->em->createQueryBuilder()
            ->select('country')
            ->from('HelloDiCoreBundle:Country', 'country')
            ->getQuery()->getResult();

        $countries = array();
        foreach ($countriesArray as $row)
            $countries [$row->getIso()] = $row;

        foreach ($col_num as $name => $num) {
            if (!$col_num[$name] = array_search($name, $headers))
                throw new \Exception($name." not exist in header.");
        }

        $count = 0;
        $log = "";

        while ($line = fgets($file)) {
            $row = explode($delimiter, trim($line));
            $log .= "---------------- line ".($count+2)." ----------------<br>";

            //----------------operator----------------
            $operator = $this->em->getRepository('HelloDiCoreBundle:Operator')->findOneBy(array('carrierCode'=>$row[$col_num['CARRIER CODE']]));
            if(!$operator) {
                $operator = new Operator();
                $operator->setName($row[$col_num['CARRIER CODE']]);
                $operator->setCarrierCode($row[$col_num['CARRIER CODE']]);
                $this->em->persist($operator);
                $this->em->flush();

                $log .= "operator '".$row[$col_num['CARRIER CODE']]."' created.<br>";
            }
            else
                $log .= "operator '".$row[$col_num['CARRIER CODE']]."' already exist.<br>";

            //----------------operator logo-----------------
            if($operator->getLogoExtension() == null) {
                $source_path = "uploads/logos/predefined_logos/".strtolower($row[$col_num['COUNTRY CODE']]."_".$row[$col_num['CARRIER CODE']]).".png";
                if(!file_exists($source_path))
                    $log .= "can't find Logo file ".$source_path."<br>";
                elseif (!copy($source_path,"uploads/logos/".$operator->getId().".png"))
                    $log .= "can't copy Logo file ".$source_path."<br>";
                else {
                    $operator->setLogoExtension("png");
                }
            }

            //----------------item-----------------
            // item_name = carrier_code country_cde topup_value currency
            $item_name = $row[$col_num['CARRIER CODE']]." ".$row[$col_num['COUNTRY CODE']]." ".$row[$col_num['TOP UP VALUE']].$row[$col_num['TOP UP CURRENCY']];
            // item_code = country_code/item_type/operator_name/item_name(_)
            $item_code = $row[$col_num['COUNTRY CODE']].'/imtu/'.$row[$col_num['CARRIER CODE']].'/'.str_replace(' ','_',$item_name);

            $item = $this->em->getRepository('HelloDiCoreBundle:Item')->findOneBy(array('code'=>$item_code));
            if(!$item) {
                $new_item = new Item();
                $new_item->setName($item_name);
                $new_item->setFaceValue($row[$col_num['TOP UP VALUE']]);
                $new_item->setCurrency($row[$col_num['TOP UP CURRENCY']]);
                $new_item->setType(Item::IMTU);
                $new_item->setAlertMinStock(0);
                $new_item->setCode($item_code);
                $new_item->setDateInsert(new \DateTime('now'));
                $new_item->setOperator($operator);
                $new_item->setCountry($countries[$row[$col_num['COUNTRY CODE']]]);
                $this->em->persist($new_item);

                $log .= "item '".$item_name."' created.<br>";
            }
            else {
                $item->setFaceValue($row[$col_num['TOP UP VALUE']]);
                $item->setCurrency($row[$col_num['TOP UP CURRENCY']]);

                $log .= "item '".$item_name."' already exist.<br>";
            }

            //----------------item_descriptions-----------------
            foreach($itemDescriptions as $itemDescription) {
                $item_desc = $item ? $this->em->getRepository('HelloDiCoreBundle:ItemDesc')->findOneBy(array('language'=>$itemDescription->getLanguage(),'item'=>$item)) : null;
                if(!$item_desc) {
                    $item_desc = new ItemDesc();
                    $item_desc->setItem(isset($new_item) ? $new_item : $item);
                    $item_desc->setLanguage($itemDescription->getLanguage());
                    $item_desc->setDescription($itemDescription->getDescription());
                    $this->em->persist($item_desc);

                    $log .= "item description for language '".$itemDescription->getLanguage()."' created.<br>";
                }
                else {
                    $item_desc->setDescription($itemDescription->getDescription());

                    $log .= "item description for language '".$itemDescription->getLanguage()."' already exist.<br>";
                }
            }

            //----------------find or create price-----------------
            $price = $item ? $this->em->getRepository('HelloDiPricingBundle:Price')->findOneBy(array('item'=>$item,'account'=>$provider->getAccount())) : null;
            if(!$price) {
                $price = new Price();
                $price->setItem(isset($new_item) ? $new_item : $item);
                $price->setAccount($provider->getAccount());
                $price->setPrice( $row[$col_num['DENOMINATION']]- (intval($row[$col_num['DISCOUNT']])/100*$row[$col_num['DENOMINATION']]) );
                $price->setDenomination($row[$col_num['DENOMINATION']]);
                $this->em->persist($price);

                $log .= "price for item '".$item_name."' created.<br>";
            }
            else {
                $price->setPrice( $row[$col_num['DENOMINATION']]- (intval($row[$col_num['DISCOUNT']])/100*$row[$col_num['DENOMINATION']]) );
                $price->setDenomination($row[$col_num['DENOMINATION']]);

                $log .= "price for item '".$item_name."' already exist.<br>";
            }

            $this->em->flush();
            $count++;
        }

        return $log;
    }
}
