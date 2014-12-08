<?php

namespace HelloDi\RetailerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class BuyImtuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('receiverMobileNumber','text',array(
                    'required'=>true,
                    'label' => 'Receiver Mobile Number','translation_domain' => 'item',
                    'attr'=> array(
                        'class'=>'tel_validation',
                        'onblur' => 'readNumber($(this).val())'
                    ),
                ))
            ->add('country','text',array(
                    'required'=>true,
                    'label' => 'Country','translation_domain' => 'item',
                    'disabled' => true,
                ))
            ->add('countryIso','hidden')
            ->add('operator','choice',array(
                    'required'=>true,
                    'label' => 'Operator','translation_domain' => 'operator',
                    'disabled' => true,
                    'attr'=> array('onchange' => 'getItem($(this).val())'),
                ))
            ->add('denomination','choice',array(
                    'required'=>true,
                    'label' => 'denomination','translation_domain' => 'price',
                    'disabled' => true,
                ))
            ->add('senderMobileNumber','text',array(
                    'required'=>false,
                    'label' => 'Sender Mobile Number','translation_domain' => 'item',
                    'attr'=> array('class'=>'tel_validation'),
                ))
            ->add('senderEmail','email',array(
                    'required'=>false,
                    'label' => 'Sender Email','translation_domain' => 'user'
                ))
        ;
    }

    public function getName()
    {
        return '';
    }
}
