<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Fils du Soleil
 * Date: 18.07.13
 * Time: 17:28
 * To change this template use File | Settings | File Templates.
 */

namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Entity\Transaction;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use HelloDi\DiDistributorsBundle\Entity\OgonePayment;
use HelloDi\DiDistributorsBundle\Form\OgonePayment\NewOgonePaymentType;
use Symfony\Component\Validator\Constraints\DateTime;


class EpaymentController extends Controller
{
    public function newAction(Request $request)
    {
        $user = $this->getUser();
        $currency = $this->getUser()->getAccount()->getAccCurrency();

        $ogonePayment = new OgonePayment();
        $tran=new Transaction();

        $ogonePayment->setUser($user);
//        $ogonePayment->setTransaction($tran);
        $ogonePayment->setPaymentCurrencyISO($currency);

        $ogonePayment->setStatus(OgonePayment::STATUS_PENDING);

        $ogonePaymentForm = $this->createForm(new NewOgonePaymentType($currency),$ogonePayment);

        if ($request->isMethod('POST'))
        { $ogonePaymentForm->handleRequest($request);
            if($ogonePaymentForm->isValid())
            {

                $em = $this->getDoctrine()->getManager();
                $em->persist($ogonePayment);
                $em->flush();
                return $this->redirect($this->generateUrl('retailer_OgoneTransactions_validate', ['id'=>$ogonePayment->getId()] ));

            }

        }

        return $this->render('HelloDiDiDistributorsBundle::Epaymentnew.html.twig',
            [
                'form' => $ogonePaymentForm->createView(),
            ]);
    }

    public function validateAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $ogonePayment = $em->getRepository('HelloDiDiDistributorsBundle:OgonePayment')->find($id);

        if( null === $ogonePayment || $this->getUser()->getId() !== $ogonePayment->getUser()->getId() )
        {
            $this->get('session')->getFlashBag()->add('Not valide!','Not valide!');
            return $this->redirect($this->generateUrl('index_page'));
        }

        $ogonePaymentValidateform = $this->get('hello_di_di_distributors.ogone.client')->generateForm($ogonePayment);

        return $this->render(
            'HelloDiDiDistributorsBundle:Epayment.validate.html.twig',
            [   'ogonePayment' => $ogonePayment,
                'form' => $ogonePaymentValidateform
            ]
        );
    }

    public function resultAction()
    {
        $digest = $this->getRequest()->query->all();
        try
        {
            $ogonePayment = $this->get('hello_di_di_distributors.ogone.client')->processResult(
                $this->getUser(),
                $digest
            );
        }
        catch (\HelloDi\DiDistributorsBundle\Exception\OgoneException $excep)
        {
            $this->get('session')->getFlashBag()->add('result is not valide!', $excep->getMessage());
            return $this->redirect($this->generateUrl('index_page'));
        }

        return $this->render('HelloDiDiDistributorsBundle:Epayment.result.html.twig', ['ogonePayment' => $ogonePayment]);
    }


}