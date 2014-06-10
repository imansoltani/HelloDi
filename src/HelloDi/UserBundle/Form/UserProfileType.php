<?php

namespace HelloDi\UserBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use HelloDi\UserBundle\Form\Type\ProfileFormType as BaseType;

class UserProfileType extends BaseType
{
    public function __construct()
    {
        parent::__construct('HelloDi\CoreBundle\Entity\User');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('first_name', null, array('label' => 'FirstName', 'translation_domain' => 'user'))
            ->add('last_name', null, array('required' => false, 'label' => 'LastName', 'translation_domain' => 'user'))
            ->add('mobile', null, array('required' => false, 'label' => 'Mobile', 'translation_domain' => 'user'))
            ->add('language', 'choice', array(
                'choices' => array(
                    'en' => 'en',
                    'fr' => 'fr',
                    'de' => 'de',
                    'it' => 'it',
                ),
                'required' => true,
                'label' => 'Language',
                'translation_domain' => 'user'
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\CoreBundle\Entity\User'
        ));
    }

    public function getName()
    {
        return 'hellodi_user_bundle_user_profile_type';
    }
}
