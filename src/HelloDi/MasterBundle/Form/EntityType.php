<?php

namespace HelloDi\MasterBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EntityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',null,array('label' => 'Name','translation_domain' => 'entity'))
            ->add('vatNumber',null,array(
                'required'=>false,
                'label' => 'VatNumber','translation_domain' => 'entity',
                'attr'=> array('class'=>'integer_validation'),
            ))
            ->add('tel1',null,array(
                'required'=>false,
                'label' => 'Tel1','translation_domain' => 'entity',
                'attr'=> array('class'=>'tel_validation'),
            ))
            ->add('tel2',null,array(
                'required'=>false,'label' => 'Tel2',
                'translation_domain' => 'entity',
                'attr'=> array('class'=>'tel_validation'),
            ))
            ->add('fax',null,array(
                'required'=>false,'label' => 'Fax',
                'translation_domain' => 'entity',
                'attr'=> array('class'=>'tel_validation'),
            ))
            ->add('website',null,array('required'=>false,'label' => 'WebSite','translation_domain' => 'entity'))
            ->add('address1',null,array('required'=>true,'label' => 'Address1','translation_domain' => 'entity'))
            ->add('address2',null,array('required'=>false,'label' => 'Address2','translation_domain' => 'entity'))
            ->add('address3',null,array('required'=>false,'label' => 'Address3','translation_domain' => 'entity'))
            ->add('NP',null,array(
                'required'=>true,
                'label' => 'NP','translation_domain' => 'entity',
                'attr'=> array('class'=>'integer_validation'),
            ))
            ->add('city',null,array('required'=>true,'label' => 'City','translation_domain' => 'entity'))
            ->add('country','entity',array(
                    'label' => 'Country','translation_domain' => 'entity',
                    'class'=>'HelloDi\CoreBundle\Entity\Country','property'=>'name'
                ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\CoreBundle\Entity\Entity'
        ));
    }

    public function getName()
    {
        return 'hellodi_master_bundle_entity_type';
    }
}
