<?php
namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use HelloDi\DiDistributorsBundle\Entity\Account;
use HelloDi\DiDistributorsBundle\Entity\Denomination;
use HelloDi\DiDistributorsBundle\Entity\Item;
use HelloDi\DiDistributorsBundle\Entity\ItemDesc;
use HelloDi\DiDistributorsBundle\Entity\Operator;
use HelloDi\DiDistributorsBundle\Entity\Price;
use HelloDi\DiDistributorsBundle\Entity\PriceHistory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\HttpFoundation\Response;

class tempController extends Controller
{
    private $item_descs_string = array(
        "en"=>"<b>Transaction details</b><br>
            Date: {{printdate}}<br>
            Transaction No.: {{tranid}}<br>
            Receiver phone number: {{recievernumber}}<br>
            Operator: {{operator}}<br>
            Value sent: {{valuesent}}<br>
            Value paid: {{valuepaid}}<br>
            Entity Name: {{entityname}}<br>
            Address: <br>
            {{entityadrs1}}<br>
            {{entityadrs2}}<br>
            {{entityadrs3}}<br>",
        "fr"=>"<b>Reçu</b><br>
            Date: {{printdate}}<br>
            Numéro transaction: {{tranid}}<br>
            Numéro de téléphone (receveur): {{recievernumber}}<br>
            Opérateur: {{operator}}<br>
            Valeur envoyée (monnaie locale): {{valuesent}}<br>
            Montant payé: {{valuepaid}}<br>
            Nom de l'entité: {{entityname}}<br>
            Adresse: <br>
            {{entityadrs1}}<br>
            {{entityadrs2}}<br>
            {{entityadrs3}}<br>"
    );

    private function csv_to_array($filename='', $delimiter=',')
    {
        if(!file_exists($filename) || !is_readable($filename))
            return FALSE;
        $header = NULL;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== FALSE)
        {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
            {
                if(!$header) $header = $row;
                else         $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        return $data;
    }

    public function itemAddAction()
    {
        ini_set('max_execution_time', 80);
        $array = $this->csv_to_array("uploads/temp/provider.csv");
        if(!$array) die("<span style='color: red'>Unable to read file.</span>");

        $em = $this->getDoctrine()->getManager();
        $provider = $em->getRepository('HelloDiDiDistributorsBundle:Account')->findOneBy(array('accName'=>'B2Bserver'));
        if(!$provider) die("<span style='color: red'>provider not found.</span>");

        $provider->setAccCurrency("CHF");
        $em->flush();

        foreach($array as $key=>$row)
        {
            if($row["Fixed/ Floating"] == "Fixed") $this->insertItem($provider,$key,$row,$em);
        }

        return new Response("done");
    }

    private function insertItem(Account $provider,$key,array $row,ObjectManager $em)
    {
        echo("------------------row: $key--------------------<br>");

        //----------------find or create operator-----------------
        $operator = $em->getRepository('HelloDiDiDistributorsBundle:Operator')->findOneBy(array('name'=>$row['CARRIER CODE']));
        if(!$operator)
        {
            $operator = new Operator();
            $operator->setName($row['CARRIER CODE']);
            $operator->setCarrierCode($row['CARRIER CODE']);
            $em->persist($operator);
            $em->flush();
            echo("<span style='color: green'>operator '".$row['CARRIER CODE']."' created. operator_id=".$operator->getId()."</span><br>");
        }
        else
            echo("<span style='color: blue'>operator '".$row['CARRIER CODE']."' already exist. operator_id=".$operator->getId()."</span><br>");

        //----------------set logo operator if not exist-----------------
        if($operator->getLogo() == null)
        {
            $source_path = "uploads/temp/Operator Logos/".$row['COUNTRY CODE']."/".$row['CARRIER CODE']."/".
                strtolower($row['COUNTRY CODE']."_".$row['CARRIER CODE']).".png";
            if(!file_exists($source_path))
                echo("<span style='color: yellow'>can't find Logo file ".$source_path."</span><br>");
            elseif (!copy($source_path,"uploads/logos/".$operator->getId().".png"))
                echo("<span style='color: yellow'>can't copy Logo file ".$source_path."</span><br>");
            else
            {
                $operator->setLogo($operator->getId().".png");
                $em->flush();
            }
        }

        //----------------find or create item-----------------
        // item_name = carriercode country topupvalue currency
        $item_name = $row['CARRIER CODE']." ".$row['Country']." ".$row['Fixed Top Up Value'].$row['Top Up Currency'];
        // item_code = countrycode/itemtype/operatorname/itemname(_)
        $itemcode = $row['COUNTRY CODE'].'/imtu/'.$row['CARRIER CODE'].'/'.str_replace(' ','_',$item_name);
        $item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->findOneBy(array('itemCode'=>$itemcode));
        if(!$item)
        {
            $item = new Item();
            $item->setItemName($item_name);
            $item->setItemFaceValue($row['Fixed Top Up Value']);
            $item->setItemCurrency($row['Top Up Currency']);
            $item->setItemType('imtu');
            $item->setAlertMinStock(0);
            $item->setItemCode($itemcode);
            $item->setItemDateInsert(new \DateTime('now'));
            $item->setOperator($operator);
            $item->setCountry($em->getRepository('HelloDiDiDistributorsBundle:Country')->findOneBy(array('iso'=>$row['COUNTRY CODE'])));
            $em->persist($item);
            $em->flush();
            echo("<span style='color: green'>item '".$item_name."' created. item_id=".$item->getId()."</span><br>");
        }
        else
        {
            $item->setItemFaceValue($row['Fixed Top Up Value']);
            $item->setItemCurrency($row['Top Up Currency']);
            $em->flush();
            echo("<span style='color: blue'>item '".$item_name."' already exist. item_id=".$item->getId()." updated.</span><br>");
        }

        //----------------find or create item_denomination-----------------
        $denomination = $em->getRepository('HelloDiDiDistributorsBundle:Denomination')->findOneBy(array('currency'=>'CHF','Item'=>$item));
        if(!$denomination)
        {
            $denomination = new Denomination();
            $denomination->setDenomination($row['DENOMINATION (CHF)']);
            $denomination->setCurrency('CHF');
            $denomination->setItem($item);
            $em->persist($denomination);
            $em->flush();
            echo("<span style='color: green'>item denomination for '".$item_name."' created. denomination_id=".$denomination->getId()."</span><br>");
        }
        else
        {
            $denomination->setDenomination($row['DENOMINATION (CHF)']);
            $em->flush();
            echo("<span style='color: blue'>item denomination for '".$item_name."' already exist. denomination_id=".$denomination->getId()." updated.</span><br>");
        }

        //----------------find or create item_descs-----------------
        foreach($this->item_descs_string as $lang=>$item_desc_string)
        {
            $item_desc = $em->getRepository('HelloDiDiDistributorsBundle:ItemDesc')->findOneBy(array('desclang'=>$lang,'Item'=>$item));
            if(!$item_desc)
            {
                $item_desc = new ItemDesc();
                $item_desc->setItem($item);
                $item_desc->setDesclang($lang);
                $item_desc->setDescdesc($item_desc_string);
                $em->persist($item_desc);
            }
            else
            {
                $item_desc->setDescdesc($item_desc_string);
            }
        }
        $em->flush();

        echo("<span style='color: green'>item_descs for item '".$item_name."' created. item_id=".$item->getId()."</span><br>");

        //----------------find or create price-----------------
        $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Item'=>$item,'Account'=>$provider));
        if(!$price)
        {
            $price = new Price();
            $price->setItem($item);
            $price->setAccount($provider);
            $price->setPrice( $row['DENOMINATION (CHF)']- (intval($row['Discount'])/100*$row['DENOMINATION (CHF)']) );
            $price->setPriceCurrency($provider->getAccCurrency());
            $price->setPriceStatus(1);
            $price->setIsFavourite(0);
            $em->persist($price);

            $priceHistory = new PriceHistory();
            $priceHistory->setDate(new \DateTime('now'));
            $priceHistory->setPrice($price->getPrice());
            $priceHistory->setPrices($price);
            $em->persist($priceHistory);
            $em->flush();
            echo("<span style='color: green'>price for item '".$item_name."' created. price_id=".$price->getId()." price_history_id=".$priceHistory->getId()."</span><br>");
        }
        else
        {
            $price->setPrice( $row['DENOMINATION (CHF)']- (intval($row['Discount'])/100*$row['DENOMINATION (CHF)']) );

            $priceHistory = $em->getRepository('HelloDiDiDistributorsBundle:PriceHistory')->findOneBy(array('Prices'=>$price));
            $priceHistory->setPrice($price->getPrice());
            echo("<span style='color: blue'>price for item '".$item_name."' already exist. price_id=".$price->getId().". Updated.</span><br>");
            $em->flush();
        }
    }

    public function addIMTUItemsToDistAction($distId)
    {
        $array = $this->csv_to_array("uploads/temp/distributor.csv");
        if(!$array) die("<span style='color: red'>Unable to read file.</span>");

        $em = $this->getDoctrine()->getManager();

        $tax = $em->getRepository('HelloDiDiDistributorsBundle:Tax')->findOneBy(array("Country"=>null));

        $dist = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($distId);
        if (!$dist || $dist->getAccType() != 0) die("<span style='color: red'>Unable to find account.</span>");

        foreach($array as $row)
        {
            if($row["Fixed/ Floating"] != "Fixed") continue;
            $item_name = $row['CARRIER CODE']." ".$row['Country']." ".floatval($row['Fixed Top Up Value']).$row['Top Up Currency'];
            $itemcode = $row['COUNTRY CODE'].'/imtu/'.$row['CARRIER CODE'].'/'.str_replace(' ','_',$item_name);
            $item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->findOneBy(array('itemCode'=>$itemcode));
            if(!$item)
            {
                echo("<span style='color:red'>item '".$item_name."' not found. price can't create.</span><br>");
                continue;
            }

            $denomination = $em->getRepository('HelloDiDiDistributorsBundle:Denomination')->findOneBy(array('currency'=>'CHF','Item'=>$item));
            if(!$denomination)
            {
                echo("<span style='color:red'>item denomination for '".$item_name."' for CHF not found. price can't create.</span><br>");
                continue;
            }

            $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Item'=>$item,'Account'=>$dist));
            if(!$price)
            {
                $price = new Price();
                $price->setItem($item);
                $price->setAccount($dist);
                $price->setPrice( $row['Net Price'] );
                $price->setPriceCurrency($dist->getAccCurrency());
                $price->setPriceStatus(1);
                $price->setIsFavourite(0);
                $price->setTax($tax);
                $em->persist($price);

                $priceHistory = new PriceHistory();
                $priceHistory->setDate(new \DateTime('now'));
                $priceHistory->setPrice($price->getPrice());
                $priceHistory->setPrices($price);
                $em->persist($priceHistory);
                $em->flush();
                echo("<span style='color: green'>price for item '".$item_name."' created. price_id=".$price->getId()." price_history_id=".$priceHistory->getId()."<br></span>");
            }
            else
            {
                $price->setPrice( $row['Net Price'] );
                $price->setTax($tax);

                $priceHistory = $em->getRepository('HelloDiDiDistributorsBundle:PriceHistory')->findOneBy(array('Prices'=>$price));
                $priceHistory->setPrice($price->getPrice());
                echo("<span style='color: blue'>price for item '".$item_name."' already exist. price_id=".$price->getId().". Updated.<br></span>");
                $em->flush();
            }
        }
        $em->flush();
        return new Response("done");
    }

    public function addIMTUItemsDistToRetAction($RetId)
    {
        $array = $this->csv_to_array("uploads/temp/retailer.csv");
        if(!$array) die("<span style='color: red'>Unable to read file.</span>");

        $em = $this->getDoctrine()->getEntityManager();

        $tax = $em->getRepository('HelloDiDiDistributorsBundle:Tax')->findOneBy(array("Country"=>null));

        $accountRet = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($RetId);
        if (!$accountRet || $accountRet->getAccType() != 2) die("<span style='color: red'>Unable to find account.</span>");

        foreach($array as $row)
        {
            if($row["Fixed/ Floating"] != "Fixed") continue;
            $item_name = $row['CARRIER CODE']." ".$row['Country']." ".floatval($row['Fixed Top Up Value']).$row['Top Up Currency'];
            $itemcode = $row['COUNTRY CODE'].'/imtu/'.$row['CARRIER CODE'].'/'.str_replace(' ','_',$item_name);
            $item = $em->getRepository('HelloDiDiDistributorsBundle:Item')->findOneBy(array('itemCode'=>$itemcode));
            if(!$item)
            {
                echo("<span style='color:red'>item '".$item_name."' not found. price can't create.</span><br>");
                continue;
            }

            $price_dist = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Item'=>$item,'Account'=>$accountRet->getParent()));
            if(!$price_dist)
            {
                echo("<span style='color:red'>item '".$item_name."' for distributor not found. price can't create.</span><br>");
                continue;
            }

            $denomination = $em->getRepository('HelloDiDiDistributorsBundle:Denomination')->findOneBy(array('currency'=>'CHF','Item'=>$item));
            if(!$denomination)
            {
                echo("<span style='color:red'>item denomination for '".$item_name."' for CHF not found. price can't create.</span><br>");
                continue;
            }

            $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Item'=>$item,'Account'=>$accountRet));
            if(!$price)
            {
                $price = new Price();
                $price->setItem($item);
                $price->setAccount($accountRet);
                $price->setPrice( $row['Net Price'] );
                $price->setPriceCurrency($accountRet->getAccCurrency());
                $price->setPriceStatus(1);
                $price->setIsFavourite(0);
                $price->setTax($tax);
                $em->persist($price);

                $priceHistory = new PriceHistory();
                $priceHistory->setDate(new \DateTime('now'));
                $priceHistory->setPrice($price->getPrice());
                $priceHistory->setPrices($price);
                $em->persist($priceHistory);
                $em->flush();
                echo("<span style='color:green'>price for item '".$item_name."' created. price_id=".$price->getId()." price_history_id=".$priceHistory->getId()."</span><br>");
            }
            else
            {
                $price->setPrice( $row['Net Price'] );
                $price->setTax($tax);

                $priceHistory = $em->getRepository('HelloDiDiDistributorsBundle:PriceHistory')->findOneBy(array('Prices'=>$price));
                $priceHistory->setPrice($price->getPrice());
                echo("<span style='color:blue'>price for item '".$item_name."' already exist. price_id=".$price->getId().". Updated.</span><br>");
                $em->flush();
            }
        }
        $em->flush();
        return new Response("done");
    }
} 