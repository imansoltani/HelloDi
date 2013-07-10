<?php

namespace HelloDi\DiDistributorsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ItemDescType extends AbstractType
{
    protected $langs;

    public function __construct ($langs)
    {
        $this->langs = $langs;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('desclang','choice', array(
                    'choices'   => $this->langs,
                ))
            ->add('descdesc',null,array('required'=>false))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\DiDistributorsBundle\Entity\ItemDesc'
        ));
    }

    public function getName()
    {
        return 'hellodi_didistributorsbundle_itemdesctype';
    }
}
