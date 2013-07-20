<?php

namespace HelloDi\DiDistributorsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class testType extends AbstractType
{
    private $provider;

    public function __construct ($provider)
    {
        $this->provider = $provider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tables', 'choice', array(
        'choices' => $this->provider->getTableNames(),
    ));

    }

    public function getName()
    {
        return 'hellodi_didistributorsbundle_itemdesctype';
    }
}
