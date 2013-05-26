<?php

namespace HelloDi\DiDistributorsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('serialNumber',null,array('label' => 'Code.SerialNumber','translation_domain' => 'code'))
            ->add('pin',null,array('label' => 'Code.Pin','translation_domain' => 'code'))
            ->add('status',null,array('label' => 'Code.Status','translation_domain' => 'code'))
            ->add('creditingDate',null,array('label' => 'Code.CreditingDate','translation_domain' => 'code'))
            ->add('Item',null,array('label' => 'Code.Item','translation_domain' => 'code'))
            ->add('Input',null,array('label' => 'Code.Input','translation_domain' => 'code'))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\DiDistributorsBundle\Entity\Code'
        ));
    }

    public function getName()
    {
        return 'Code';
    }
}
