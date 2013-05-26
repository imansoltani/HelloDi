<?php

namespace HelloDi\DiDistributorsBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('itemName',null,array('label' => 'Item.Name','translation_domain' => 'item'))
            ->add('itemFaceValue',null,array('label' => 'Item.FaceValue','translation_domain' => 'item'))
            ->add('itemCurrency','choice',array('choices'=> array('0'=>'USD','1' =>'CHF'),'label' => 'Item.Currency','translation_domain' => 'item'))
            ->add('itemType','choice',array('choices'=> array('1'=>'Item.TypeChioce.Internet','0' =>'Item.TypeChioce.Mobile','2' =>'Item.TypeChioce.Tel'),'label' => 'Item.Type','translation_domain' => 'item'))
            ->add('alertMinStock',null,array('label' => 'Item.AlertMinStock','translation_domain' => 'item'))
            ->add('operator','text',array('label' => 'Item.Operator','translation_domain' => 'item'))
            ->add('itemCode','text',array('label' => 'Item.itemCode','translation_domain' => 'item'))
            ->add('Country','entity',array(
                'class'=>'HelloDi\DiDistributorsBundle\Entity\Country',
                'property'=>'name',
            ))
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
