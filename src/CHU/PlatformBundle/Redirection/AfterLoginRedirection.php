<?php

namespace CHU\PlatformBundle\Redirection;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class AfterLoginRedirection implements AuthenticationSuccessHandlerInterface
{
    protected $router;
    protected $security;

    /**
     * AfterLoginRedirection constructor.
     * @param Router $router
     * @param AuthorizationChecker $security
     */
    public function __construct(Router $router, AuthorizationChecker $security)
    {
        $this->router = $router;
        $this->security = $security;
    }


    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            $response = new RedirectResponse($this->router->generate('chu_administartion_homepage'));
        } else if($this->security->isGranted('ROLE_MEDECIN')) {
   $response = new RedirectResponse($this->router->generate('chu_medecin_homepage'));
  }else if($this->security->isGranted('ROLE_INFIRMIER')) {
   $response = new RedirectResponse($this->router->generate('chu_infirmier_homepage'));
  }
        else {
            $referer_url = $request->headers->get('referer');

            $response = new RedirectResponse($referer_url);
        }
        return $response;
    }
}

?>