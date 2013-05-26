<?php

namespace HelloDi\DiDistributorsBundle\Form\Account;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AccountDistMasterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('accName','text',array('label'=>'Name'))
            ->add('accBalance','text',array('label'=>'Balance','disabled'=>true))
            ->add('accCurrency','choice',array('label'=>'Currency','choices'=>(array('USD'=>'USD','CHF'=>'CHF'))))
            ->add('accTimeZone','timezone',array('label'=>'TimeZone'))
            ->add('accTerms','text',array('label'=>'Payment condition','required'=>false))
          //  ->add('accType','choice',array('choices'=>(array('Distri'=>'Yes',0=>'No')),'preferred_choices'=>array(1)));
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
        return 'AccountDistMaster';
    }
}
