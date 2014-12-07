<?php

namespace HelloDi\MasterBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TaxType extends AbstractType
{
    private $countries;

    public function __construct (array $countries)
    {
        $this->countries = $countries;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('vat', null, array(
                    'required'=>true,
                    'label'=>'Tax','translation_domain'=>'vat',
                    'attr'=> array('class'=>'float_validation')
                ))
            ->add('country', 'choice', array(
                    'required'=>true,
                    'label' => 'Country','translation_domain' => 'entity',
                    'choices' => $this->countries
                ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\CoreBundle\Entity\Tax'
        ));
    }

    public function getName()
    {
        return 'hellodi_master_bundle_tax_type';
    }
}
