<?php

namespace HelloDi\DiDistributorsBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PriceEditType extends AbstractType
{
    private $country;
    public function __construct($country)
    {
        $this->country = $country;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $country = $this->country;
        $builder
            ->add('price','number',array(
                'label' => 'Price','translation_domain' => 'price'
            ))
            ->add('denomination','number', array(
                'label' => 'denomination','translation_domain' => 'price' , 'required'=>false
            ))
            ->add('priceStatus','choice', array(
                'expanded'   => true,
                'choices'    => array(
                    0 => 'Inactive',
                    1 => 'Active',
                ),
                'label' => 'Status','translation_domain' => 'price'
            ))
            ->add('tax', 'entity', array(
                'class' => 'HelloDiDiDistributorsBundle:Tax',
                'property' => 'tax',
                'query_builder' => function(EntityRepository $er) use ($country) {
                    return $er->createQueryBuilder('u')
                        ->where('u.id = 1 or u.Country = :country')->setParameter('country',$country)
                        ->orderBy('u.id', 'DESC');
                },
                'label' => 'Tax','translation_domain' => 'vat'
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\DiDistributorsBundle\Entity\Price'
        ));
    }

    public function getName()
    {
        return 'hellodi_didistributorsbundle_priceedittype';
    }
}
