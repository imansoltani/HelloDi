<?php
namespace HelloDi\MasterBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InputType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', 'file', array(
                    'required' => true,
                    'label' => 'File', 'translation_domain' => 'code'
                ))
            ->add('batch', 'text', array(
                    'required' => true,
                    'attr'=> array('class'=>'integer_validation'),
                    'label' => 'Batch','translation_domain' => 'code'
                ))
            ->add('dateProduction', 'date', array(
                    'required' => true,
                    'widget' => 'single_text', 'format' => 'yyyy-MM-dd',
                    'label' => 'DateProduction','translation_domain' => 'code'
                ))
            ->add('dateExpiry', 'date', array(
                    'required' => true,
                    'widget' => 'single_text', 'format' => 'yyyy-MM-dd',
                    'label' => 'DateExpiry','translation_domain' => 'code'
                ))
            ->add('delimiter', 'choice', array(
                    'required' => true,
                    'choices' => array(';' => ';', ',' => ',', ' ' => 'Space', '-' => '-'),
                    'label' => 'Delimiter','translation_domain' => 'code',
                    'mapped' => false
                ))
        ;

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\CoreBundle\Entity\Input'
        ));
    }

    public function getName()
    {
        return 'hellodi_master_bundle_input_type';
    }
}
