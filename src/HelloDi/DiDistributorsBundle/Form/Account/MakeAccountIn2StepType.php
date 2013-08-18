<?php

namespace HelloDi\DiDistributorsBundle\Form\Account;

use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Form\Account\AccountType;
use HelloDi\DiDistributorsBundle\Form\AddressType;
use HelloDi\DiDistributorsBundle\Form\Distributors\NewUserDistributorsType;
use HelloDi\DiDistributorsBundle\Form\User\NewUserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MakeAccountIn2StepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('entName', 'text',array('required'=>false,'label' => 'Name','translation_domain' => 'common'))
            ->add('entVatNumber', 'text',array('label' => 'VatNumber','translation_domain' => 'entity'))
            ->add('entTel1', 'text',array('label' => 'Tel1','translation_domain' => 'entity'))
            ->add('entTel2', 'text',array('required'=>false,'label' => 'Tel2','translation_domain' => 'entity'))
            ->add('entFax', 'text',array('required'=>false,'label' => 'Fax','translation_domain' => 'entity'))
            ->add('entWebsite', 'text',array('required'=>false,'label' => 'WebSite','translation_domain' => 'entity'))
            ->add('entAdrs1', 'text',array('label' => 'Adrs1','translation_domain' => 'entity'))
            ->add('entAdrs2', 'text',array('required'=>false,'label' => 'Adrs2','translation_domain' => 'entity'))
            ->add('entAdrs3', 'text',array('required'=>false,'label' => 'Adrs3','translation_domain' => 'entity'))
            ->add('entCity', 'text',array('required'=>false,'label' => 'City','translation_domain' => 'entity'))
            ->add('entNP', 'text',array('label' => 'NP','translation_domain' => 'entity'))
            ->add('Country','entity',
                array('label' => 'Country','translation_domain' => 'entity',
                    'class'=>'HelloDi\DiDistributorsBundle\Entity\Country',
                    'property'=>'name',))
            ->add('Accounts','collection',array('type'=>new AccountType()))
            ->add('Users','collection',array('type'=>new NewUserType('HelloDi\DiDistributorsBundle\Entity\User',0)))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\DiDistributorsBundle\Entity\Entiti',
        ));
    }

    public function getName()
    {
        return 'EntitiAccountprov';
    }
}