<?php

namespace HelloDi\MasterBundle\Form;

use HelloDi\AccountingBundle\Entity\Account;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DistributorAccountUserType extends AbstractType
{
    private $currencies;
    private $languages;

    public function __construct (array $currencies, array $languages)
    {
        $this->currencies = array_combine($currencies, $currencies);
        $this->languages = array_combine($languages, $languages);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('account',new AccountUserType($this->languages, Account::DISTRIBUTOR))
            ->add('timezone','timezone',array('label' => 'TimeZone','translation_domain' => 'accounts'))
            ->add('currency','choice',array('label' => 'Currency','translation_domain' => 'accounts','choices'=>$this->currencies))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\DistributorBundle\Entity\Distributor'
        ));
    }

    public function getName()
    {
        return 'hellodi_master_bundle_distributor_account_user_type';
    }
}
