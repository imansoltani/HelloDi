<?php

namespace HelloDi\DiDistributorsBundle\Form\Account;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;

class AccountSearchDistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
             //  ->add('TypeSearch','choice',array('choices'=>(array(0=>'All',1=>'MyAccount')),'preferred_choices'=>array(1)))
               ->add('accName','text',array('required'=>false))
               ->add('entName','text',array('required'=>false))
               ->add('accBalance','choice',array('choices'=>(array(0=>'Lower Than',1=>'More Than'))))
               ->add('accBalanceValue','text',array('required'=>false))
                ->add('accCurrency','choice',array('choices'=>(array(1=>'USD',0=>'CHF',2=>'All'))))
               //->add('accStatus','choice',array('choices'=>(array(2=>'All',0=>'InActive',1=>'Active')),'preferred_choices'=>array(2)))
               ->add('accCreditLimit','choice',array('choices'=>(array(2=>'All',1=>'Have',0=>'Have Not')),'preferred_choices'=>array(2)));


    }

    public function getName()
    {
        return 'AccountSearchDistType';
    }
}
