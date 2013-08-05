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
            ->add('serial', 'text',array(
                'required'=> false,
                'label' => 'Code.SerialNumber','translation_domain' => 'code'
            ))
            ->add('pin', 'text',array(
                'required'=> false,
                'label' => 'Code.Pin','translation_domain' => 'code
                '))
            ->add('status','choice',array(
                'choices'=> array('3' => 'Code.Sale.All' , '0'=>'Code.Sale.Sale', '1'=>'Code.Sale.NotSale', '2'=>'Code.Sale.DeadBeat'),
                'label' => 'Code.Status','translation_domain' => 'code'
            ))
            ->add('insertdate','date',array(
                'empty_value' => 'No Value',
                'required'=> false,
                'widget' => 'single_text','format' => 'yyyy/MM/dd',
                'label' => 'Code.InsertDate','translation_domain' => 'code'
            ))
            ->add('expiredate','date',array(
                'empty_value' => 'No Value',
                'required'=> false,
                'widget' => 'single_text','format' => 'yyyy/MM/dd',
                'label' => 'Code.ExpireDate','translation_domain' => 'code'
            ));
    }

    public function getName()
    {
        return 'CdSearch';
    }
}
