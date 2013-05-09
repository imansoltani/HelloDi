<?php

namespace HelloDi\DiDistributorsBundle\Form\Entiti;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EntitiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('entName', 'text',array('label' => 'Entiti.Name','translation_domain' => 'entity'))
            ->add('entVatNumber', 'text',array('label' => 'Entiti.VatNumber','translation_domain' => 'entity'))
            ->add('entTel1', 'text',array('label' => 'Entiti.Tel1','translation_domain' => 'entity'))
            ->add('entTel2', 'text',array('required'=>false,'label' => 'Entiti.Tel2','translation_domain' => 'entity'))
            ->add('entFax', 'text',array('required'=>false,'label' => 'Entiti.Fax','translation_domain' => 'entity'))
            ->add('entWebsite', 'text',array('required'=>false,'label' => 'Entiti.WebSite','translation_domain' => 'entity'))
            ->add('entAdrs1')
            ->add('entAdrs2')
            ->add('entAdrs3')
            ->add('entNp')
            ->add('entCity')
            ->add('Country','entity',
                array(
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
        return 'Entiti';
    }
}
