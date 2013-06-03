<?php

namespace HelloDi\DiDistributorsBundle\Form\Account;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AccountRetailerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('accName','text',array('label'=>'Name'))
            ->add('accBalance','text',array('label'=>'Balance','disabled'=>true))
            ->add('accCurrency','choice',array('label'=>'Currency','choices'=>(array('USD'=>'USD','CHF'=>'CHF'))))
            ->add('accTimeZone','timezone',array('label'=>'TimeZone'))
            ->add('accTerms','text',array('label'=>'Payment condition','required'=>false))
            ->add('accCreditLimit','text',array('label'=>'accCreditLimit','required'=>false))
            ->add('accCreationDate','date',array('format'=>'y-M-d','label'=>'accCreationDate','required'=>false))
            ->add('accDefaultLanguage','choice',array('choices'=>(array('en'=>'en','fr'=>'fr')),'preferred_choices'=>array(1)));
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
        return 'AccountRetailer';
    }
}
