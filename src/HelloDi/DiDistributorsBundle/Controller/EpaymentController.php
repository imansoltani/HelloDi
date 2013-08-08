<?php
namespace HelloDi\DiDistributorsBundle\Controller;

use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Entity\Transaction;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use HelloDi\DiDistributorsBundle\Entity\OgonePayment;
use HelloDi\DiDistributorsBundle\Form\OgonePayment\NewOgonePaymentType;
use Symfony\Component\Validator\Constraints\DateTime;
use HelloDi\DiDistributorsBundle\Ogone\RoutesContainer;

class EpaymentController extends Controller

{




    public function newAction(Request $request)
    {
        $ePaymentRoutes = new RoutesContainer($request);

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

                return  $this->redirect($this->generateUrl($ePaymentRoutes->getValidateUrl(),array('id'=>$ogonePayment->getId())));

            }

        }

    return $this->render('HelloDiDiDistributorsBundle:Ogone:EpaymentNew.html.twig',
            array('form' => $ogonePaymentForm->createView(),
            ));

    }

    public function validateAction($id)
    {
        $ePaymentRoutes = new RoutesContainer($this->getRequest());
        $em = $this->getDoctrine()->getManager();
        $ogonePayment = $em->getRepository('HelloDiDiDistributorsBundle:OgonePayment')->find($id);

        if( null === $ogonePayment || $this->getUser()->getId() !== $ogonePayment->getUser()->getId() )
        {
            $this->get('session')->getFlashBag()->add('Not valide!','Not valide!');

            return  $this->redirect($this->generateUrl( $ePaymentRoutes->getHomeUrl()));
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

        $ePaymentRoutes = new RoutesContainer($this->getRequest());
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

            return  $this->redirect($this->generateUrl($ePaymentRoutes->getHomeUrl()));

        }


      return $this->render('HelloDiDiDistributorsBundle:Ogone:EpaymentResult.html.twig', array('ogonePayment' => $ogonePayment));

    }

}