<?php

namespace HelloDi\DiDistributorsBundle\Form\Account;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AccountType extends AbstractType
{
    private $currencies;

    public function __construct ($_currencies)
    {
        $this->currencies = array_combine($_currencies, $_currencies);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('accName','text',array('required'=>true,'label' => 'Name','translation_domain' => 'accounts'))
            ->add('accCurrency','choice',array('label' => 'Currency','translation_domain' => 'accounts','choices'=>$this->currencies))
            ->add('accTerms','text',array('label' => 'Terms','translation_domain' => 'accounts','required'=>false))
            ->add('accTimeZone','timezone',array('label' => 'TimeZone','translation_domain' => 'accounts'))
            ->add('accDefaultLanguage','choice',array('label' => 'DefaultLanguage','translation_domain' => 'accounts',
                'choices'=>(
                       array('en'=>'en','fr'=>'fr'))));
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
        return 'AccountProv';
    }
}
