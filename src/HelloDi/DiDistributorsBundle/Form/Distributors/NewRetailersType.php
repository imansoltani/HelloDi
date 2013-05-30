<?php

namespace HelloDi\DiDistributorsBundle\Form\Distributors;

use Doctrine\ORM\EntityRepository;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistChildType;
use HelloDi\DiDistributorsBundle\Form\Account\AccountDistMasterType;
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
                      ->add('entName', 'text',array('data'=>'kavir','required'=>false,'label' => 'Entiti.Name','translation_domain' => 'entity'))
                      ->add('entVatNumber', 'text',array('data'=>221525,'label' => 'Entiti.VatNumber','translation_domain' => 'entity'))
                      ->add('entTel1', 'text',array('data'=>0545263521,'label' => 'Entiti.Tel1','translation_domain' => 'entity'))
                      ->add('entTel2', 'text',array('data'=>221455214,'required'=>false,'label' => 'Entiti.Tel2','translation_domain' => 'entity'))
                      ->add('entFax', 'text',array('data'=>22152,'required'=>false,'label' => 'Entiti.Fax','translation_domain' => 'entity'))
                      ->add('entCity')
                      ->add('entNP')
                       ->add('entAdrs1')
                      ->add('entAdrs2')
                      ->add('Country', 'entity', array(
                              'class'=>'HelloDi\DiDistributorsBundle\Entity\Country','property' => 'name',
                              'query_builder' => function(EntityRepository $er) {
                                  return $er->createQueryBuilder('u')
                                      ->orderBy('u.name', 'ASC');
                              })
                      )


                      ->add('entWebsite', 'text',array('data'=>'www.kavir.com','required'=>false,'label' => 'Entiti.WebSite','translation_domain' => 'entity'))
                      ->add('users','collection',array('type' => new UserRegistrationType('HelloDi\DiDistributorsBundle\Entity\User'),'label' => 'Entiti.Users','translation_domain' => 'entity'))
                      ->add('accounts','collection',array('type' => new AccountDistMasterType(),'label' => 'Entiti.Accounts','translation_domain' => 'entity'))

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
