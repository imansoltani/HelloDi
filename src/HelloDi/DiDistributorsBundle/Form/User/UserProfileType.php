<?php

namespace HelloDi\DiDistributorsBundle\Form\User;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;

class UserProfileType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('firstname',null,array('label' => 'User.FirstName','translation_domain' => 'user'))
            ->add('lastname',null,array('label' => 'User.LastName','translation_domain' => 'user'))
            ->add('mobile',null,array('label' => 'User.Mobile','translation_domain' => 'user'))
            ->add('language','choice', array(
                'choices' => array(
                    'en' => 'Languages.English',
                    'fa' => 'Languages.Persian',
                    'fr' => 'Languages.French',
                ),
                'required'  => true,
                'label' => 'User.Locale',
                'translation_domain' => 'user'
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\DiDistributorsBundle\Entity\User'
        ));
    }

    public function getName()
    {
        return 'UserProfile';
    }
}
