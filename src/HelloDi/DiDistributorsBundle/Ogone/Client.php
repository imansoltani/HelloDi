<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Fils du Soleil
 * Date: 26.06.13
 * Time: 18:21
 * To change this template use File | Settings | File Templates.
 */

namespace HelloDi\DiDistributorsBundle\Ogone;

use Doctrine\Common\Collections\ArrayCollection;
use HelloDi\DiDistributorsBundle\Entity\OgonePayment;
use HelloDi\DiDistributorsBundle\Entity\Transaction;
use HelloDi\DiDistributorsBundle\Entity\User;
use HelloDi\DiDistributorsBundle\Exception\OgoneException;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Validator\Constraints\DateTime;
use HelloDi\DiDistributorsBundle\Ogone\RoutesContainer;


class Client
{
    private $em;
    //private $routePrefix;


    private $pspId;
    private $shaIn;
    private $shaOut;
    private $submitUrl;
    private $resultUrl;
    private $catalogUrl;
    private $homeUrl;
    private $ogoneTemplateUrl;

    public function __construct(Router $router, EntityManager $em, $pspId, $shaIn, $shaOut, $submitUrl,RoutesContainer $routesContainer)
    {
        $this->em           = $em;
        $this->pspId        = $pspId;
        $this->shaIn        = $shaIn;
        $this->shaOut       = $shaOut;
        $this->submitUrl    = $submitUrl;

        //generate(string $name, mixed $parameters = array(), Boolean $absolute = false)
        $this->resultUrl    = $router->generate($routesContainer->getResultUrl(), [], true);
        $this->catalogUrl   = $router->generate($routesContainer->getCatalogUrl(), [], true);
        $this->homeUrl  = $router->generate($routesContainer->getHomeUrl(), [], true);
        $this->ogoneTemplateUrl   = $router->generate($routesContainer->getOgoneTemplateUrl(), [], true);
    }

    private function getSortedParameters(OgonePayment $payment)
    {


        return array(
            'ACCEPTURL'     => $this->resultUrl,
            'AMOUNT'        => $payment->getOgoneAmount(),
            'CANCELURL'     => $this->resultUrl,
            'CATALOGURL'    => $this->catalogUrl,
            'CURRENCY'      => $payment->getPaymentCurrencyISO(),
            'DECLINEURL'    => $this->resultUrl,
            'EXCEPTIONURL'  => $this->resultUrl,
            'HOMEURL'       => $this->homeUrl,
            'LANGUAGE'      => sprintf('%s_%s',$payment->getUser()->getLanguage(),$payment->getUser()->getEntiti()->getCountry()->getIso()),
            'ORDERID'       => $payment->getOrderReference(),
            'PSPID'         => $this->pspId,
            'TP'            => $this->ogoneTemplateUrl
        );
    }

    private function generateHashIn(OgonePayment $payment)
    {


        $fields = [];
        foreach ($this->getSortedParameters($payment) as $fieldName => $fieldValue)
        {
            $fields[] = sprintf('%s=%s', $fieldName, $fieldValue);
        }
        $fields[] = '';
        return sha1(implode($this->shaIn, $fields));
    }

  public function processResult(User $user, $fields)
    {
        if (false === $this->checkHashOut($fields))
        {
            throw new OgoneException('Invalid Ogone datas');
        }

        $ogonePayment = $this->em->getRepository('HelloDiDiDistributorsBundle:OgonePayment')->findOneByOrderReference($fields['orderID']);


        if (null === $ogonePayment)
        {
            throw new OgoneException('Invalid payment');
        }

        if ($ogonePayment->isProcessed())
        {
            return $ogonePayment;
        }

        $ogonePayment->setOgoneRef($fields['PAYID']);

        switch ($fields['STATUS'])
        {
            case OgonePayment::OGONE_RESULT_ACCPETED:

                $transaction=new Transaction();
                $transaction->setTranBookingValue(null);
                $transaction->setTranAmount($ogonePayment->getPaymentAmount());
                $transaction->setTranCurrency($ogonePayment->getPaymentCurrencyISO());
                $transaction->setAccount($ogonePayment->getUser()->getAccount());
                $transaction->setTranAction('ogn_pmt');
                $transaction->setTranDate($ogonePayment->getCreatedAt());
                $transaction->setTranInsert(new \DateTime('now'));
                $transaction->setTranType(1);
                $transaction->setTranBalance($ogonePayment->getUser()->getAccount()->getAccBalance());
                $transaction->setUser($ogonePayment->getUser());
                $transaction->setTranDescription('ogone payment');
                $transaction->setTranFees(0);
                $ogonePayment->setTransaction($transaction);
                $ogonePayment->setStatus(OgonePayment::STATUS_ACCEPTED);

               $this->em->persist($transaction);

                break;

            case OgonePayment::OGONE_RESULT_DECLINED:

                $ogonePayment->setStatus(OgonePayment::STATUS_DECLINED);

                break;

            case OgonePayment::OGONE_RESULT_CANCELED:
                $ogonePayment->setStatus(OgonePayment::STATUS_CANCELED);

                break;

            case OgonePayment::OGONE_RESULT_EXCEPTION:
                $ogonePayment->setStatus(OgonePayment::STATUS_UNCERTAIN);

                break;

            default:
                $ogonePayment->setStatus(OgonePayment::STATUS_UNKNOWN);

        }

      $this->em->flush();

      return $ogonePayment;

    }

    private function checkHashOut(array $fields)
    {
        $datas = array_change_key_case($fields, CASE_UPPER);

        foreach (['ORDERID', 'PAYID', 'SHASIGN', 'STATUS'] as $fieldName)
        {
            if (!isset($datas[$fieldName])){return false;}
        }

        $ogoneDigestSign = $datas['SHASIGN'];
        unset($datas['SHASIGN']);
        ksort($datas);

        $hashedString = '';

        foreach ($datas as $fieldName => $fieldValue)
        {
            if($fieldValue !== '')
            {
                $hashedString .= sprintf('%s=%s%s', $fieldName, $fieldValue, $this->shaOut);
            }
        }

        return $ogoneDigestSign === strtoupper(sha1($hashedString));
    }

    public function generateForm(OgonePayment $payment)
    {



        $fields[] = sprintf('<form id="form1" name="form1" method="post" action="%s">', $this->submitUrl);

        foreach ($this->getSortedParameters($payment) as $name => $value)
        {
            $fields[] = sprintf('<input  type="hidden" name="%s" value="%s" />', $name, $value);
        }

        $fields[] = sprintf('<input  type="hidden" name="SHASIGN" value="%s" />', $this->generateHashIn($payment));
        $fields[] ='<input  class="button btn btn-primary btn-large"  type="submit"  value="Accept">';
        $fields[] = '</form>';

        return    implode('', $fields);

    }

}