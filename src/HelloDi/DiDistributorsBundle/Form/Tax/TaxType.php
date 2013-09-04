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
            ->add('tax',null,array('label'=>'Tax','translation_domain'=>'vat'))
            ->add('Country','entity',
            array(
                   'label'=>'Country','translation_domain'=>'entity',
                   'class'=>'HelloDi\DiDistributorsBundle\Entity\Country',
                    'property'=>'IsoName'
            )
            )
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
