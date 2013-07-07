<?php

namespace HelloDi\DiDistributorsBundle\Form\Account;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AccountDistSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('accName','text',array('required'=>false))
            ->add('accCreditLimit','choice',array('choices'=>(array(0=>'Have Not',1=>'Have'))))
//            ->add('accTerms','text',array())
//            ->add('accStatus','choice',array('choices'=>(array(0=>'InActive',1=>'Active'))))
//            ->add('accTimeZone','timezone',array())
            ->add('accBalance','choice',array('choices'=>(array(0=>'Lower Than',1=>'More Than'))))
            ->add('accBalanceValue','text',array('required'=>false))
         //   ->add('TypeAccount','choice',array('choices'=>(array(0=>'Dist',1=>'Prov',2=>'Every'))))
        ;
    }


    public function getName()
    {
        return 'AccountDistChildSearch';
    }
}
