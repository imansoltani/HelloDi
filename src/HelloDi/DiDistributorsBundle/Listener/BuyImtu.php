<?php
namespace HelloDi\DiDistributorsBundle\Listener;


use Doctrine\ORM\EntityManager;
use HelloDi\DiDistributorsBundle\Helper\SoapClientTimeout;
use Symfony\Component\Security\Core\SecurityContext;

class BuyImtu {

    private $em;
    private $receiverMobileNumber;
    private $user;
    private $provider;
    private $accountRet;
    private $priceRet;
    private $priceDist;
    private $priceProv;

    public function __construct(EntityManager $em, SecurityContext $securityContext)
    {
        $this->em = $em;
        $this->user = $securityContext->getToken()->getUser();
        ini_set('max_execution_time', 60);
        $this->provider = $em->getRepository('HelloDiAccountingBundle:Account')->findOneBy(array('accName'=>'B2Bserver'));
        $this->accountRet = $this->user->getAccount();
    }

    public function Buy($receiverMobileNumber , $item)
    {
//        $this->receiverMobileNumber = $receiverMobileNumber;
//
//        $this->priceRet = $this->em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Item'=>$item,'Account'=>$this->accountRet));
//        $this->priceDist = $this->em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Item'=>$item,'Account'=>$this->accountRet->getParent()));
//        $this->priceProv = $this->em->getRepository('HelloDiDiDistributorsBundle:Price')->findOneBy(array('Item'=>$item,'Account'=>$this->provider));
//        $clientTranId= $this->CreateTranId();
//        $taxhistory = $this->em->getRepository('HelloDiDiDistributorsBundle:TaxHistory')->findOneBy(array('Tax'=>$this->priceDist->getTax(),'taxend'=>null));
//        $com = $this->priceRet->getprice() - $this->priceDist->getprice();
//
//        try
//        {
//            $client = new SoapClientTimeout($this->container->getParameter('B2BServer.WSDL'));
//            $client->__setTimeout(40);
//            $result0 = $client->CreateAccount(array(
//                    'CreateAccountRequest' => array(
//                        'UserInfo' => array(
//                            'UserName'=>$this->container->getParameter('B2BServer.UserName'),
//                            'Password'=>$this->container->getParameter('B2BServer.Password')
//                        ),
//                        'ClientReferenceData' => array(
//                            'Service'=>'imtu',
//                            'ClientTransactionID'=>$clientTranId,
//                            'IP'=>$this->container->getParameter('B2BServer.IP'),
//                            'TimeStamp'=>  date_format(new \DateTime(),DATE_ATOM)
//                        ),
//                        'Parameters' => array(
//                            'CarrierCode'=>$item->getOperator()->getName(),
//                            'CountryCode'=>$item->getCountry()->getIso(),
//                            'Amount'=>$priceProv->getDenomination(),
//                            'MobileNumber'=>$mobileNumber,
//                            'StoreID'=>$this->container->getParameter('B2BServer.StoreID'),
//                            'ChargeType'=>'transfer',
//                            'Recharge'=>'N',
//                            'SendSMS'=>'N',
//                            'SendEmail'=>'N',
//                        ),
//                    )
//                ));
//
//            if($result0->CreateAccountResponse->ResponseReferenceData->Success == 'N')
//            {
//                $messages = $result0->CreateAccountResponse->ResponseReferenceData->MessageList;
//                foreach ($messages as $message)
//                    $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans($message->StatusCode,array(),'message'));
////
////                $s = "request:<br/>".$client->__getLastRequest()."<br/>response:<br/>".$client->__getLastResponse();
////                die("part1 has error.<br/>".$s);
//            }
//            else
//            {
////                $s = "request:<br/>".$client->__getLastRequest()."<br/>response:<br/>".$client->__getLastResponse();
////                die("part1 hasn't error.<br/>".$s);
//                $serviceNumber = $result0->CreateAccountResponse->Result->ServiceNumber;
//                $b2blog = new B2BLog();
//                $b2blog->setUser($user);
//                $b2blog->setAmount($priceProv->getDenomination());
//                $b2blog->setClientTransactionID($clientTranId);
//                $b2blog->setDate(new \DateTime());
//                $b2blog->setMobileNumber($mobileNumber);
//                $b2blog->setItem($item);
//                $em->persist($b2blog);
//                $em->flush();
//
//                $result = $client->Recharge(array(
//                        'RechargeRequest' => array(
//                            'UserInfo' => array(
//                                'UserName'=>$this->container->getParameter('B2BServer.UserName'),
//                                'Password'=>$this->container->getParameter('B2BServer.Password')
//                            ),
//                            'ClientReferenceData' => array(
//                                'Service'=>'imtu',
//                                'ClientTransactionID'=>$clientTranId,
//                                'IP'=>$this->container->getParameter('B2BServer.IP'),
//                                'TimeStamp'=>  date_format(new \DateTime(),DATE_ATOM)
//                            ),
//                            'Parameters' => array(
//                                'CarrierCode'=>$item->getOperator()->getName(),
//                                'CountryCode'=>$item->getCountry()->getIso(),
//                                'Amount'=>$priceProv->getDenomination(),
//                                'MobileNumber'=>$mobileNumber,
//                                'StoreID'=>$this->container->getParameter('B2BServer.StoreID'),
//                                'ChargeType'=>'transfer',
//                                'SendSMS'=>'N',
//                                'SendEmail'=>'N',
//                                'ServiceNumber'=>$serviceNumber
//                            ),
//                        )
//                    ));
//
//                $RechargeResponse = $result->RechargeResponse;
//
//                if($RechargeResponse->ResponseReferenceData->Success == 'N')
//                {
//                    $b2blog->setTransactionID($RechargeResponse->ResponseReferenceData->TransactionID);
//                    $b2blog->setStatus(0);
//                    $messages = $RechargeResponse->ResponseReferenceData->MessageList;
//                    $error_codes = "";
//                    foreach ($messages as $message)
//                    {
//                        $error_codes.= $message->StatusCode.',';
//                        $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans($message->StatusCode,array(),'message'));
//                    }
//                    $b2blog->setStatusCode($error_codes);
//                }
//                else
//                {
//                    $b2blog->setTransactionID($RechargeResponse->ResponseReferenceData->TransactionID);
//                    $b2blog->setStatus(1);
//
//                    // For retailers
//                    $tranretailer = new Transaction();
//                    $tranretailer->setAccount($accountRet);
//                    $tranretailer->setTranAmount(-($priceRet->getPrice()));
//                    $tranretailer->setTranFees(0);
//                    $tranretailer->setTranDescription('ClientTransactionID: ' . $clientTranId);
//                    $tranretailer->setTranCurrency($accountRet->getAccCurrency());
//                    $tranretailer->setTranDate(new \DateTime('now'));
//                    $tranretailer->setTranInsert(new \DateTime('now'));
//                    $tranretailer->setTranAction('sale');
//                    $tranretailer->setTranType(0);
//                    $tranretailer->setUser($user);
//                    $tranretailer->setTranBookingValue(null);
//                    $tranretailer->setTranBalance($accountRet->getAccBalance());
//                    $tranretailer->setTaxHistory($taxhistory);
//                    $tranretailer->setB2BLog($b2blog);
//                    $b2blog->addTransaction($tranretailer);
//                    $em->persist($tranretailer);
//
//                    // For distributors
//                    $trandist = new Transaction();
//                    $trandist->setAccount($accountRet->getParent());
//                    $trandist->setTranAmount($com);
//                    $trandist->setTranFees(0);
//                    $trandist->setTranDescription('ClientTransactionID: ' . $clientTranId);
//                    $trandist->setTranCurrency($accountRet->getParent()->getAccCurrency());
//                    $trandist->setTranDate(new \DateTime('now'));
//                    $trandist->setTranInsert(new \DateTime('now'));
//                    $trandist->setTranAction('com');
//                    $trandist->setTranType(1);
//                    $trandist->setUser($user);
//                    $trandist->setTranBookingValue(null);
//                    $trandist->setTranBalance($accountRet->getParent()->getAccBalance());
//                    $trandist->setTaxHistory($taxhistory);
//                    $trandist->setBuyingprice($priceDist->getPrice());
//                    $trandist->setB2BLog($b2blog);
//                    $b2blog->addTransaction($trandist);
//                    $em->persist($trandist);
//
//                    $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('mobile_number_%mobilenumber%_charged',array('mobilenumber'=>$mobileNumber),'message'));
//                }
//                $em->flush();
//
//                if($accountRet->getAccBalance()+$accountRet->getAccCreditLimit()<=15000)
//                    $this->forward('hello_di_di_notification:NewAction',array('id'=>$accountRet->getId(),'type'=>31,'value'=>'15000 ' .$accountRet->getAccCurrency()));
//            }
//        }
//        catch (\Exception $e)
//        {}
    }

    private function CreateTranId()
    {
        return "HD-".sprintf("%05s",$this->user->getId()).'-'.(new \DateTime())->getTimestamp();
    }
} 