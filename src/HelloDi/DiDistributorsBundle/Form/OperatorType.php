<?php

namespace HelloDi\DiDistributorsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OperatorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',null,array('label' => 'Name','translation_domain' => 'operator'))
            ->add('file',null,array('label'=>'Logo','translation_domain' => 'operator','required'=> true))
            ->add('carrierCode',null,array('label'=>'CarrierCode','translation_domain' => 'operator','required'=> false))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\DiDistributorsBundle\Entity\Operator'
        ));
    }

    public function getName()
    {
        return 'hellodi_didistributorsbundle_operatortype';
    }
}
