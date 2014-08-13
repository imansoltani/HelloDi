<?php

namespace HelloDi\AggregatorBundle\Form;

use Doctrine\ORM\EntityRepository;
use HelloDi\AccountingBundle\Entity\Account;
use HelloDi\CoreBundle\Entity\Item;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SaleSearchType extends AbstractType
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
            ->add('itemType', 'choice', array(
                    'required'=> false,
                    'empty_value' => 'All',
                    'choices'=> array('clcd'=>'Calling_Card','dmtu' =>'Mobile','epmt' =>'E-payment','imtu' =>'IMTU'),
                    'label' => 'ItemType','translation_domain' => 'item',
                ))
            ->add('item','entity',array(
                    'required'=> false,
                    'empty_value' => 'All',
                    'class' => 'HelloDiCoreBundle:Item',
                    'property' => 'name',
                    'label' => 'Item', 'translation_domain' => 'item',
                    'query_builder' => function(EntityRepository $er) use ($account) {
                            return $er->createQueryBuilder('u')
                                ->innerJoin('u.prices', 'price')
                                ->where('price.account = :account')->setParameter('account', $account)
                                ->andWhere("u.type != :type")->setParameter('type', Item::IMTU);
                        }
                ))
            ->add('retailer','entity',array(
                    'required'=> false,
                    'empty_value' => 'All',
                    'class' => 'HelloDiRetailerBundle:Retailer',
                    'property' => 'NameWithCurrency',
                    'label' => 'Retailer', 'translation_domain' => 'accounts',
                    'query_builder' => function(EntityRepository $er) use ($account) {
                            return $er->createQueryBuilder('u')
                                ->innerJoin('u.distributor', 'distributor')
                                ->where('distributor.account = :account')->setParameter('account', $account);
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
            ->add('group_by', 'choice', array('translation_domain'=>'transaction',
                    'required' => false,
                    'expanded' => true,
                    'multiple' => true,
                    'choices' => array(
                        1 => 'daily_sales_grouped_by_item_and_retailer',
                    )
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
