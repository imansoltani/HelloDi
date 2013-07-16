<?php

namespace HelloDi\DiDistributorsBundle\Form\Account;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('accName','text',array('required'=>true,'label' => 'Name','translation_domain' => 'Account'))
            ->add('accCurrency','choice',array('label' => 'Currency','translation_domain' => 'Account','choices'=>(array('USD'=>'USD','CHF'=>'CHF'))))
            ->add('accTerms','text',array('label' => 'Terms','translation_domain' => 'Account','required'=>false))
            ->add('accTimeZone','timezone',array('label' => 'TimeZone','translation_domain' => 'Account'))
            ->add('accDefaultLanguage','choice',array('label' => 'DefaultLanguage','translation_domain' => 'Account','choices'=>(array('en'=>'en','fr'=>'fr')),'preferred_choices'=>array(1)));
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
        return 'AccountProv';
    }
}
