<?php

namespace HelloDi\DiDistributorsBundle\Form\Distributors;

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
$role_retailer=array('ROLE_RETAILER');$role_retailer_admin=array('ROLE_RETAILER_ADMIN');

        parent::buildForm($builder, $options);
        $builder
            ->add('firstname')
            ->add('lastname')
            ->add('mobile')
            ->add('language','choice',array('choices'=>array('en'=>'en','fr'=>'fr')))
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
        return 'NewUserRetailers';
    }
}
