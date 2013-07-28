<?php

namespace HelloDi\DiDistributorsBundle\Form\Account;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AccountProvType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('accName','text')
            ->add('accCurrency','choice',array('choices'=>(array('USD'=>'USD','CHF'=>'CHF'))))
            ->add('accTerms','text',array())
            ->add('accTimeZone','timezone')
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
