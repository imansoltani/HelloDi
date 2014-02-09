<?php

namespace HelloDi\DiDistributorsBundle\Form\Account;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EditDistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

                $builder
                    ->add('accTerms','text',array('label'=>'Terms','translation_domain'=>'accounts','required'=>false))
                    ->add('accTimeZone','timezone',array('label'=>'TimeZone','translation_domain'=>'accounts',))


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
        return 'EditDistType';
    }
}
