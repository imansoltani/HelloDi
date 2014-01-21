<?php

namespace HelloDi\DiDistributorsBundle\Form\HomePage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('Name','text',array('required'=>false))
            ->add('Email','email',array('label'=>'Email','translation_domain'=>'homepage'))
            ->add('Description','textarea',array('label'=>'Description','translation_domain'=>'homepage'))
            ->add('Inquiry','choice',array(
                'choices'=>array(
                    0=>'Distributors',
                    1=>'retailers',
                     2=>'Providers'
                ),
                'label'=>'Select_your_inquiry',
                'translation_domain'=>'homepage'
            ));
            //->add('captcha', 'captcha');
    }


    public function getName()
    {
        return 'hellodi_didistributorsbundle_contacttype';
    }
}
