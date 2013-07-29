<?php

namespace HelloDi\DiDistributorsBundle\Form\Account;

use HelloDi\DiDistributorsBundle\Form\Account\AccountProvType;
use HelloDi\DiDistributorsBundle\Form\AddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EntitiAccountprovType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('entName', 'text',array('required'=>false))
            ->add('entVatNumber', 'text',array())
            ->add('entTel1', 'text',array())
            ->add('entTel2', 'text',array('required'=>false))
            ->add('entFax', 'text',array('required'=>false))
            ->add('entWebsite', 'text',array('required'=>false))
            ->add('entAdrs1', 'text',array())
            ->add('entAdrs2', 'text',array('required'=>false))
            ->add('entAdrs3', 'text',array('required'=>false))
            ->add('entCity', 'text',array('required'=>false))
            ->add('entNP', 'text',array())
            ->add('Country','entity',array('class'=>'HelloDi\DiDistributorsBundle\Entity\Country','property'=>'name'))
            ->add('Accounts','collection',array('type'=>new AccountProvType()))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\DiDistributorsBundle\Entity\Entiti',
        ));
    }

    public function getName()
    {
        return 'EntitiAccountprov';
    }
}