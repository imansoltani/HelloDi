<?php

namespace HelloDi\DiDistributorsBundle\Form\User;

use HelloDi\DiDistributorsBundle\Form\Userprivilege\UserprivilegeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

class UserRegistrationEntityType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        parent::buildForm($builder, $options);
        $builder
            ->add('name')
            ->add('mobile')
            ->add('Userprivileges','collection',array('type'=>new UserprivilegeType()));


    }


    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\DiDistributorsBundle\Entity\User'
        ));
    }
    public function getName()
    {
        return 'UserRegistrationEntity';
    }
}
