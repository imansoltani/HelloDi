<?php

namespace HelloDi\MasterBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CreditLimitType extends AbstractType
{
    private $currency;

    public function __construct($currency)
    {
        $this->currency = $currency;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount','money', array(
                    'currency' => $this->currency,
                    'label'=>'Amount','translation_domain'=>'transaction',
                    'attr'=> array('class'=>'float_validation'),
                ))
            ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\AccountingBundle\Entity\CreditLimit'
        ));
    }

    public function getName()
    {
        return 'hellodi_master_bundle_credit_limit_type';
    }
}
