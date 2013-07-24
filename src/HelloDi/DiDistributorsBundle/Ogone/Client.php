<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Fils du Soleil
 * Date: 26.06.13
 * Time: 18:21
 * To change this template use File | Settings | File Templates.
 */

namespace HelloDi\DiDistributorsBundle\Ogone;

use HelloDi\DiDistributorsBundle\Entity\OgonePayment;
use HelloDi\DiDistributorsBundle\Entity\Transaction;
use HelloDi\DiDistributorsBundle\Entity\User;
use HelloDi\DiDistributorsBundle\Exception\OgoneException;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;


class Client
{
    private $em;
    //private $routePrefix;

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

    public function __construct(Router $router, EntityManager $em, $pspId, $shaIn, $shaOut, $submitUrl)
    {
        $this->em           = $em;

        $this->pspid        = $pspId;
        $this->shaIn        = $shaIn;
        $this->shaout       = $shaOut;
        $this->submitUrl    = $submitUrl;

        //generate(string $name, mixed $parameters = array(), Boolean $absolute = false)
        $this->resultUrl    = $router->generate('hellodi_transactions_result', [], true);
        $this->catalogUrl   = $router->generate('hellodi_transactions_new', [], true);
        $this->homeUrl  = $router->generate('hellodi_index', [], true);
        $this->ogoneTemplateUrl   = $router->generate('hellodi_transactions_ogone_template', [], true);
    }

    private function getSortedParameters(OgonePayment $payment)
    {

        return [
            'ACCEPTURL'     => $this->resultUrl,
            'AMOUNT'        => $payment->getOgoneAmount(),
            'CANCELURL'     => $this->resultUrl,
            'CATALOGURL'    => $this->catalogUrl,
            'CURRENCY'      => $payment->getPaymentCurrencyISO(),
            'DECLINEURL'    => $this->resultUrl,
            'EXCEPTIONURL'  => $this->resultUrl,
            'HOMEURL'       => $this->homeUrl,
            'LANGUAGE'      => sprintf('%s_%s', $this->locale, $this->locale),
            'ORDERID'       => $payment->getOrderReference(),
            'PSPID'         => $this->pspId,
            'TP'            => $this->ogoneTemplateUrl,
        ];
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

        $ogonePayment->setPaymentId($fields['PAYID']);

        switch ($fields['STATUS'])
        {
            case OgonePayment::OGONE_RESULT_ACCPETED:

            case OgonePayment::OGONE_RESULT_DECLINED:

            case OgonePayment::OGONE_RESULT_CANCELED:

            case OgonePayment::OGONE_RESULT_EXCEPTION:

            default:

        }



    }

    private function checkHashOut(array $fields)
    {
        $datas = array_change_key_case($fields, CASE_UPPER);

        foreach (['SHASIGN' , 'ORDERID' ,'STATUS', 'PAYID'] as $fieldName)
        {
            if (!isset($fields[$fieldName])){return false;}
        }

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

        return $fields['SHASIGN'] === sha1($hashedString);
    }

    public function generateForm(OgonePayment $payment)
    {

        $fields[] = sprintf('<form id="ogone-form" method="post" action="%s">', $this->submitUrl);

        foreach ($this->getSortedParameters($payment) as $name => $value)
        {
            $fields[] = sprintf('<input="hidden" name="%s" value="%s" />', $name, $value);
        }

        $fields[] = sprintf('<input="hidden" name="SHASIGN" value="%s" />', $this->generateHashIn($payment));
        $fields[] = '</form>';

        return implode('', $fields);
    }
}