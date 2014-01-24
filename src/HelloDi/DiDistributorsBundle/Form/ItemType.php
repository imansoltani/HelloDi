<?php

namespace HelloDi\DiDistributorsBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ItemType extends AbstractType
{
    private $langs;
    private $currencies;

    public function __construct ($_langs, $_currencies)
    {
        $this->langs = array_combine($_langs, $_langs);
        $this->currencies = array_combine($_currencies, $_currencies);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('itemName',null,array('label' => 'Name','translation_domain' => 'item'))
            ->add('itemFaceValue',null,array('label' => 'FaceValue','translation_domain' => 'item'))
            ->add('itemCurrency','choice',array(
                'empty_value' => '--',
                'choices'=> $this->currencies,
                'label' => 'Currency',
                'translation_domain' => 'item'
            ))
            ->add('itemType','choice',array(
                'empty_value' => '--',
                'choices'=> array('clcd'=>'Calling_Card','dmtu' =>'Mobile','epmt' =>'E-payment','imtu' =>'IMTU'),
                'label' => 'ItemType','translation_domain' => 'item'
            ))
            ->add('alertMinStock',null,array('label' => 'MinStock','translation_domain' => 'item'))
            ->add('operator',null,array(
                'empty_value' => '--',
                'property'=>'NameCarrier',
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
