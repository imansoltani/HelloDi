<?php

namespace HelloDi\DiDistributorsBundle\Form;


use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ItmSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name','text',array('required'=> false,'label' => 'Item.Name','translation_domain' => 'item'))
            ->add('type','choice',array('choices'=> array('3' => 'Item.TypeChioce.All' , '1'=>'Item.TypeChioce.Internet','0' =>'Item.TypeChioce.Mobile','2' =>'Item.TypeChioce.Tel'),'label' => 'Item.Type','translation_domain' => 'item'))
            ->add('operator','text',array('required'=>false,'label' => 'Item.Operator','translation_domain' => 'item'))
            ->add('currency','choice',array('choices'=> array('2' => 'Item.All' , '0'=>'USD','1' =>'CHF'),'label' => 'Item.Currency','translation_domain' => 'item'));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\DiDistributorsBundle\Entity\ItmSearch'
        ));
    }

    public function getName()
    {
        return 'ItmSearch';
    }
}
