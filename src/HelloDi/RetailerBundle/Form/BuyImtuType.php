<?php

namespace HelloDi\RetailerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

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
                    'constraints' => array(
                        new Assert\NotNull(),
                        new Assert\Regex(array('pattern' => '/^\+?\d{5,}$/', 'match'=>true))
                    )
                ))
            ->add('country','text',array(
                    'required'=>true,
                    'label' => 'Country','translation_domain' => 'item',
                    'disabled' => true,
                ))
            ->add('countryIso','hidden')
            ->add('operator','text',array(
                    'required'=>true,
                    'label' => 'Operator','translation_domain' => 'operator',
                    'attr'=> array(
                        'onchange' => 'getItem($(this).val(), $(\'#denomination\').val())',
                        'ajax-dropdown',
                        'on_refresh_click' => 'readNumber($(\'#receiverMobileNumber\').val())'
                    ),
                    'constraints' => array(
                        new Assert\NotNull()
                    )
                ))
            ->add('denomination','text',array(
                    'required'=>true,
                    'label' => 'denomination','translation_domain' => 'price',
                    'attr'=> array('ajax-dropdown', 'on_refresh_click' => 'getItem($(\'#operator\').val(), $(\'#denomination\').val())'),
                    'constraints' => array(
                        new Assert\NotNull()
                    )
                ))
            ->add('senderMobileNumber','text',array(
                    'required'=>false,
                    'label' => 'Sender Mobile Number','translation_domain' => 'item',
                    'attr'=> array('class'=>'tel_validation'),
                    'constraints' => array(
                        new Assert\Regex(array('pattern' => '/^\+?\d{5,}$/', 'match'=>true))
                    )
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
