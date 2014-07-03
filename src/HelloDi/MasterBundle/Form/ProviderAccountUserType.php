<?php

namespace HelloDi\MasterBundle\Form;

use HelloDi\AccountingBundle\Entity\Account;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProviderAccountUserType extends AbstractType
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
            ->add('account',new AccountUserType($this->languages, Account::PROVIDER))
            ->add('timezone','timezone',array('label' => 'TimeZone','translation_domain' => 'accounts'))
            ->add('currency','choice',array('label' => 'Currency','translation_domain' => 'accounts','choices'=>$this->currencies))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\CoreBundle\Entity\Provider'
        ));
    }

    public function getName()
    {
        return 'hellodi_master_bundle_provider_account_user_type';
    }
}
