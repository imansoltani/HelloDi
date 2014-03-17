<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HelloDi\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RegistrationFormType extends AbstractType
{
    private $class;
    private $type;

    /**
     * @param string $class The User class name
     * @param int $type
     */
    public function __construct($class, $type = null)
    {
        $this->class = $class;
        $this->type = $type;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', null, array('required'=>true,'label' => 'UserName', 'translation_domain' => 'user'))
            ->add('email', 'email', array('required' => false, 'label' => 'Email', 'translation_domain' => 'user'))
            ->add('plainPassword', 'repeated', array('translation_domain' => 'user',
                'type' => 'password',
                'options' => array(),
                'first_options' => array('label' => 'PlainPassword_First'),
                'second_options' => array('label' => 'PlainPassword_Second'),
                'invalid_message' => 'password.mismatch',
            ))
            ->add('enabled', 'choice',
                array(
                 'label' => 'Active','translation_domain' => 'user',
                'choices' =>
                array(
                    0 => 'Disable',
                    1 => 'Enable'
                )




                ));
        if ($this->type == 2) {
            $builder->add('roles', 'collection', array('translation_domain' => 'user',
                'type' => 'choice',
                'options' => array('label' => 'Role',
                    'choices' => array(
                        'ROLE_RETAILER' => 'ROLE_RETAILER',
                        'ROLE_RETAILER_ADMIN' => 'ROLE_RETAILER_ADMIN',
                    ),
                ),
            ));
        } elseif ($this->type == 0) {
            $builder->add('roles', 'collection', array('translation_domain' => 'user',
                'type' => 'choice',
                'options' => array('label' => 'Role',
                    'choices' => array(
                        'ROLE_DISTRIBUTOR' => 'ROLE_DISTRIBUTOR',
                        'ROLE_DISTRIBUTOR_ADMIN' => 'ROLE_DISTRIBUTOR_ADMIN',
                    ),
                ),
            ));
        } elseif ($this->type == 1) {
            $builder->add('roles', 'collection', array('translation_domain' => 'user',
                'type' => 'choice',
                'options' => array('label' => 'Role',
                    'choices' => array(
                        'ROLE_MASTER' => 'ROLE_MASTER',
                        'ROLE_MASTER_ADMIN' => 'ROLE_MASTER_ADMIN',
                    ),
                ),
            ));
        }

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->class,
            'intention'  => 'registration',
        ));
    }

    public function getName()
    {
        return 'fos_user_registration';
    }
}
