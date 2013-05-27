<?php

namespace HelloDi\DiDistributorsBundle\Form\Account;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AccountDistChildType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('accName','text',array())
            ->add('accCreditLimit','money',array('grouping'=>3))
            ->add('accTerms','text',array('data'=>120))
            //>add('accStatus','choice',array('choices'=>(array(0=>'InActive',1=>'Active')),'preferred_choices'=>array(1)))
            ->add('accTimeZone','timezone',array('preferred_choices'=>array('America/Chicago')))
            ->add('accType','choice',array('choices'=>(array(0=>'has not child',1=>'has child')),'preferred_choices'=>array(1)))
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
        return 'AccountDistChild';
    }
}
