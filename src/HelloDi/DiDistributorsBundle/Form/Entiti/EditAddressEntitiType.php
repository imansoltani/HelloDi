<?php

namespace HelloDi\DiDistributorsBundle\Form\Entiti;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EditAddressEntitiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('entAdrs1',null,array('required'=>true,'label' => 'Town','translation_domain' => 'entity'))
            ->add('entAdrs2',null,array('required'=>false,'label' => 'Street','translation_domain' => 'entity'))
            ->add('entAdrs3',null,array('required'=>false,'label' => 'Additional','translation_domain' => 'entity'))
            ->add('entNp',null,array('required'=>true,'label' => 'PostaCode','translation_domain' => 'entity'))
            ->add('entCity',null,array('required'=>true,'label' => 'City','translation_domain' => 'entity'))
            ->add('Country','entity',
                array('label' => 'Country','translation_domain' => 'Country',
                    'class'=>'HelloDi\DiDistributorsBundle\Entity\Country',
                    'property'=>'name'
                )
            )
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
        return 'EditAddressEntitiType';
    }
}
