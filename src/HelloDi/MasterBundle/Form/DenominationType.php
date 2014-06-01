<?php

namespace HelloDi\MasterBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DenominationType extends AbstractType
{
    private $currencies;

    public function __construct ($currencies)
    {
        $this->currencies = array_combine($currencies, $currencies);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('denomination', 'number', array(
                    'precision' => 2,
                    'attr'=> array('class'=>'float_validation'),
                ))
            ->add('currency', 'choice', array(
                    'empty_value' => '--',
                    'choices'=> $this->currencies,
                    'label' => 'Currency','translation_domain' => 'item',
                ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\CoreBundle\Entity\Denomination'
        ));
    }

    public function getName()
    {
        return 'hellodi_master_bundle_denomination_type';
    }
}
