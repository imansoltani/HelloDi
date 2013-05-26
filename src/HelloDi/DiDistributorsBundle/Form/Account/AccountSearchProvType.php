<?php

namespace HelloDi\DiDistributorsBundle\Form\Account;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;

class AccountSearchProvType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                   ->add('id','text',array('required'=>false))
                   ->add('accName','text',array('required'=>false))
                   ->add('entName','text',array('required'=>false))
                   ->add('accBalance','choice',array('choices'=>(array(0=>'Lower Than',1=>'More Than')),'preferred_choices'=>array(1)))
                   ->add('accBalanceValue','text',array('required'=>false));

    }

    public function getName()
    {
        return 'AccountSearchProvType';
    }
}
