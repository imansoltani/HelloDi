<?php
namespace HelloDi\MasterBundle\Controller;

use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\CoreBundle\Entity\Entity;
use HelloDi\CoreBundle\Entity\Provider;
use HelloDi\MasterBundle\Form\ProviderAccountEntityUserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ProviderController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $providers = $em->getRepository('HelloDiCoreBundle:Provider')->findAll();

        return $this->render('HelloDiMasterBundle:provider:index.html.twig', array(
            'providers' => $providers
        ));
    }

    public function AddAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $provider = new Provider();

        $account = new Account();
        $account->setCreationDate(new \DateTime('now'));
        $account->setType(Account::PROVIDER);
        $provider->setAccount($account);

        $entity = new Entity();
        $account->setEntity($entity);
        $entity->addAccount($account);

        $currencies = $this->container->getParameter('Currencies.Account');
        $languages = $this->container->getParameter('languages');

        $form = $this->createForm(new ProviderAccountEntityUserType($currencies,$languages), $provider, array('cascade_validation' => true));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($provider);
                $em->persist($account);
                $em->persist($entity);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'this operation done success !');
                return $this->redirect($this->generateUrl('hello_di_master_provider_index'));
            }
        }

        return $this->render('HelloDiMasterBundle:provider:Add.html.twig', array(
            'form' => $form->createView(),
        ));
    }
} 