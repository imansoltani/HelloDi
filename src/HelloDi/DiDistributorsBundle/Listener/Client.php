<?php

namespace HelloDi\DiDistributorsBundle\Listener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use HelloDi\DiDistributorsBundle\Entity\OgonePayment;
use HelloDi\DiDistributorsBundle\Entity\Transaction;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\Constraints\True;

class Client
{
    private $em;
    private $routePrefix;

    private $user;
    private $account;
    private $locale;

    private $pspId;
    private $shaIn;
    private $shaOut;
    private $submitUrl;

    private $resultUrl;
    private $catalogUrl;
    private $homeUrl;
    private $ogoneTemplateUrl;

    public function __construct( EntityManager $em,Router $router, $pspId, $shaIn, $shaOut, $submitUrl)
    {
        $this->em           = $em;
        $this->pspId        = $pspId;
        $this->shaIn        = $shaIn;
        $this->shaout       = $shaOut;
        $this->submitUrl    = $submitUrl;

        //generate(string $name, mixed $parameters = array(), Boolean $absolute = false)
        $this->resultUrl    = $router->generate('retailer_OgoneTransactions_Accept', [], true);
//        $this->catalogUrl   = $router->generate('hellodi_transactions_new', [], true);
//        $this->homeUrl  = $router->generate('hellodi_index', [], true);
//        $this->ogoneTemplateUrl   = $router->generate('', [], true);
    }

    private  function getSortedParameters(OgonePayment $payment)
    {

     return [
            'ACCEPTURL'     => $this->resultUrl,
            'AMOUNT'        => $payment->getOgoneAmount(),
            'CANCELURL'     => $this->resultUrl,
//            'CATALOGURL'    => $this->catalogUrl,
            'CURRENCY'      => $payment->getPaymentCurrencyISO(),
//            'DECLINEURL'    => $this->resultUrl,
//            'EXCEPTIONURL'  => $this->resultUrl,
//            'HOMEURL'       => $this->homeUrl,
            'LANGUAGE'      => $payment->getUser()->getLanguage(),
            'ORDERID'       => $payment->getOrderReference(),
            'PSPID'         => $this->pspId
//            'TP'            => $this->ogoneTemplateUrl,
        ];


    }
    private function generateHashIn(OgonePayment $payment){
        $fields[] = '' ;
        foreach ($this->getSortedParameters($payment) as $fieldName => $fieldValue)
        {
            $fields[] = sprintf('%s=%s', $fieldName, $fieldValue);
        }

        array_shift($fields);
        array_push($fields,'');
        return sha1(implode($this->shaIn,$fields));

    }

    private function generateHashOut($parametr,$sin){

        $id = substr($parametr['ORDERID'],10);
        $OgonePayment = $this->em->getRepository('HelloDiDiDistributorsBundle:OgonePayment')->find($id);

        print '<br>';

        if($OgonePayment->getStatus()!= OgonePayment::STATUS_PENDING){
            return false;
        }

        $fields[] = '' ;
        foreach ($parametr as $fieldName => $fieldValue)
        {
            $fields[] = sprintf('%s=%s', $fieldName, $fieldValue);
        }
        array_shift($fields);
        array_push($fields,'');
        if(strtoupper(sha1(implode($this->shaout,$fields))) == $sin)
        {
            return true ;
        }
        else{
            return false ;
        }

    }

    public function ProcessRequest(OgonePayment $payment){
          return $this->generateHashIn($payment);

    }

    public function getValues(OgonePayment $payment){
        return $this->getSortedParameters($payment);
    }

    public function ProcessBack($parametr,$sin){

        if($this->generateHashOut($parametr,$sin)){

            if($parametr['STATUS']==9){

            $id = substr($parametr['ORDERID'],10);
            $OgonePayment = $this->em->getRepository('HelloDiDiDistributorsBundle:OgonePayment')->find($id);
            $user = $OgonePayment->getUser();
            $account = $OgonePayment->getUser()->getAccount();
            $accBalance = $account->getAccBalance();
            $OgonePayment->setStatus(OgonePayment::STATUS_ACCEPTED);
            $this->em->persist($OgonePayment);
            $this->em->flush();

            $transaction = new Transaction();
            $transaction->setUser($user);
            $transaction->setAccount($account);
            $transaction->setTranCurrency($parametr['CURRENCY']);
            $transaction->setTranAmount($parametr['AMOUNT']);
            $transaction->setTranAction('OgonePayment');
            $transaction->setTranDate(new \DateTime());
            $transaction->setTranFees(0);
            $this->em->persist($transaction);
            $this->em->flush();

            $accSum = $accBalance + $parametr['AMOUNT'];
            $account->setAccBalance($accSum);
            $this->em->persist($account);
            $this->em->flush();

            }
        }

    }

}
