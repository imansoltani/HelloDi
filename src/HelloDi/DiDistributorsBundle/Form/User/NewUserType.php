<?php

namespace HelloDi\DiDistributorsBundle\Form\User;

use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Form\Userprivilege\UserprivilegeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Symfony\Component\Form\FormTypeInterface;
use HelloDi\DiDistributorsBundle\Entity\Entiti;

class NewUserType extends BaseType
{



    public function buildForm(FormBuilderInterface $builder, array $options,Entiti $ent=null)
    {

        parent::buildForm($builder, $options);
        $builder
            ->add('firstname',null,array(
                'required'=>true,'label'=>'FirstName:'
            ))
            ->add('lastname',null,array('required'=>false,'label'=>'LastName:'))
            ->add('mobile',null,array('required'=>false,'label'=>'Mobile:'))
            ->add('language','choice',array('required'=>true,'label'=>'Language:','choices'=>array('en'=>'en','fr'=>'fr')))


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
