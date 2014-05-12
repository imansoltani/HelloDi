<?php

namespace HelloDi\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ContactUsType
 * @package HelloDi\CoreBundle\Form
 */
class ContactUsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('Name', 'text', array('required' => false))
            ->add('Email', 'email', array('label' => 'Email', 'translation_domain' => 'homepage'))
            ->add('Description', 'textarea', array('label' => 'Description', 'translation_domain' => 'homepage'))
            ->add('Inquiry', 'choice', array(
                'choices' => array(
                    0 => 'Distributors',
                    1 => 'retailers',
                    2 => 'Providers'
                ),
                'label' => 'Select_your_inquiry',
                'translation_domain' => 'homepage'
            ));
        //->add('captcha', 'captcha');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'hellodi_core_bundle_contact_us_type';
    }
}
