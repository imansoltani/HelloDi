<?php

namespace HelloDi\AggregatorBundle\Form;

use Doctrine\ORM\EntityRepository;
use HelloDi\CoreBundle\Entity\Item;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TopUpSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('provider','entity',array(
                    'required'=> false,
                    'empty_value' => 'All',
                    'class' => 'HelloDiAggregatorBundle:Provider',
                    'property' => 'name',
                    'label' => 'Provider', 'translation_domain' => 'accounts'
                ))
            ->add('item','entity',array(
                    'required'=> false,
                    'empty_value' => 'All',
                    'class' => 'HelloDiCoreBundle:Item',
                    'property' => 'name',
                    'label' => 'Item', 'translation_domain' => 'item',
                    'query_builder' => function(EntityRepository $er) {
                            return $er->createQueryBuilder('u')
                                ->where('u.type = :type')->setParameter('type', Item::IMTU);
                        }
                ))
            ->add('user','entity',array(
                    'required'=> false,
                    'empty_value' => 'All',
                    'class' => 'HelloDiCoreBundle:User',
                    'property' => 'username',
                    'label' => 'User', 'translation_domain' => 'user',
                    'query_builder' => function(EntityRepository $er) {
                            return $er->createQueryBuilder('u')->innerJoin('u.topUps', 'topUp');
                        }
                ))
            ->add('from', 'date', array(
                    'required' => false,
                    'widget' => 'single_text', 'format' => 'yyyy-MM-dd',
                    'label' => 'From','translation_domain' => 'transaction'
                ))
            ->add('to', 'date', array(
                    'required' => false,
                    'widget' => 'single_text', 'format' => 'yyyy-MM-dd',
                    'label' => 'To','translation_domain' => 'transaction'
                ))
            ->add('status','choice',array(
                    'required'=>false,
                    'empty_value'=>'All',
                    'label'=>'Status','translation_domain' => 'code',
                    'expanded' => true,
                    'choices' => array('1' => 'Done', '0' => 'Error', '2' => 'Pending'),
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
