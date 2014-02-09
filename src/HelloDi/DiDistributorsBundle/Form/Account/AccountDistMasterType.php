<?php

namespace HelloDi\DiDistributorsBundle\Form\Account;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AccountDistMasterType extends AbstractType
{
    private $currencies;

    public function __construct ($_currencies)
    {
        $this->currencies = array_combine($_currencies, $_currencies);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('accName','text',array('label'=>'Name'))
            ->add('accBalance','text',array('label'=>'Balance','disabled'=>true))
            ->add('accCurrency','choice',array('label'=>'Currency','choices'=>$this->currencies))
            ->add('accTimeZone','timezone',array('label'=>'TimeZone'))
            ->add('accTerms','text',array('label'=>'Payment condition','required'=>false))
            ->add('accDefaultLanguage','choice',array('choices'=>(array('en'=>'en','fr'=>'fr')),'preferred_choices'=>array(1)));
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
        return 'AccountDistMaster';
    }
}
