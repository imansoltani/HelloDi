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
            ->add('firstname',null,array('label' => 'FirstName','translation_domain' => 'user'))
            ->add('lastname',null,array('required'=>false,'label' => 'LastName','translation_domain' => 'user'))
            ->add('mobile',null,array('required'=>false,'label' => 'Mobile','translation_domain' => 'user'))
            ->add('language','choice', array(
                'choices' => array(
                    'en' => 'en',
                    'fr' => 'fr',
                     'de'=>'de'
                ),
                'required'  => true,
                'label' => 'Language',
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
