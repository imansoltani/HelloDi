<?php

namespace HelloDi\DiDistributorsBundle\Form\Entiti;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EditEntitiRetailerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('entName', 'text',array('required'=>true,'label' => 'EntityName','translation_domain' => 'entity'))
            ->add('entVatNumber', 'text',array('required'=>false,'label' => 'VatNumber','translation_domain' => 'entity'))
            ->add('entTel1', 'text',array('required'=>false,'label' => 'Tel1','translation_domain' => 'entity'))
            ->add('entTel2', 'text',array('required'=>false,'label' => 'Tel2','translation_domain' => 'entity'))
            ->add('entFax', 'text',array('required'=>false,'label' => 'Fax','translation_domain' => 'entity'))
            ->add('entWebsite', 'text',array('required'=>false,'label' => 'WebSite','translation_domain' => 'entity'))
            ->add('entAdrs1',null,array('required'=>true,'label' => 'Adrs1','translation_domain' => 'entity'))
            ->add('entAdrs2',null,array('required'=>false,'label' => 'Adrs2','translation_domain' => 'entity'))
            ->add('entAdrs3',null,array('required'=>false,'label' => 'Adrs3','translation_domain' => 'entity'))
            ->add('entNp',null,array('required'=>true,'label' => 'NP','translation_domain' => 'entity'))
            ->add('entCity',null,array('required'=>true,'label' => 'City','translation_domain' => 'entity'))
            ->add('Country','entity',
                array('label' => 'Country','translation_domain' => 'entity',
                    'class'=>'HelloDi\DiDistributorsBundle\Entity\Country',
                    'property'=>'name'
                )
            )
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\DiDistributorsBundle\Entity\Entiti'
        ));
    }

    public function getName()
    {
        return 'EditEntitiRetailerTypType';
    }
}
