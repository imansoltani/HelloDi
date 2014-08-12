<?php

namespace HelloDi\AggregatorBundle\Form;

use Doctrine\ORM\EntityRepository;
use HelloDi\AccountingBundle\Entity\Account;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InputSearchType extends AbstractType
{
    private $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $account = $this->account;

        $builder
            ->add('from', 'date', array(
                    'required' => false,
                    'widget' => 'single_text', 'format' => 'yyyy-MM-dd',
                    'label' => 'FromInsertDate','translation_domain' => 'code'
                ))
            ->add('to', 'date', array(
                    'required' => false,
                    'widget' => 'single_text', 'format' => 'yyyy-MM-dd',
                    'label' => 'ToInsertDate','translation_domain' => 'code'
                ))
            ->add('item', 'entity', array(
                    'required' => false,
                    'empty_value' => 'All',
                    'class' => 'HelloDiCoreBundle:Item',
                    'property' => 'name',
                    'query_builder' => function (EntityRepository $er) use ($account) {
                            return $er->createQueryBuilder('item')
                                ->innerJoin('item.prices', 'price')
                                ->where('price.account = :account')->setParameter('account', $account);
                        },
                    'label' => 'Item','translation_domain' => 'item'
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
