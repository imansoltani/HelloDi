<?php

namespace HelloDi\DiDistributorsBundle\Form\Distributors;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;

class RetailerSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id','text',array('required'=>false))
            ->add('retName','text',array('required'=>false))
            ->add('retCityName','text',array('required'=>false))
            ->add('retBalance','choice',array('choices'=>(array(0=>'Lower Than',1=>'More Than')),'preferred_choices'=>array(1)))
            ->add('retBalanceValue','text',array('required'=>false))
            ->add('retcurency','choice',array('required'=>false,'choices'=>(array('CHF'=>'CHF','EUR'=>'EUR' ,'USD'=>'USD'))));
    }

    public function getName()
    {
        return 'RetailerSerachType';
    }
}
