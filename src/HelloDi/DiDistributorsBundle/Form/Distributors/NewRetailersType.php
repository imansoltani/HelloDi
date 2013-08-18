<?php

namespace HelloDi\DiDistributorsBundle\Form\Distributors;

use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistChildType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistMasterType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistRetailerType;
use HelloDi\DiDistributorsBundle\Form\User\UserRegistrationType;
use HelloDi\DiDistributorsBundle\Form\Userprivilege\UserprivilegeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Symfony\Component\Form\FormTypeInterface;

class NewRetailersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
                  $builder
                      ->add('entName', 'text',array('required'=>true,'label' => 'Name','translation_domain' => 'common'))
                      ->add('entVatNumber', 'text',array('required'=>false,'label' => 'VatNumber','translation_domain' => 'entity'))
                      ->add('entTel1', 'text',array('required'=>false,'label' => 'Tel1','translation_domain' => 'entity'))
                      ->add('entTel2', 'text',array('required'=>false,'label' => 'Tel2','translation_domain' => 'entity'))
                      ->add('entFax', 'text',array('required'=>false,'label' => 'Fax','translation_domain' => 'entity'))
                      ->add('entWebsite', 'text',array('required'=>false,'label' => 'WebSite','translation_domain' => 'entity'))
                      ->add('entCity','text',array('required'=>true,'label' => 'City','translation_domain' => 'entity'))
                      ->add('entNP','text',array('required'=>true,'label' => 'NP','translation_domain' => 'entity'))
                       ->add('entAdrs1','text',array('required'=>true,'label' => 'Adrs1','translation_domain' => 'entity'))
                      ->add('entAdrs2','text',array('required'=>false,'label' => 'Adrs2','translation_domain' => 'entity'))
                      ->add('entAdrs3','text',array('required'=>false,'label' => 'Adrs3','translation_domain' => 'entity'))
                      ->add('Country', 'entity', array('translation_domain' => 'entity',
                              'label'=>'Country',
                              'class'=>'HelloDi\DiDistributorsBundle\Entity\Country','property' => 'name',
                              'query_builder' => function(EntityRepository $er) {
                                  return $er->createQueryBuilder('u')
                                      ->orderBy('u.name', 'ASC');
                              })
                      )


                      ->add('users','collection',array('type' => new \HelloDi\DiDistributorsBundle\Form\User\NewUserType('HelloDi\DiDistributorsBundle\Entity\User',2),'label' => 'Entiti.Users','translation_domain' => 'entity'))
                      ->add('accounts','collection',array('type' => new AccountDistRetailerType() ,'label' => 'Entiti.Accounts','translation_domain' => 'entity'))

                  ;

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\DiDistributorsBundle\Entity\Entiti'
        ));
    }
    public function getName()
    {
        return 'NewRetailers';
    }
}
