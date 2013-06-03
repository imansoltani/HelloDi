<?php
namespace HelloDi\DiDistributorsBundle\Listener;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationHandler implements AuthenticationSuccessHandlerInterface
{
    protected $router;
    protected $security;

    public function __construct(Router $router, SecurityContext $security)
    {
        $this->router = $router;
        $this->security = $security;
    }

    function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if     ($this->security->isGranted('ROLE_MASTER'))
        {
            $response = new RedirectResponse($this->router->generate('loginsuccess'));
        }
        elseif ($this->security->isGranted('ROLE_DISTRIBUTOR'))
        {
            $response = new RedirectResponse($this->router->generate('distributors_dashboard'));
        }
        elseif ($this->security->isGranted('ROLE_RETAILER'))
        {
            $response = new RedirectResponse($this->router->generate('retailers_dashboard'));
        }
        else
        {
            $referer_url = $request->headers->get('referer');
            $response = new RedirectResponse($referer_url);
        }
        return $response;
    }
}