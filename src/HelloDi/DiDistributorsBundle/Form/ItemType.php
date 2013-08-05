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
            ->add('itemName',null,array('label' => 'Item.Name','translation_domain' => 'item'))
            ->add('itemFaceValue',null,array('label' => 'Item.FaceValue','translation_domain' => 'item'))
            ->add('itemCurrency','choice',array('choices'=> array('USD'=>'USD','CHF' =>'CHF'),'label' => 'Item.Currency','translation_domain' => 'item'))
            ->add('itemType','choice',array('choices'=> array('clcd'=>'calling card','dmtu' =>'Mobile','epmy' =>'e-payment'),'label' => 'Item.Type','translation_domain' => 'item'))
            ->add('alertMinStock',null,array('label' => 'Item.AlertMinStock','translation_domain' => 'item'))
            ->add('operator',null,array(
                'property'=>'name',
                'label' => 'Item.Operator','translation_domain' => 'item'
            ))
            ->add('itemCode','text',array('label' => 'Item.itemCode','translation_domain' => 'item'))
            ->add('Country','entity',array(
                'class'=>'HelloDi\DiDistributorsBundle\Entity\Country',
                'property'=>'name',
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
