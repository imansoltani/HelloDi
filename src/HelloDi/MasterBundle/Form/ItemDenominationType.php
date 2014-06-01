<?php

namespace HelloDi\MasterBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ItemDenominationType extends AbstractType
{
    private $currencies;

    public function __construct ($currencies)
    {
        $this->currencies = $currencies;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("denominations", 'collection', array(
                    'type'=> new DenominationType($this->currencies),
                    'by_reference' => false,
                    'allow_add'=>true,
                    'allow_delete'=>false
                ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\CoreBundle\Entity\Item'
        ));
    }

    public function getName()
    {
        return 'hellodi_master_bundle_item_denomination_type';
    }
}
