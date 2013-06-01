<?php

namespace HelloDi\DiDistributorsBundle\Form\User;

use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Form\Userprivilege\UserprivilegeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Symfony\Component\Form\FormTypeInterface;

class NewUserRetailersType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('firstname')
            ->add('lastname')
            ->add('mobile')
            ->add('language','choice',array('choices'=>array('en'=>'en','fr'=>'fr')))
            ->add('Account', 'entity', array(
                'class'    => 'HelloDiDiDistributorsBundle:Account',
                'property' => 'accName',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where("u.accType = 2 ");
                }
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\DiDistributorsBundle\Entity\User'
        ));
    }
    public function getName()
    {
        return 'NewUserRetailers';
    }
}
