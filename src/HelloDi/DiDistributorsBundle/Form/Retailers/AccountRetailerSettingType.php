<?php

namespace HelloDi\DiDistributorsBundle\Form\Retailers;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AccountRetailerSettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('accTerms','text',array())
            ->add('accDefaultLanguage','choice',array('choices'=>(array('en'=>'en','fr'=>'fr')),'preferred_choices'=>array(1)));
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\DiDistributorsBundle\Entity\Account'
        ));
    }

    public function getName()
    {
        return 'AccountDistRetailerType';
    }
}
