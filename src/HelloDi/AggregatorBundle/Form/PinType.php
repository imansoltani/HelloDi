<?php

namespace HelloDi\AggregatorBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('codes','entity',array(
                    'multiple' => true,
                    'expanded' => true,
                    'class' => 'HelloDiCoreBundle:Code',
                    'property' => 'serialNumber',
                ))
            ->add('check_all', 'checkbox', array(
                    'required'=>false,
                    'label' => 'Check All','translation_domain' => 'code',
                    'mapped' => false
                ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'csrf_protection' => false,
                'data_class' => 'HelloDi\CoreBundle\Entity\Pin'
        ));
    }

    public function getName()
    {
        return 'hellodi_master_bundle_pin_type';
    }
}
