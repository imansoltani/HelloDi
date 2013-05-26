<?php

namespace HelloDi\DiDistributorsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('adrsType', 'choice',array('choices'=>(array(0=>'Address.TypeChoice.Office')),'preferred_choices'=>array('OFFICE'),'label' => 'Address.Type','translation_domain' => 'entity'))
            ->add('adrsNp', 'text',array('data'=>221525,'label' => 'Address.Np','translation_domain' => 'entity'))
            ->add('adrsCity', 'text',array('trim'=>true,'data'=>'ghaen','label' => 'Address.City','translation_domain' => 'entity'))
            ->add('Country','entity',array('preferred_choices'=>array('swaziland'),'label' => 'Address.Country','translation_domain' => 'entity'))
            ->add('adrs1', 'text',array('trim'=>true,'data'=>'street abolmafakhwr','label' => 'Address.Address1','translation_domain' => 'entity'))
            ->add('adrs2','text',array('required'=>false,'trim'=>true,'data'=>'abolmafakher 1','label' => 'Address.Address2','translation_domain' => 'entity'))
            ->add('adrs3','text',array('required'=>false,'trim'=>true,'data'=>'pelpak 15','label' => 'Address.Address3','translation_domain' => 'entity'))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\DiDistributorsBundle\Entity\Address'
        ));
    }

    public function getName()
    {
        return 'Address';
    }
}
