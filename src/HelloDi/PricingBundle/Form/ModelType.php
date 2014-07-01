<?php

namespace HelloDi\PricingBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ModelType extends AbstractType
{
    private $currencies;

    public function __construct (array $currencies = null)
    {
        $this->currencies = array_combine($currencies, $currencies);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("name",null,array('label' => 'Model Name','required'=>true,'translation_domain' => 'transaction'))
            ->add('currency','choice',array(
                    'label' => 'Currency','translation_domain' => 'accounts',
                    'choices'=>$this->currencies
                ))
            ->add("json",'hidden')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\PricingBundle\Entity\Model'
        ));
    }

    public function getName()
    {
        return '';
    }
}
