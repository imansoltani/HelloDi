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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RegistrationFormType extends BaseType
{
    private $accountType;
    private $languages;

    /**
     * @param array $languages
     * @param int $accountType
     * @param string $class The User class name
     */
    public function __construct(array $languages, $accountType = null, $class = null)
    {
        parent::__construct($class?:'HelloDi\CoreBundle\Entity\User');
        $this->languages = array_combine($languages, $languages);
        $this->accountType = $accountType;
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

        switch ($this->accountType)
        {
            case Account::RETAILER:
                $builder->add('role', 'choice', array('translation_domain' => 'user',
                    'choices' => array(
                        'ROLE_RETAILER' => 'ROLE_RETAILER',
                        'ROLE_RETAILER_ADMIN' => 'ROLE_RETAILER_ADMIN',
                    ),
                ));
                break;

            case Account::DISTRIBUTOR:
                $builder->add('role', 'choice', array('translation_domain' => 'user',
                    'choices' => array(
                        'ROLE_DISTRIBUTOR' => 'ROLE_DISTRIBUTOR',
                        'ROLE_DISTRIBUTOR_ADMIN' => 'ROLE_DISTRIBUTOR_ADMIN',
                    ),
                )); break;
            case Account::PROVIDER:
                $builder->add('role', 'choice', array('translation_domain' => 'user',
                    'choices' => array(
                        'ROLE_MASTER' => 'ROLE_MASTER',
                        'ROLE_MASTER_ADMIN' => 'ROLE_MASTER_ADMIN',
                    ),
                )); break;
        }
    }

    public function getName()
    {
        return 'hellodi_user_bundle_registration_form_type';
    }
}
