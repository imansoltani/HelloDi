<?php

namespace HelloDi\DiDistributorsBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CdSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('provider','entity',array(
                'required'=> false,
                'empty_value' => 'Code.All',
                'class' => 'HelloDiDiDistributorsBundle:Account',
                'property' => 'accName',
                'query_builder' => function(EntityRepository $er)
                {
                    return $er->createQueryBuilder('u')->where("u.accType = 1 ");
                },
                'label' => 'Code.Provider','translation_domain' => 'code'
            ))
            ->add('item','entity',array(
                'required'=> false,
                'empty_value' => 'Code.All',
                'class' => 'HelloDiDiDistributorsBundle:Item',
                'property' => 'itemName',
                'label' => 'Code.Item','translation_domain' => 'code'
            ))
            ->add('inputFileName', 'entity', array(
                'required'=> false,
                'empty_value' => 'Code.All',
                'class' => 'HelloDiDiDistributorsBundle:Input',
                'property' => 'fileName',
                'label' => 'Code.InputFileName','translation_domain' => 'code'
            ))
            ->add('fromserial', 'text',array(
                'required'=> false,
                'label' => 'Code.FromSerialNumber','translation_domain' => 'code'
            ))
            ->add('toserial', 'text',array(
                'required'=> false,
                'label' => 'Code.ToSerialNumber','translation_domain' => 'code'
            ))
            ->add('status','choice',array(
                'choices'=> array('3' => 'All' , '0'=>'Unavailable', '1'=>'Available'),
                'label' => 'Code.Status','translation_domain' => 'code'
            ))
            ->add('frominsertdate','date',array(
                'empty_value' => 'No Value',
                'required'=> false,
                'widget' => 'single_text','format' => 'yyyy/MM/dd',
                'label' => 'Code.FromInsertDate','translation_domain' => 'code'
            ))
            ->add('toinsertdate','date',array(
                'empty_value' => 'No Value',
                'required'=> false,
                'widget' => 'single_text','format' => 'yyyy/MM/dd',
                'label' => 'Code.ToInsertDate','translation_domain' => 'code'
            ))
            ->add('fromexpiredate','date',array(
                'empty_value' => 'No Value',
                'required'=> false,
                'widget' => 'single_text','format' => 'yyyy/MM/dd',
                'label' => 'Code.FromExpireDate','translation_domain' => 'code'
            ))
            ->add('toexpiredate','date',array(
                'empty_value' => 'No Value',
                'required'=> false,
                'widget' => 'single_text','format' => 'yyyy/MM/dd',
                'label' => 'Code.ToExpireDate','translation_domain' => 'code'
            ));
    }

    public function getName()
    {
        return 'CdSearch';
    }
}
