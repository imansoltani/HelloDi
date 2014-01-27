<?php
namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use HelloDi\DiDistributorsBundle\Entity\Account;
use HelloDi\DiDistributorsBundle\Entity\Item;
use HelloDi\DiDistributorsBundle\Entity\ItemDesc;
use HelloDi\DiDistributorsBundle\Entity\Operator;
use HelloDi\DiDistributorsBundle\Entity\Price;
use HelloDi\DiDistributorsBundle\Entity\PriceHistory;
use Symfony\Bundle\FrameworkBundle\Console\Application;
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
            Transaction No.: --<br>
            Receiver phone number: --<br>
            Operator: {{operator}}<br>
            Value sent: --<br>
            Value paid: --<br>
            Entity Name: {{entityname}}<br>
            Address: <br>
            {{entityadrs1}}<br>
            {{entityadrs2}}<br>
            {{entityadrs3}}<br>",
        "fr"=>"<b>Reçu</b><br>
            Date: {{printdate}}<br>
            Numéro transaction: --<br>
            Numéro de téléphone (receveur): --<br>
            Opérateur: {{operator}}<br>
            Valeur envoyée (monnaie locale): --<br>
            Montant payé: --<br>
            Nom de l'entité: {{entityname}}<br>
            Adresse: <br>
            {{entityadrs1}}<br>
            {{entityadrs2}}<br>
            {{entityadrs3}}<br>"
    );

    public function updateDataBaseAction()
    {
        $application = new Application($this->get('kernel'));
        $application->setAutoExit(false);

        $input = new ArrayInput(array(
            'command' => 'doctrine:schema:update',
            '--force'  => true,
        ));

        $retval = $application->run($input);
        return new Response("status: ".$retval);
    }

    public function itemAddAction()
    {
        //for update db
        $this->updateDataBaseAction();

        $array = $this->csv_to_array("uploads/temp/CH IMTU Item List 100114 (updated).csv");
        //return new Response(str_replace(array("\n"," "),array("<br/>","&nbsp;"),print_r($array,true)));

        $em = $this->getDoctrine()->getManager();
        $provider = $em->getRepository('HelloDiDiDistributorsBundle:Account')->findOneBy(array('accName'=>'B2Bserver'));
        if(!$provider)
            die("provider not found.");
        $provider->setAccCurrency("CHF");
        $em->flush();

        foreach($array as $key=>$row)
        {
            if($row["Fixed/ Floating"] == "Fixed") $this->insertItem($provider,$key,$row,$em);
        }

        return new Response("done");
    }

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
                if(!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        return $data;
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
            echo("operator '".$row['CARRIER CODE']."' created. operator_id=".$operator->getId()."<br>");
        }
        else
            echo("operator '".$row['CARRIER CODE']."' already exist. operator_id=".$operator->getId()."<br>");

        //----------------set logo operator if not exist-----------------
        if($operator->getLogo() == null)
        {
            $source_path = "uploads/temp/Operator Logos/".$row['COUNTRY CODE']."/".$row['CARRIER CODE']."/".
                strtolower($row['COUNTRY CODE']."_".$row['CARRIER CODE']).".png";
            if(!file_exists($source_path))
                echo("++ can't find Logo file ".$source_path."<br>");
            elseif (!copy($source_path,"uploads/logos/".$operator->getId().".png"))
                echo("++ can't copy Logo file ".$source_path."<br>");
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
            echo("item '".$item_name."' created. item_id=".$item->getId()."<br>");
        }
        else
            echo("item '".$item_name."' already exist. item_id=".$item->getId()."<br>");

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

        echo("item_descs for item '".$item_name."' created. item_id=".$item->getId()."<br>");

        //----------------find or create price-----------------
        $price = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Item'=>$item,'Account'=>$provider));
        if(!$price)
        {
            $price = new Price();
            $price->setItem($item);
            $price->setAccount($provider);
            $price->setPrice( $row['DENOMINATION (CHF)']- (intval($row['Discount'])/100*$row['DENOMINATION (CHF)']) );
            $price->setPriceCurrency($provider->getAccCurrency());
            $price->setDenomination($row['DENOMINATION (CHF)']);
            $price->setPriceStatus(1);
            $price->setIsFavourite(0);
            $em->persist($price);

            $priceHistory = new PriceHistory();
            $priceHistory->setDate(new \DateTime('now'));
            $priceHistory->setPrice($price->getPrice());
            $priceHistory->setPrices($price);
            $em->persist($priceHistory);
            $em->flush();
            echo("price for item '".$item_name."' created. price_id=".$price->getId()." price_history_id=".$priceHistory->getId()."<br>");
        }
        else
        {
            $price->setPrice( $row['DENOMINATION (CHF)']- (intval($row['Discount'])/100*$row['DENOMINATION (CHF)']) );
            $price->setDenomination($row['DENOMINATION (CHF)']);

            $priceHistory = $em->getRepository('HelloDiDiDistributorsBundle:PriceHistory')->findOneBy(array('Prices'=>$price));
            $priceHistory->setPrice($price->getPrice());
            echo("price for item '".$item_name."' already exist. price_id=".$price->getId().". Updated.<br>");
            $em->flush();
        }
    }

    public function addIMTUItemsToDistAction($distId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $tax = $em->getRepository('HelloDiDiDistributorsBundle:Tax')->findOneBy(array("Country"=>null));
        $accountDist = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($distId);
        if (!$accountDist || $accountDist->getAccType() != 0) {
            throw $this->createNotFoundException("Unable to find account.");
        }

        $PricesOfProv = $em->createQueryBuilder()
            ->select("price")
            ->from("HelloDiDiDistributorsBundle:Price","price")
            ->innerJoin("price.Account","account")
            ->where("account.accName = :accName")->setParameter("accName","B2Bserver")
            ->getQuery()->getResult();

        $Country_EC = $em->getRepository('HelloDiDiDistributorsBundle:Country')->findOneBy(array("iso"=>"EC"));
        $Country_RU = $em->getRepository('HelloDiDiDistributorsBundle:Country')->findOneBy(array("iso"=>"RU"));

        foreach($PricesOfProv as $price_prov)
        {
            $price_dist = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array(
                "Account"=>$accountDist,
                "Item"=>$price_prov->getItem()
            ));

            if(!$price_dist)
            {
                $price_dist = new Price();
                $price_dist->setDenomination($price_prov->getDenomination());
                $price_dist->setAccount($accountDist);
                $price_dist->setItem($price_prov->getItem());

                $discount = ($price_prov->getItem()->getCountry() == $Country_EC || $price_prov->getItem()->getCountry() == $Country_RU)?
                    0.07:0.09;


                $price_dist->setPrice( $price_prov->getDenomination()- ($discount*$price_prov->getDenomination()) );

                $price_dist->setPriceCurrency($accountDist->getAccCurrency());
                $price_dist->setPriceStatus(1);
                $price_dist->setIsFavourite(0);
                $price_dist->setTax($tax);
                $em->persist($price_dist);

                $priceHistory = new PriceHistory();
                $priceHistory->setDate(new \DateTime('now'));
                $priceHistory->setPrice($price_dist->getPrice());
                $priceHistory->setPrices($price_dist);
                $em->persist($priceHistory);
                echo("price for item '".$price_prov->getItem()."' created.<br>");
            }
            else
            {
                $discount = ($price_prov->getItem()->getCountry() == $Country_EC || $price_prov->getItem()->getCountry() == $Country_RU)?
                    0.07:0.09;
                $price_dist->setPrice( $price_prov->getDenomination() - ($discount*$price_prov->getDenomination()) );

                $priceHistory = $em->getRepository('HelloDiDiDistributorsBundle:PriceHistory')->findOneBy(array('Prices'=>$price_dist));
                $priceHistory->setPrice($price_dist->getPrice());
                echo("price for item '".$price_dist->getItem()."' already exist. Price = '".$price_dist->getPrice()." . Updated.'<br>");
            }
        }
        $em->flush();
        return new Response("done");
    }

    public function addIMTUItemsDistToRetAction($distId,$RetId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $tax = $em->getRepository('HelloDiDiDistributorsBundle:Tax')->findOneBy(array("Country"=>null));
        $accountDist = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($distId);
        if (!$accountDist || $accountDist->getAccType() != 0) {
            throw $this->createNotFoundException("Unable to find account dist.");
        }
        $accountRet = $em->getRepository('HelloDiDiDistributorsBundle:Account')->find($RetId);
        if (!$accountRet || $accountRet->getAccType() != 2) {
            throw $this->createNotFoundException("Unable to find account ret.");
        }

        foreach($accountDist->getPrices() as $price_dist)
        {
            $price_ret = $em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array(
                "Account"=>$accountRet,
                "Item"=>$price_dist->getItem()
            ));

            if(!$price_ret)
            {
                $price_ret = new Price();
                $price_ret->setDenomination($price_dist->getDenomination());
                $price_ret->setAccount($accountRet);
                $price_ret->setItem($price_dist->getItem());
                $price_ret->setPrice($price_dist->getPrice());
                $price_ret->setPriceCurrency($accountRet->getAccCurrency());
                $price_ret->setPriceStatus(1);
                $price_ret->setIsFavourite(0);
                $price_ret->setTax($tax);
                $em->persist($price_ret);

                $priceHistory = new PriceHistory();
                $priceHistory->setDate(new \DateTime('now'));
                $priceHistory->setPrice($price_ret->getPrice());
                $priceHistory->setPrices($price_ret);
                $em->persist($priceHistory);
                echo("price for item '".$price_dist->getItem()."' created.<br>");
            }
            else
            {
                $price_ret->setPrice($price_dist->getPrice());
                $priceHistory = $em->getRepository('HelloDiDiDistributorsBundle:PriceHistory')->findOneBy(array('Prices'=>$price_ret));
                $priceHistory->setPrice($price_ret->getPrice());
                echo("price for item '".$price_ret->getItem()."' already exist. Price = '".$price_ret->getPrice()." . Updated.'<br>");
            }
        }
        $em->flush();
        return new Response("done");
    }
} 