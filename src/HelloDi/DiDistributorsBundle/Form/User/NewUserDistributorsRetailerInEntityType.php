<?php

namespace HelloDi\DiDistributorsBundle\Form\User;

use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Form\Userprivilege\UserprivilegeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use HelloDi\DiDistributorsBundle\Entity\Entiti;

class NewUserDistributorsRetailerInEntityType extends BaseType
{
    protected $Entity;
    private $class;
    public function __construct ($class,Entiti $entity,$type)
    {
        parent::__construct($class,$type);
        $this->Entity =$entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
   $entity=$this->Entity;
        parent::buildForm($builder, $options);
        $builder
            ->add('firstname')
            ->add('lastname',null,array('required'=>false))
            ->add('mobile',null,array('required'=>false))
            ->add('language','choice',array('choices'=>array('en'=>'en','fr'=>'fr')))

             ->add('Account', 'entity', array(
                     'class'    => 'HelloDiDiDistributorsBundle:Account',
                     'property' => 'accName',

                     'query_builder' => function(EntityRepository $er)use ($entity) {
                         return $er->createQueryBuilder('u')
                             ->where('u.accType != 1 ')
                             ->andwhere('u.Entiti=:ent')->setParameter('ent',$entity);
                     }
                ,'required'=>true
                 )

            )
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
        return 'UserRegistrationEntity';
    }
}
