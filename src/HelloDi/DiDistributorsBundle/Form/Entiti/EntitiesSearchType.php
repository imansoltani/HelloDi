<?php

namespace HelloDi\DiDistributorsBundle\Form\Entiti;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class EntitiesSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('entName', 'text',array('required'=>false))
            ->add('Country', 'entity', array(
                    'class'=>'HelloDi\DiDistributorsBundle\Entity\Country','property' => 'name',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->orderBy('u.name', 'ASC');
                    })
            )
            ->add('HaveAccount', 'choice',array('choices'=>array(2=>'Both',1=>'Provider',0=>'Distributors')))
        ;
    }

    public function getName()
    {
        return 'EntitiesSearch';
    }
}
