<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HelloDi\UserBundle\Form;

use HelloDi\AccountingBundle\Entity\Account;
use Symfony\Component\Form\FormBuilderInterface;
use HelloDi\UserBundle\Form\Type\RegistrationFormType as BaseType;

class RegistrationFormType extends BaseType
{
    private $type;
    private $languages;

    /**
     * @param string $class
     * @param array $languages
     * @param int $type
     */
    public function __construct($class, array $languages, $type = null)
    {
        parent::__construct($class);
        $this->languages = array_combine($languages, $languages);
        $this->type = $type;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('firstName',null,array('required'=>true,'label'=>'FirstName','translation_domain' => 'user'))
            ->add('lastName',null,array('required'=>false,'label'=>'LastName','translation_domain' => 'user'))
            ->add('mobile',null,array(
                'required'=>false,
                'label'=>'Mobile','translation_domain' => 'user',
                'attr'=> array('class'=>'tel_validation'),
            ))
            ->add('language','choice',array('required'=>true,'label'=>'Language','translation_domain' => 'user',
                'choices'=>$this->languages
            ))
            ->add('enabled', 'choice',array('label' => 'Active','translation_domain' => 'user','choices' =>array(
                0 => 'Disable',1 => 'Enable'
            )));

        switch ($this->type)
        {
            case Account::RETAILER:
                $builder->add('roles', 'collection', array('translation_domain' => 'user',
                    'type' => 'choice',
                    'options' => array('label' => 'Role',
                        'choices' => array(
                            'ROLE_RETAILER' => 'ROLE_RETAILER',
                            'ROLE_RETAILER_ADMIN' => 'ROLE_RETAILER_ADMIN',
                        ),
                    ),
                )); break;

            case Account::DISTRIBUTOR:
                $builder->add('roles', 'collection', array('translation_domain' => 'user',
                    'type' => 'choice',
                    'options' => array('label' => 'Role',
                        'choices' => array(
                            'ROLE_DISTRIBUTOR' => 'ROLE_DISTRIBUTOR',
                            'ROLE_DISTRIBUTOR_ADMIN' => 'ROLE_DISTRIBUTOR_ADMIN',
                        ),
                    ),
                )); break;
            case Account::PROVIDER:
                $builder->add('roles', 'collection', array('translation_domain' => 'user',
                    'type' => 'choice',
                    'options' => array('label' => 'Role',
                        'choices' => array(
                            'ROLE_MASTER' => 'ROLE_MASTER',
                            'ROLE_MASTER_ADMIN' => 'ROLE_MASTER_ADMIN',
                        ),
                    ),
                )); break;
        }
    }

    public function getName()
    {
        return 'hellodi_user_bundle_registration_form_type';
    }
}
