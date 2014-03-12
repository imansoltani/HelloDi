<?php

namespace HelloDi\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class HelloDiUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
