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
                'empty_value' => 'All',
                'class' => 'HelloDiAccountingBundle:Account',
                'property' => 'accName',
                'query_builder' => function(EntityRepository $er)
                {
                    return $er->createQueryBuilder('u')->where("u.accType = 1 ");
                },
                'label' => 'Provider','translation_domain' => 'accounts'
            ))
            ->add('item','entity',array(
                'required'=> false,
                'empty_value' => 'All',
                'class' => 'HelloDiDiDistributorsBundle:Item',
                'query_builder' => function(EntityRepository $er)
                {
                    return $er->createQueryBuilder('u')->where("u.itemType != :itemtype ")->setParameter('itemtype','imtu');
                },
                'property' => 'itemName',
                'label' => 'Item','translation_domain' => 'item'
            ))
            ->add('inputFileName', 'entity', array(
                'required'=> false,
                'empty_value' => 'All',
                'class' => 'HelloDiDiDistributorsBundle:Input',
                'property' => 'fileName',
                'label' => 'FileName','translation_domain' => 'code'
            ))
            ->add('fromserial', 'text',array(
                'required'=> false,
                'label' => 'FromSerialNumber','translation_domain' => 'code'
            ))
            ->add('toserial', 'text',array(
                'required'=> false,
                'label' => 'ToSerialNumber','translation_domain' => 'code'
            ))
            ->add('status','choice',array(
                'choices'=> array('3' => 'All' , '0'=>'Unavailable', '1'=>'Available'),
                'label' => 'Status','translation_domain' => 'code'
            ))
            ->add('frominsertdate','date',array(
                'required'=> false,
                'widget' => 'single_text','format' => 'yyyy/MM/dd',
                'label' => 'FromInsertDate','translation_domain' => 'code'
            ))
            ->add('toinsertdate','date',array(
                'required'=> false,
                'widget' => 'single_text','format' => 'yyyy/MM/dd',
                'label' => 'ToInsertDate','translation_domain' => 'code'
            ))
            ->add('fromexpiredate','date',array(
                'required'=> false,
                'widget' => 'single_text','format' => 'yyyy/MM/dd',
                'label' => 'FromExpireDate','translation_domain' => 'code'
            ))
            ->add('toexpiredate','date',array(
                'required'=> false,
                'widget' => 'single_text','format' => 'yyyy/MM/dd',
                'label' => 'ToExpireDate','translation_domain' => 'code'
            ));
    }

    public function getName()
    {
        return 'CdSearch';
    }
}
