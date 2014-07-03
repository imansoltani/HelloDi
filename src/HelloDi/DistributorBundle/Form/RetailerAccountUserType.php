<?php

namespace HelloDi\DistributorBundle\Form;

use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\MasterBundle\Form\AccountUserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RetailerAccountUserType extends AbstractType
{
    private $languages;

    public function __construct (array $languages)
    {
        $this->languages = array_combine($languages, $languages);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('account',new AccountUserType($this->languages, Account::RETAILER))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\RetailerBundle\Entity\Retailer'
        ));
    }

    public function getName()
    {
        return 'hellodi_distributor_bundle_retailer_account_entity_user_type';
    }
}
