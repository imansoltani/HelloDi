<?php

namespace HelloDi\DiDistributorsBundle\Form\Tax;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TaxType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tax')
//            ->add('taxstart')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\DiDistributorsBundle\Entity\Tax'
        ));
    }

    public function getName()
    {
        return 'hellodi_didistributorsbundle_taxtype';
    }
}
