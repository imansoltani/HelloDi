<?php

namespace HelloDi\AggregatorBundle\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\AccountingBundle\Container\TransactionContainer;
use HelloDi\AccountingBundle\Controller\DefaultController;
use HelloDi\AggregatorBundle\Entity\Provider;
use HelloDi\AggregatorBundle\Entity\TopUp;
use HelloDi\AggregatorBundle\Helper\SoapClientTimeout;
use HelloDi\CoreBundle\Entity\Item;
use HelloDi\CoreBundle\Entity\ItemDesc;
use HelloDi\CoreBundle\Entity\Operator;
use HelloDi\CoreBundle\Entity\User;
use HelloDi\DistributorBundle\Entity\Distributor;
use HelloDi\PricingBundle\Entity\Price;
use HelloDi\RetailerBundle\Entity\Retailer;
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
     * @var array
     */
    private $b2b_settings;

    /**
     * @var DefaultController
     */
    private $accounting;

    /**
     * constructor
     * @param EntityManager $em
     * @param DefaultController $accounting
     * @param array $b2b_setting
     */
    public function __construct(EntityManager $em, DefaultController $accounting, array $b2b_setting)
    {
        $this->em = $em;
        $this->accounting = $accounting;
        $this->b2b_settings = $b2b_setting;
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
            $item_name = $row[$col_num['CARRIER CODE']]." ".strtoupper($row[$col_num['COUNTRY CODE']])." ".$row[$col_num['TOP UP VALUE']].$row[$col_num['TOP UP CURRENCY']];
            // item_code = country_code/item_type/operator_name/item_name(_)
            $item_code = strtoupper($row[$col_num['COUNTRY CODE']]).'/imtu/'.$row[$col_num['CARRIER CODE']].'/'.str_replace(' ','_',$item_name);

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
                $new_item->setCountry($row[$col_num['COUNTRY CODE']]);
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

    public function updateReportB2BServer()
    {
        ini_set('max_execution_time', 80);

        //find provider
        /** @var Provider $provider */
        $provider = $this->em->createQueryBuilder()
            ->select('provider, account')
            ->from('HelloDiAggregatorBundle:Provider', 'provider')
            ->innerJoin('provider.account', 'account')
            ->where('account.name = :account_name')->setParameter('account_name', $this->b2b_settings['AccountName'])
            ->getQuery()->getSingleResult();

        //get range for log
        $firstTimeOver = $this->em->getRepository('HelloDiAggregatorBundle:TopUp')->findOneBy(array('status'=>TopUp::TIME_OVER),array('id'=>'asc'));
        $lastTimeOver = $this->em->getRepository('HelloDiAggregatorBundle:TopUp')->findOneBy(array('status'=>TopUp::TIME_OVER),array('id'=>'desc'));

        if($firstTimeOver == null || $lastTimeOver == null)
            throw new \Exception("There is no row with null status.");

        $from = clone $firstTimeOver->getDate();
        $from->modify('-1 day');

        $to = clone $lastTimeOver->getDate();
        $to->modify('+1 day');

        //sending request and get response
        try {
            $client = new SoapClientTimeout($this->b2b_settings['WSDL']);//,array('trace'=>true));
            $client->__setTimeout(60);
            $result = $client->__call('QueryAccount', array(
                    'Request' => array(
                        'UserInfo' => array(
                            'UserName'=>$this->b2b_settings['UserName'],
                            'Password'=>$this->b2b_settings['Password']
                        ),
                        'ClientReferenceData' => array(),
                        'Parameters' => array(
                            'ReturnBillingHistory' => 'Y',
                            'DateFrom' => date_format($from,'Y-m-d'),
                            'DateTo' => date_format($to,'Y-m-d')
                        ),
                    )
                ));

            $topup_s = $this->em->createQueryBuilder()
                ->select('topup, item, user')
                ->from('HelloDiAggregatorBundle:TopUp', 'topup')
                ->innerJoin('topup.item', 'item')
                ->innerJoin('topup.user', 'user')
                ->where('topup.status = :status')->setParameter('status', TopUp::TIME_OVER)
                ->getQuery()->getResult();

            $response = $result->QueryAccountResponse;
        }
        catch(\Exception $e) {
            throw new \Exception('Unable connect B2B server: '.$e->getMessage());
        }

        //if response returned and failed
        if($response->ResponseReferenceData->Success == 'N')
            throw new \Exception($this->convertErrorMessagesToString($response->ResponseReferenceData->MessageList));

        //data of request
        $report = $response->BillDataList->Data;

        //search all buying with 'null status'
        $row_num_viewed = 0;
        foreach($topup_s as $topup) {
            /** @var TopUp $topup */
            if (!$row_num_viewed = $this->findDatainB2BLog($topup->getClientTransactionID(),$report,$row_num_viewed))
                break;
            //if not found left null.

            //founded data
            $data = is_array($report)?$report[$row_num_viewed]:$report;

            $topup->setStatus($data->Description == "Success" ? TopUp::SUCCESS : TopUp::FAILED);
            $topup->setTransactionID('Update_Log');

            //collect needed data
            /** @var Retailer $retailer */
            $retailer = $this->em->createQueryBuilder()
                ->select('retailer', 'account')
                ->from('HelloDiRetailerBundle:Retailer', 'retailer')
                ->innerJoin('retailer.account', 'account')
                ->innerJoin('account.users', 'user')
                ->where('user = :user')->setParameter('user', $topup->getUser())
                ->getQuery()->getSingleResult();

            $distributor = $retailer->getDistributor();

            $priceProvider = $this->em->getRepository('HelloDiPricingBundle:Price')->findOneBy(array(
                    'item' => $topup->getItem(),
                    'account' => $provider->getAccount()
                ));

            $priceDistributor = $this->em->getRepository('HelloDiPricingBundle:Price')->findOneBy(array(
                    'item' => $topup->getItem(),
                    'account' => $distributor->getAccount()
                ));

            $priceRetailer = $this->em->getRepository('HelloDiPricingBundle:Price')->findOneBy(array(
                    'item' => $topup->getItem(),
                    'account' => $retailer->getAccount()
                ));

            $commission = $priceRetailer->getPrice() - $priceDistributor->getPrice();

            //if status of transaction is success update transaction else just release reserved amount.
            if($topup->getStatus() == TopUp::SUCCESS) {
                $result = $this->accounting->processTransaction(array(
                        new TransactionContainer(
                            $provider->getAccount(),
                            $priceProvider->getPrice(),
                            'provider b2b topup buy.'
                        ),
                        new TransactionContainer(
                            $distributor->getAccount(),
                            $commission,
                            'distributor b2b topup buy.'
                        ),
                        new TransactionContainer(
                            $retailer->getAccount(),
                            -$priceRetailer->getPrice(),
                            'retailer b2b topup buy.'
                        ),
                    ), false);

                if($result == false)
                    throw new \Exception("Retailer '".$retailer->getAccount()->getName()."' hasn't enough balance. (error in reserved Amount)");

                $topup->setProviderTransaction($result[0]);
                $topup->setCommissionerTransaction($result[1]);
                $topup->setSellerTransaction($result[2]);
            }

            $this->accounting->reserveAmount($priceRetailer->getPrice(), $retailer->getAccount(), false);
        }

        $this->em->flush();
    }

    private function findDataInB2BLog($MyClientId, $dataList, $i)
    {
        if (is_array($dataList)) {
            while ($dataList[$i]->BillTransactionID != $MyClientId) {
                $i++;
                if ($i == count($dataList))
                    return false;
            }
            return $i;
        } else {
            if ($dataList->BillTransactionID == $MyClientId)
                return 0;
            else
                return false;
        }
    }

    private function convertErrorMessagesToString($messages, $joiner = "<br>")
    {
        $result = "";
        if(isset($messages->StatusText))
            $result = $messages->StatusText;
        else
            foreach($messages as $message)
                $result .= $message->StatusText . $joiner;

        return $result;
    }

    private function CreateRandomClientTransactionId($user_id)
    {
        return "HD-".sprintf("%05s", $user_id).'-'.round(microtime(true));
    }

    /**
     * @param User $user
     * @param Item $item
     * @param string $senderMobileNumber
     * @param string $receiverMobileNumber
     * @param string $senderEmail
     * @return array
     */
    public function buyImtu(User $user, Item $item, $receiverMobileNumber, $senderMobileNumber, $senderEmail)
    {
        ini_set('max_execution_time', 80);

        //find accounts
        /** @var Retailer $retailer */
        $retailer = $this->em->createQueryBuilder()
            ->select('retailer', 'account')
            ->from('HelloDiRetailerBundle:Retailer', 'retailer')
            ->innerJoin('retailer.account', 'account')
            ->innerJoin('account.users', 'user')
            ->where('user = :user')->setParameter('user', $user)
            ->getQuery()->getSingleResult();

        if (!$retailer) {
            return array(0, null, 'Unable to find Retailer Account.');
        }

        /** @var Distributor $distributor */
        $distributor = $retailer->getDistributor();

        /** @var Provider $provider */
        $provider = $this->em->createQueryBuilder()
            ->select('provider, account')
            ->from('HelloDiAggregatorBundle:Provider', 'provider')
            ->innerJoin('provider.account', 'account')
            ->where('account.name = :account_name')->setParameter('account_name', $this->b2b_settings['AccountName'])
            ->getQuery()->getSingleResult();

        if (!$provider) {
            return array(0, null, 'Unable to find Provider Account.');
        }

        //find prices
        $priceProvider = $this->em->getRepository('HelloDiPricingBundle:Price')->findOneBy(
            array(
                'item' => $item,
                'account' => $provider->getAccount()
            )
        );

        $priceDistributor = $this->em->getRepository('HelloDiPricingBundle:Price')->findOneBy(
            array(
                'item' => $item,
                'account' => $distributor->getAccount()
            )
        );

        $priceRetailer = $this->em->getRepository('HelloDiPricingBundle:Price')->findOneBy(
            array(
                'item' => $item,
                'account' => $retailer->getAccount()
            )
        );

        if (!$priceProvider || !$priceDistributor || !$priceRetailer) {
            return array(0, null, 'Unable to find Prices for Accounts.');
        }

        $commission = $priceRetailer->getPrice() - $priceDistributor->getPrice();

        $clientTransactionId = $this->CreateRandomClientTransactionId($user->getId());

        if (!$this->accounting->reserveAmount($priceRetailer->getPrice(), $retailer->getAccount(), true)) {
            return array(0, null, "Retailer hasn't enough balance.");
        }

        $topUp = new TopUp();
        $topUp->setAmount($priceProvider->getDenomination());
        $topUp->setClientTransactionID($clientTransactionId);
        $topUp->setDate(new \DateTime());
        $topUp->setItem($item);
        $topUp->setMobileNumber($receiverMobileNumber);
        $topUp->setSenderEmail($senderEmail);
        $topUp->setSenderMobileNumber($senderMobileNumber);
        $topUp->setUser($user);
        $topUp->setStatus(TopUp::PENDING);
        $this->em->persist($topUp);
        $this->em->flush();

        try {
            $client = new SoapClientTimeout($this->b2b_settings['WSDL']);//,array('trace'=>true));
            $client->__setTimeout(60);
            $result = $client->__soapCall("CreateAccount",
                array(
                    'CreateAccountRequest' => array(
                        'UserInfo' => array(
                            'UserName' => $this->b2b_settings['UserName'],
                            'Password' => $this->b2b_settings['Password']
                        ),
                        'ClientReferenceData' => array(
                            'Service' => 'imtu',
                            'ClientTransactionID' => $clientTransactionId,
                            'IP' => $this->b2b_settings['IP'],
                            'TimeStamp' => date_format(new \DateTime(), DATE_ATOM)
                        ),
                        'Parameters' => array(
                            'CarrierCode' => $item->getOperator()->getName(),
                            'CountryCode' => $item->getCountry(),
                            'Amount' => $priceProvider->getDenomination() * 100,
                            'MobileNumber' => $receiverMobileNumber,
                            'StoreID' => $this->b2b_settings['StoreID'],
                            'ChargeType' => 'transfer',
                            'Recharge' => 'Y',
                            'SendSMS' => ($senderMobileNumber != "" ? "Y" : "N"),
                            'SenderNumber' => $senderMobileNumber,
                            'NotificationMobile' => $senderMobileNumber,
                            'SendEmail' => ($senderEmail != "" ? "Y" : "N"),
                            'NotificationEmail' => $senderEmail,
                        ),
                    )
                )
            );

            $CreateAccountResponse = $result->CreateAccountResponse;
            $topUp->setTransactionID($CreateAccountResponse->ResponseReferenceData->TransactionID);

            if ($CreateAccountResponse->ResponseReferenceData->Success == 'N') {
                $topUp->setStatus(TopUp::FAILED);
                $messages = $CreateAccountResponse->ResponseReferenceData->MessageList;
                $error_codes = "";
                $error_texts = "";
                foreach ($messages as $message) {
                    $error_codes .= $message->StatusCode . ',';
                    $error_texts .= $message->StatusText . ',';
                }
                $topUp->setStatusCode($error_codes);

                $this->em->flush();
//                    $s  = "request: ".$client->__getLastRequest() . "<br/>";
//                    $s .= "response: ".$client->__getLastResponse() . "<br/>";
//                    die($s);
                return array(-1, $topUp->getId(), $error_texts);
            } else {
                $topUp->setStatus(TopUp::SUCCESS);

                $result = $this->accounting->processTransaction(array(
                        new TransactionContainer(
                            $provider->getAccount(),
                            $priceProvider->getPrice(),
                            'provider b2b topup buy.'
                        ),
                        new TransactionContainer(
                            $distributor->getAccount(),
                            $commission,
                            'distributor b2b topup buy.'
                        ),
                        new TransactionContainer(
                            $retailer->getAccount(),
                            -$priceRetailer->getPrice(),
                            'retailer b2b topup buy.'
                        ),
                    ), false);

                if($result == false)
                    return array(-2, $topUp->getId(), "Retailer '".$retailer->getAccount()->getName()."' hasn't enough balance. (error in reserved Amount)");

                $topUp->setProviderTransaction($result[0]);
                $topUp->setCommissionerTransaction($result[1]);
                $topUp->setSellerTransaction($result[2]);

                $this->em->flush();
//                    $s  = "request: ".$client->__getLastRequest() . "<br/>";
//                    $s .= "response: ".$client->__getLastResponse() . "<br/>";
//                    die($s);
                return array(1, $topUp->getId(), null);
            }

        } catch (\Exception $e) {
//            die("---".$e->getMessage()."---");
//            if ($e->getCode() == -99) {
                $topUp->setStatus(TopUp::TIME_OVER);
                $this->em->flush();

//                return array(-3, $topUp->getId(), 'server_no_response');
//                $this->get('session')->getFlashBag()->add(
//                    'error',
//                    $this->get('translator')->trans('server_no_response', array(), 'message')
//                );
//            } else {
                return array(-3, $topUp->getId(), "Server Error: ".$e->getMessage());
//                $this->get('session')->getFlashBag()->add(
//                    'error',
//                    $this->get('translator')->trans('error_b2b', array(), 'message')
//                );
//            }
        }
    }
}
