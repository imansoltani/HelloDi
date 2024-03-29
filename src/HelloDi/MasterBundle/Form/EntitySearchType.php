<?php

namespace HelloDi\MasterBundle\Form;

use HelloDi\AccountingBundle\Entity\Account;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class EntitySearchType extends AbstractType
{
    private $countries;

    public function __construct (array $countries)
    {
        $this->countries = $countries;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('entityName', 'text',array(
                    'label' => 'EntityName', 'translation_domain' => 'entity',
                    'required' => false,
                ))
            ->add('country', 'choice', array(
                    'label' => 'Country', 'translation_domain' => 'entity',
                    'choices' => $this->countries,
                    'empty_value' => 'All',
                    'required' => false,
                ))
            ->add('accountTypes','choice',array(
                    'label' => 'HaveAccount', 'translation_domain' => 'accounts',
                    'required'  => false,
                    'multiple' => true,
                    'expanded' => true,
                    'choices'   => array(
                        Account::PROVIDER => 'Providers',
                        Account::DISTRIBUTOR => 'Distributors',
                        Account::RETAILER => 'Retailers',
                        Account::API => 'APIs',
                    ),
                ))
            ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'csrf_protection' => false,
            ));
    }

    public function getName()
    {
        return '';
    }
}
