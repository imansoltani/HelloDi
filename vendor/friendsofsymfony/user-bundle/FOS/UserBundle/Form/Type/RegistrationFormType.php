<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RegistrationFormType extends AbstractType
{
    private $class;
    private $type;

    /**
     * @param string $class The User class name
     */
    public function __construct($class, $type = null)
    {
        $this->class = $class;
        $this->type = $type;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', null, array('required'=>true,'label' => 'form.username', 'translation_domain' => 'FOSUserBundle'))
            ->add('email', 'email', array('required' => false, 'label' => 'form.email', 'translation_domain' => 'FOSUserBundle'))
            ->add('plainPassword', 'repeated', array(
                'type' => 'password',
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array('label' => 'form.password'),
                'second_options' => array('label' => 'form.password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
            ))
            ->add('enabled', 'choice',
                array(

                    'label' => 'Enabled:', 'translation_domain' => 'FOSUserBundle',
                'choices' =>
                array(
                   ''=>'select user status',
                    0 => 'Disabled',
                    1 => 'Enabled'

                )

                ,'data'=>''


                ));
        if ($this->type == 2) {
            $builder->add('roles', 'collection', array(
                'type' => 'choice',
                'options' => array('label' => 'Role:',
                    'choices' => array(
                        'ROLE_RETAILER' => 'ROLE_RETAILER',
                        'ROLE_RETAILER_ADMIN' => 'ROLE_RETAILER_ADMIN',
                    ),
                ),
            ));
        } elseif ($this->type == 0) {
            $builder->add('roles', 'collection', array(
                'type' => 'choice',
                'options' => array('label' => 'Role',
                    'choices' => array(
                        'ROLE_DISTRIBUTOR' => 'ROLE_DISTRIBUTOR',
                        'ROLE_DISTRIBUTOR_ADMIN' => 'ROLE_DISTRIBUTOR_ADMIN',
                    ),
                ),
            ));
        } elseif ($this->type == 1) {
            $builder->add('roles', 'collection', array(
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
            'intention' => 'registration',
        ));
    }

    public function getName()
    {
        return 'fos_user_registration';
    }
}
