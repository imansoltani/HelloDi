<?php

namespace HelloDi\PricingBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ModelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("name",null,array('label' => 'Model Name','required'=>true,'translation_domain' => 'transaction'))
            ->add("json",'hidden')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\PricingBundle\Entity\Model'
        ));
    }

    public function getName()
    {
        return 'hellodi_pricingbundle_modeltype';
    }
}
