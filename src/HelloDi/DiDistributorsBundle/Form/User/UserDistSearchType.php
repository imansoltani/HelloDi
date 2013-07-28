<?php

namespace HelloDi\DiDistributorsBundle\Form\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserDistSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username',null,array('required'=>false))
            ->add('privilege','choice',array('choices'=>array(0=>'Seller',1=>'Admin')))
        ;
    }


    public function getName()
    {
        return 'UserDistSearch';
    }
}
