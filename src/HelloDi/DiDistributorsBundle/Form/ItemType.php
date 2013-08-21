<?php

namespace HelloDi\DiDistributorsBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ItemType extends AbstractType
{
    protected $langs;

    public function __construct ($langs)
    {
        $this->langs = $langs;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('itemName',null,array('label' => 'Name','translation_domain' => 'item'))
            ->add('itemFaceValue',null,array('label' => 'FaceValue','translation_domain' => 'item'))
            ->add('itemCurrency','choice',array('choices'=> array('USD'=>'USD','CHF' =>'CHF'),'label' => 'Currency','translation_domain' => 'item'))
            ->add('itemType','choice',array('choices'=> array('clcd'=>'Calling_Card','dmtu' =>'Mobile','epmy' =>'E-payment'),'label' => 'Type','translation_domain' => 'item'))
            ->add('alertMinStock',null,array('label' => 'MinStock','translation_domain' => 'item'))
            ->add('operator',null,array(
                'empty_value' => '--',
                'property'=>'name',
                'label' => 'Operator','translation_domain' => 'operator'
            ))
            ->add('itemCode','text',array('label' => 'ItemCode','translation_domain' => 'item'))
            ->add('Country','entity',array(
                'empty_value' => '--',
                'class'=>'HelloDi\DiDistributorsBundle\Entity\Country',
                'property'=>'name',
                'label' => 'Country','translation_domain' => 'item'
            ))
            ->add('ItemDescs','collection', array('type' => new ItemDescType($this->langs)));
        ;

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\DiDistributorsBundle\Entity\Item'
        ));
    }

    public function getName()
    {
        return 'Item';
    }
}
