<?php

namespace HelloDi\MasterBundle\Form;

use HelloDi\AccountingBundle\Entity\Account;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProviderAccountEntityUserType extends AbstractType
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
            ->add('timezone','timezone',array('label' => 'TimeZone','translation_domain' => 'accounts'))
            ->add('currency','choice',array('label' => 'Currency','translation_domain' => 'accounts','choices'=>$this->currencies))

            ->add($builder->create('account',new AccountEntityUserType($this->languages, Account::PROVIDER)))
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
        return 'hellodi_master_bundle_provider_account_entity_user_type';
    }
}
