<?php

namespace HelloDi\UserBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $registrationFormTypeService = $container->getDefinition('fos_user.registration.form.type');
        $registrationFormTypeService->setClass('HelloDi\UserBundle\Form\Type\RegistrationFormType');

        $profileFormTypeService = $container->getDefinition('fos_user.profile.form.type');
        $profileFormTypeService->setClass('HelloDi\UserBundle\Form\Type\ProfileFormType');
    }
} 