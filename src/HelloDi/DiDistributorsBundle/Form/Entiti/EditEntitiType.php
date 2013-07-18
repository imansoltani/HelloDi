<?php

namespace HelloDi\DiDistributorsBundle\Form\Entiti;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EditEntitiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('entTel1',null,array('label' => 'Tel1:','translation_domain' => 'entity'))
            ->add('entTel2',null,array('label' => 'Tel2:','translation_domain' => 'entity'))
            ->add('entFax',null,array('label' => 'Fax:','translation_domain' => 'entity'))
            ->add('entWebsite',null,array('label' => 'WebSite:','translation_domain' => 'entity'))

        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\DiDistributorsBundle\Entity\Entiti'
        ));
    }

    public function getName()
    {
        return 'EditEntitiType';
    }
}
