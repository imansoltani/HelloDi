<?php

namespace HelloDi\DiDistributorsBundle\Form\Account;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AccountDistRetailerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('accName','text',array('required'=>true,'label'=>'Name','translation_domain'=>'common',))
            ->add('accTerms','text',array('label'=>'Terms','translation_domain'=>'accounts','required'=>false))
            ->add('accDefaultLanguage','choice',array('label'=>'DefaultLanguage','translation_domain'=>'accounts','choices'=>(array('en'=>'en','fr'=>'fr'))));
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\AccountingBundle\Entity\Account'
        ));
    }

    public function getName()
    {
        return 'AccountDistRetailerType';
    }
}
