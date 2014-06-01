<?php
namespace HelloDi\MasterBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ItemType extends AbstractType
{
    private $languages;
    private $currencies;

    public function __construct ($languages, $currencies)
    {
        $this->languages = $languages;
        $this->currencies = array_combine($currencies, $currencies);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('label' => 'Name','translation_domain' => 'item'))
            ->add('faceValue', null, array(
                    'attr'=> array('class'=>'float_validation'),
                    'label' => 'FaceValue','translation_domain' => 'item',
                ))
            ->add('currency', 'choice', array(
                    'empty_value' => '--',
                    'choices'=> $this->currencies,
                    'label' => 'Currency','translation_domain' => 'item',
            ))
            ->add('type', 'choice', array(
                    'empty_value' => '--',
                    'choices'=> array('clcd'=>'Calling_Card','dmtu' =>'Mobile','epmt' =>'E-payment','imtu' =>'IMTU'),
                    'label' => 'ItemType','translation_domain' => 'item',
                    'attr' => array('onchange' => 'changeItemTypes($(this).val())'),
            ))
            ->add('alertMinStock', null, array(
                    'attr'=> array('class'=>'integer_validation'),
                    'label' => 'MinStock','translation_domain' => 'item',
                ))
            ->add('operator', null, array(
                    'empty_value' => '--',
                    'property'=>'NameCarrier',
                    'label' => 'Operator','translation_domain' => 'operator',
            ))
            ->add('country', 'entity', array(
                    'empty_value' => '--',
                    'class'=>'HelloDi\CoreBundle\Entity\Country',
                    'property'=>'name',
                    'label' => 'Country','translation_domain' => 'item',
                ))
            ->add('code', null, array(
                    'attr' => array(
                        'on_refresh_click' => 'refreshItemCode()',
                        'onfocus' => 'if($(this).val()=="")refreshItemCode()',
                    ),
                    'label' => 'ItemCode','translation_domain' => 'item',
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
        return 'hellodi_master_bundle_item_type';
    }
}
