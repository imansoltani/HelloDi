<?php

namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Entity\OgonePayment;
use HelloDi\DiDistributorsBundle\Entity\Transaction;
use HelloDi\DiDistributorsBundle\Form\TransactionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use HelloDi\DiDistributorsBundle\Form\NewOgonePaymentType;
use Symfony\Component\Validator\Constraints\True;

class EpaymentController extends Controller
{
    public function newAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $useraccount = $user->getAccount();
        $accCurency =  $useraccount->getAccCurrency();

        $ogonePayment = new OgonePayment;
        $ogonePayment->setUser($user);
        $ogonePayment->setCreatedAt(new \DateTime());
        $ogonePayment->setPaymentCurrencyISO($accCurency);
        $ogonePayment->setStatus(OgonePayment::STATUS_PENDING);

        $ogonePaymentForm = $this->createForm(new NewOgonePaymentType(), $ogonePayment);

        if ($request->isMethod('POST')) {
            $ogonePaymentForm->handleRequest($request);
            if ($ogonePaymentForm->isValid()) {
                   $em = $this->getDoctrine()->getManager();
                   $em->persist($ogonePayment);
                   $em->flush();
                return $this->redirect($this->generateUrl('retailer_OgoneTransactions_validate',array('id'=>$ogonePayment->getId())));
            }
        }


        return $this->render('HelloDiDiDistributorsBundle:Retailers:OgoneTransactionNew.html.twig',array('id'=>1,'accCurency'=>$accCurency,'formOgone'=>$ogonePaymentForm->createView()));
    }

    public function ValidateAction(Request $request,$id){
        $em = $this->getDoctrine()->getManager();
        $ogonePayment = $em->getRepository('HelloDiDiDistributorsBundle:OgonePayment')->find($id);
        $Amount = $ogonePayment->getpaymentAmount();
        $Curency = $ogonePayment->getpaymentCurrencyISO();
        $NumberOrder = $ogonePayment->getOrderReference();
        $codeselector = $this->get('hello_di_di_distributors.Client');
        $Signager = $codeselector->ProcessRequest($ogonePayment);
        $Values  = $codeselector->getValues($ogonePayment);



        return $this->render('HelloDiDiDistributorsBundle:Retailers:OgoneTransactionNewValidate.html.twig',array('signager'=>$Signager,'values'=>$Values,'amount'=>$Amount,'curency'=>$Curency,'orderNumber'=>$NumberOrder));


    }

    public function AcceptAction(Request $request){

        $parametr = array(
            'ACCEPTANCE'     => $request->get('ACCEPTANCE'),
            'AMOUNT'     => $request->get('amount'),
            'BRAND'     => $request->get('BRAND'),
            'CARDNO'     => $request->get('CARDNO'),
            'CURRENCY'        => $request->get('currency'),
            'NCERROR'     => $request->get('NCERROR'),
            'ORDERID'     => $request->get('orderID'),
            'PAYID'     => $request->get('PAYID'),
            'PM'      =>$request->get('PM'),
            'STATUS'     => $request->get('STATUS')
        );



        $ogonePmt = $this->get('hello_di_di_distributors.Client');
        $ogonePmt->ProcessBack($parametr,$request->get('SHASIGN'));



        return $this->render('HelloDiDiDistributorsBundle:Retailers:AcceptOgoneTransaction.html.twig');
    }
}
