<?php
namespace HelloDi\MasterBundle\Controller;

use Doctrine\ORM\EntityManager;
use HelloDi\AggregatorBundle\Entity\Provider;
use HelloDi\AggregatorBundle\Form\TopUpSearchType;
use HelloDi\CoreBundle\Entity\ItemDesc;
use HelloDi\MasterBundle\Form\ItemDescType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class TopUpController extends Controller
{
    public function importAction(Request $request)
    {
        $languages = $this->container->getParameter('languages');

        $description_en = new ItemDesc();
        $description_en->setLanguage('en');
        $description_en->setDescription("<b>Transaction details</b><br>
            Date: {{print_date}}<br>
            Transaction No.: {{tran_id}}<br>
            Receiver phone number: {{receiver_number}}<br>
            Operator: {{operator}}<br>
            Value sent: {{value_sent}}<br>
            Value paid: {{value_paid}}<br>
            Entity Name: {{entity_name}}<br>
            Address: <br>
            {{entity_address1}}<br>
            {{entity_address2}}<br>
            {{entity_address3}}<br>");

        $description_fr = new ItemDesc();
        $description_fr->setLanguage('fr');
        $description_fr->setDescription("<b>Reçu</b><br>
            Date: {{print_date}}<br>
            Numéro transaction: {{tran_id}}<br>
            Numéro de téléphone (receveur): {{receiver_number}}<br>
            Opérateur: {{operator}}<br>
            Valeur envoyée (monnaie locale): {{value_sent}}<br>
            Montant payé: {{value_paid}}<br>
            Nom de l'entité: {{entity_name}}<br>
            Adresse: <br>
            {{entity_address1}}<br>
            {{entity_address2}}<br>
            {{entity_address3}}<br>");

        $form = $this->createFormBuilder(array('descriptions'=>array($description_en, $description_fr)), array('attr'=>array(
                'class' => 'YesNoMessage',
                'header' => $this->get('translator')->trans('Upload',array(),'code'),
                'message' => $this->get('translator')->trans('Are_you_sure_you_perform_this_operation?',array(),'message'),
            )))
            ->add('provider' , 'entity', array(
                    'required' => true,
                    'label'=>'Provider', 'translation_domain'=>'accounts',
                    'class' => 'HelloDi\AggregatorBundle\Entity\Provider',
                    'property' => 'NameWithCurrency',
                    'empty_value' => 'select_a_provider',
                ))
            ->add('file', 'file', array(
                    'required' => true,
                    'label' => 'File', 'translation_domain' => 'code'
                ))
            ->add('delimiter', 'choice', array(
                    'required' => true,
                    'choices' => array(';' => ';', ',' => ',', ' ' => 'Space', '-' => '-'),
                    'label' => 'Delimiter','translation_domain' => 'code'
                ))
            ->add('descriptions', 'collection', array(
                    'type'   => new ItemDescType($languages),
                    'allow_add'    => true,
                    'allow_delete' => true,
                    'attr' => array('class'=>'descriptions')
                ))
            ->add('upload','submit', array(
                    'label'=>'Upload','translation_domain'=>'code',
                    'attr'=>array('first-button')
                ))
            ->add('addTranslate','button', array(
                    'label'=>'Add Translate','translation_domain'=>'code',
                    'attr'=>array('last-button', 'class'=>'addTranslate')
                ))
            ->getForm();

        if($request->isMethod('post')) {
            $form->handleRequest($request);

            if(count($form->get('descriptions')) > 0) {
                $used_languages = array();
                foreach($form->get('descriptions') as $descriptionForm) {
                    /** @var FormInterface $descriptionForm */
                    $lang = $descriptionForm->get('language')->getData();
                    if(in_array($lang, $used_languages))
                        $descriptionForm->get('language')->addError(new FormError('Language must be unique.'));
                    else
                        $used_languages[]= $lang;
                }
            } else
                $form->get('delimiter')->addError(new FormError('Add one description at least.'));

            if($form->isValid()) {
                $data = $form->getData();

                /** @var UploadedFile $uploaded_file */
                $uploaded_file = $data['file'];
                $file = $uploaded_file->move(__DIR__.'/../../../../web/uploads/temp', round(microtime(true)*1000).".tmp");

                $result = null;
                try {
                    $result = $this->get('topup')->importItemsAndPricesFromFile($file, $data['delimiter'], $data['provider'], $data['descriptions']);

                    unlink($file->getRealPath());

                    return $this->render('HelloDiMasterBundle:topup:importResult.html.twig' , array(
                            'result' => $result
                        ));
                } catch (\Exception $e) {
                    unlink($file->getRealPath());

                    $form->get('file')->addError(new FormError($e->getMessage()));
                }
            }
        }

        return $this->render('HelloDiMasterBundle:topup:import.html.twig' , array(
                'form' => $form->createView()
            ));
    }

    public function reportAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(new TopUpSearchType(), null, array(
                'attr' => array('class' => 'SearchForm'),
                'method' => 'get',
            ))
            ->add('search','submit', array(
                    'label'=>'Search','translation_domain'=>'common',
                ));

        $form->handleRequest($request);

        $qb = $em->createQueryBuilder()
            ->select('topup, item, user, provider_transaction, provider_account')
            ->from('HelloDiAggregatorBundle:TopUp', 'topup')
            ->leftJoin('topup.providerTransaction', 'provider_transaction')
            ->leftJoin('provider_transaction.account', 'provider_account')
            ->innerJoin('topup.item', 'item')
            ->innerJoin('topup.user', 'user')
        ;

        if($form->isValid()) {
            $form_data = $form->getData();

            if(isset($form_data['provider'])) {
                /** @var Provider $provider */
                $provider = $form_data['provider'];
                $qb->andWhere('provider_account = :provider')->setParameter('provider', $provider->getAccount());
            }

            if(isset($form_data['item']))
                $qb->andWhere('item = :item')->setParameter('item', $form_data['item']);

            if(isset($form_data['user']))
                $qb->andWhere('user = :user')->setParameter('user', $form_data['user']);

            if(isset($form_data['from']))
                $qb->andWhere('topup.date >= :from')->setParameter('from', $form_data['from']);

            if(isset($form_data['to']))
                $qb->andWhere('topup.date <= :to')->setParameter('to', $form_data['to']);

            if(isset($form_data['status'])) {
                if($form_data['status'] == 2)
                    $qb->andWhere('topup.status is null');
                else
                    $qb->andWhere('topup.status = :status')->setParameter('status', $form_data['status'] == '1');
            }
        }

        $topup_s = $this->get('knp_paginator')->paginate($qb->getQuery(), $request->get('page', 1), 20);

        return $this->render('HelloDiMasterBundle:topup:report.html.twig', array(
                'topup_s' => $topup_s,
                'form' => $form->createView()
            ));
    }

    public function updateReportAction()
    {
        try {
            $this->get('topup')->updateReportB2BServer();
            $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('the_operation_done_successfully', array(), 'message'));
        } catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans(strip_tags($e->getMessage()), array(), 'message'));
        }
        return $this->redirect($this->generateUrl('hello_di_master_topup_report'));
    }
}