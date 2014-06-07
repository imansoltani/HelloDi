<?php

namespace HelloDi\MasterBundle\Form;

use HelloDi\UserBundle\Form\RegistrationFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AccountUserType extends AbstractType
{
    private $languages;
    private $type;

    public function __construct (array $languages, $type = null)
    {
        $this->languages = array_combine($languages, $languages);
        $this->type = $type;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name','text',array('label' => 'Name','translation_domain' => 'accounts'))
            ->add('defaultLanguage','choice',array('label' => 'DefaultLanguage','translation_domain' => 'accounts',
                    'choices'=>$this->languages))
            ->add('terms','text',array(
                'label' => 'Terms','translation_domain' => 'accounts',
                'required'=>false,
                'attr'=> array('class'=>'integer_validation'),
            ))
            ->add('users','collection',array(
                'type'=>new RegistrationFormType('HelloDi\CoreBundle\Entity\User',$this->languages,$this->type)
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HelloDi\AccountingBundle\Entity\Account'
        ));
    }

    public function getName()
    {
        return 'hellodi_master_bundle_account_entity_user_type';
    }
}
