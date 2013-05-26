<?php

namespace HelloDi\DiDistributorsBundle\Form\Userprivilege;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserprivilegeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('privileges','choice',array('choices'=>array(2=>'-',0=>'Seller',1=>'Admin')))
           // ->add('Account')//,'entity',array(
//                'class'=>'HelloDi\DiDistributorsBundle\Entity\Account',
//                'property'=>'accName'

//            ))
         //   ->add('User')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\DiDistributorsBundle\Entity\Userprivilege'
        ));
    }

    public function getName()
    {
        return 'hellodi_didistributorsbundle_userprivilegetype';
    }
}
