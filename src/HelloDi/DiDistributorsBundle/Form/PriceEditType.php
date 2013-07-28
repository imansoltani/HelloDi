<?php

namespace HelloDi\DiDistributorsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PriceEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('price','integer')
            ->add('priceStatus','choice', array(
                'expanded'   => true,
                'choices'    => array(
                    0 => 'Inactive',
                    1 => 'Active',
                )
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\DiDistributorsBundle\Entity\Price'
        ));
    }

    public function getName()
    {
        return 'hellodi_didistributorsbundle_priceedittype';
    }
}
