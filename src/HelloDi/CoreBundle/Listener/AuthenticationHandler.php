<?php
namespace HelloDi\CoreBundle\Listener;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

/**
 * Class AuthenticationHandler
 * @package HelloDi\CoreBundle\Listener
 */
class AuthenticationHandler implements AuthenticationSuccessHandlerInterface
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected $router;
    /**
     * @var \Symfony\Component\Security\Core\SecurityContext
     */
    protected $security;

    /**
     * @param Router $router
     * @param SecurityContext $security
     */
    public function __construct(Router $router, SecurityContext $security)
    {
        $this->router = $router;
        $this->security = $security;
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @return RedirectResponse
     */
    function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if ($this->security->isGranted('ROLE_MASTER'))
            $response = new RedirectResponse($this->router->generate('hello_di_master_homepage'));
        elseif ($this->security->isGranted('ROLE_DISTRIBUTOR'))
            $response = new RedirectResponse($this->router->generate('hello_di_distributor_homepage'));
        elseif ($this->security->isGranted('ROLE_RETAILER'))
            $response = new RedirectResponse($this->router->generate('hello_di_retailer_homepage'));
        else
            $response = new RedirectResponse($request->headers->get('referer'));
        return $response;
    }
}