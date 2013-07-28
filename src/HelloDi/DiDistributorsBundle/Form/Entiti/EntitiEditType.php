<?php

namespace HelloDi\DiDistributorsBundle\Form\Entiti;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EntitiEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('entName',null,array('label' => 'Entiti.Name','translation_domain' => 'entity'))
            ->add('entVatNumber',null,array('label' => 'Entiti.VatNumber','translation_domain' => 'entity'))
            ->add('entTel1',null,array('label' => 'Entiti.Tel1','translation_domain' => 'entity'))
            ->add('entTel2',null,array('label' => 'Entiti.Tel2','translation_domain' => 'entity'))
            ->add('entFax',null,array('label' => 'Entiti.Fax','translation_domain' => 'entity'))
            ->add('entWebsite',null,array('label' => 'Entiti.WebSite','translation_domain' => 'entity'))
            ->add('entRegistrationNumber',null,array('label' => 'Entiti.RegistrationNumber','translation_domain' => 'entity'))
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
        return 'EntitiEdit';
    }
}
