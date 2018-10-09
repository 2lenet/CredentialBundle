<?php

namespace Lle\OAuthClientBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends Controller
{
    /**
     * Login route that redirects to server.
     * @Route("/login", name="login")
     */
    public function login()
    {
        return $this->get('oauth2.registry')
        ->getClient('2le_oauth')
        ->redirect();
    }

    /**
     * Check route. Should be handled by the GuardAuthenticator.
     * @Route("/login_check", name="login_check")
     */
    public function loginCheck()
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    /**
     * Logout from the server.
     * @Route("/logout_oauth", name="logout_oauth")
     */
    public function logoutOAuth(Request $request)
    {
        $request->getSession()->invalidate();
        return $this->redirect(getenv('DOMAIN') . 'logout');
    }
}
