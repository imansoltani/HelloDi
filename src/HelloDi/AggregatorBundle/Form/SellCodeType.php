<?php

namespace HelloDi\AggregatorBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SellCodeType extends AbstractType
{
    private $languages;

    public function __construct(array $languages)
    {
        $this->languages = array_combine($languages, $languages);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('operator', 'text', array(
                    'required' => true,
                    'disabled' => true,
                    'attr' => array("class"=>"input-large"),
                    'label' => 'Operator','translation_domain' => 'operator'
                ))
            ->add('itemName', 'text', array(
                    'required' => true,
                    'disabled' => true,
                    'attr' => array("class"=>"input-large"),
                    'label' => 'Item','translation_domain' => 'item'
                ))
            ->add('itemType', 'text', array(
                    'required' => true,
                    'disabled' => true,
                    'attr' => array("class"=>"input-large"),
                    'label' => 'ItemType','translation_domain' => 'item'
                ))
            ->add('faceValue', 'text', array(
                    'required' => true,
                    'disabled' => true,
                    'attr' => array("class"=>"input-large"),
                    'label' => 'FaceValue','translation_domain' => 'item'
                ))
            ->add('language', 'choice', array(
                    'required' => true,
                    'label' => 'Language','translation_domain' => 'item',
                    'choices' => $this->languages
                ))
            ->add('count', 'choice', array(
                    'required' => true,
                    'label' => 'Count','translation_domain' => 'code',
                    'choices' => array(1=>1, 2=>2, 3=>3, 5=>5, 10=>10, 20=>20),
                    'attr' => array('onclick'=>'calc_sum()')
                ))
            ->add('sum', 'text', array(
                    'required' => true,
                    'disabled' => true,
                    'attr' => array("class"=>"input-large"),
                    'label' => 'SumValues','translation_domain' => 'price'
                ))
            ->add('item_id', 'hidden', array(
                    'required' => true
                ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return '';
    }
}
