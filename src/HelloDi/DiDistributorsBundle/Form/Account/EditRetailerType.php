<?php

namespace HelloDi\DiDistributorsBundle\Form\Account;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EditRetailerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

                $builder
                    ->add('accTerms','text',array('label'=>'Terms','translation_domain' => 'accounts','required'=>false))
                    ->add('accDefaultLanguage','choice',
                        array(
                            'label'=>'DefaultLanguage',
                            'translation_domain' => 'accounts',
                            'required'=>true,
                            'choices'=>
                            (
                            array('en'=>'en','fr'=>'fr','de'=>'de'))));


    ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\DiDistributorsBundle\Entity\Account'
        ));
    }

    public function getName()
    {
        return 'EditRetailerType';
    }
}
