<?php

namespace HelloDi\DiDistributorsBundle\Form\Address;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('adrsType', 'choice',array('choices'=>(array(0=>'OFFICE')),'preferred_choices'=>array('OFFICE'),'label'=>'Your adrsType :'))
            ->add('adrsNp', 'text',array('data'=>221525,))
            ->add('adrsCity', 'text',array('trim'=>true,'data'=>'ghaen'))
            ->add('Country','entity',array('class'=>'HelloDi\DiDistributorsBundle\Entity\Country','property'=>'name'))
            ->add('adrs1', 'text',array('trim'=>true,'data'=>'street abolmafakhwr'))
            ->add('adrs2','text',array('required'=>false,'trim'=>true,'data'=>'abolmafakher 1','required'=>false))
            ->add('adrs3','text',array('required'=>false,'trim'=>true,'data'=>'pelpak 15','required'=>false))
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
