<?php

namespace HelloDi\MasterBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class TaxSearchType extends AbstractType
{
    private $countries;

    public function __construct (array $countries)
    {
        $this->countries = $countries;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', 'choice', array(
                    'choices'   => array(1 => 'Last Amount', 2 => 'History of Country'),
                    'required'  => true,
                    'expanded' => true
                ))
            ->add('country', 'choice', array(
                    'required'=>true,
                    'label' => 'Country','translation_domain' => 'entity',
                    'choices' => $this->countries
                ));
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
