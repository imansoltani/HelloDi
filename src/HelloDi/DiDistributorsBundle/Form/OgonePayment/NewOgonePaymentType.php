<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Fils du Soleil
 * Date: 02.07.13
 * Time: 19:09
 * To change this template use File | Settings | File Templates.
 */

namespace HelloDi\DiDistributorsBundle\Form\OgonePayment;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class NewOgonePaymentType extends AbstractType
{
    private $currency;

    public function __construct ($_currency)
    {
        $this->currency = $_currency;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('paymentAmount','money',array('label'=>'paymentAmount','translation_domain'=>'ogone'))
            ->add('PaymentCurrencyISO', 'hidden',array('data'=>$this->currency))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\AccountingBundle\Entity\OgonePayment'
        ));
    }


    public function getName()
    {
        return 'new_ogone_payment';
    }
}