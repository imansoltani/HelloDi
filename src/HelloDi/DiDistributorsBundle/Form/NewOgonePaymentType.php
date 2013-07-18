<?php

namespace HelloDi\DiDistributorsBundle\Form;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;

        class NewOgonePaymentType extends AbstractType
        {
            public function buildForm(FormBuilderInterface $builder, array $options)
            {
        $builder
            ->add('paymentAmount', 'money')
            ->add('paymentCurrencyISO', 'hidden')
        ;
    }

//            public function setDefaultOptions(OptionsResolverInterface $resolver)
//            {
//                $resolver->setRequired(['paymentCurrencyISO']);
//            }



            public function getName()
    {
        return 'NewOgonePayment';
    }
}
