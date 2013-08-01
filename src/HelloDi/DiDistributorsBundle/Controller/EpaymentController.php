<?php
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
        $role=$this->get('hello_di_di_distributors.ogone.role');

        $user = $this->getUser();
        $currency = $this->getUser()->getAccount()->getAccCurrency();

        $ogonePayment = new OgonePayment();

        $ogonePayment->setUser($user);

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
                return  $this->redirect($role->Validate($ogonePayment->getId()));

            }

        }

    return $this->render('HelloDiDiDistributorsBundle:Ogone:EpaymentNew.html.twig',
            array('form' => $ogonePaymentForm->createView(),
            ));

    }

    public function validateAction($id)
    {
        $role=$this->get('hello_di_di_distributors.ogone.role');
        $em = $this->getDoctrine()->getManager();
        $ogonePayment = $em->getRepository('HelloDiDiDistributorsBundle:OgonePayment')->find($id);

        if( null === $ogonePayment || $this->getUser()->getId() !== $ogonePayment->getUser()->getId() )
        {
            $this->get('session')->getFlashBag()->add('Not valide!','Not valide!');

            return  $this->redirect($role->IndexPage());
         }

       $cilent=$this->get('hello_di_di_distributors.ogone.client');

        $ogonePaymentValidateform = $cilent->generateForm($ogonePayment);
        return $this->render(
            'HelloDiDiDistributorsBundle:Ogone:EpaymentValidate.html.twig',
            array(
                'ogonePayment' => $ogonePayment,
                'form' => $ogonePaymentValidateform
            )
        );
    }

    public function resultAction()
    {
        $role=$this->get('hello_di_di_distributors.ogone.role');

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

            return  $this->redirect($role->TransactionNew());

        }


      return $this->render('HelloDiDiDistributorsBundle:Ogone:EpaymentResult.html.twig', array('ogonePayment' => $ogonePayment));

    }




}