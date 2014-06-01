<?php

namespace HelloDi\MasterBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ItemDescType extends AbstractType
{
    protected $languages;

    public function __construct($languages)
    {
        $this->languages = array_combine($languages, $languages);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('language', 'choice', array(
                    'choices' => $this->languages,
                    'label' => 'Language','translation_domain' => 'item',
                ))
            ->add('description', null, array(
                    'attr' => array('class' => 'text_editor'),
                    'label' => 'Description', 'translation_domain' => 'item',
                ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\CoreBundle\Entity\ItemDesc'
        ));
    }

    public function getName()
    {
        return 'hellodi_master_bundle_item_desc_type';
    }
}
