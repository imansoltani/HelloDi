<?php

namespace HelloDi\DiDistributorsBundle\Form\User;

use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Form\Userprivilege\UserprivilegeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

class NewUserDistributorsType extends BaseType
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
                'query_builder' => function(EntityRepository$er) {
                    return $er->createQueryBuilder('u')
                        ->where("u.accType = 0 ");
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
        return 'UserRegistrationEntity';
    }
}
