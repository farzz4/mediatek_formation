<?php
namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OAuthController extends AbstractController
{
    

    /**
     * @Route("/oauth/login", name="oauth_login")
     */
    public function login(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry
            ->getClient('keycloak')
            ->redirect(['openid', 'profile', 'email']);
    }

   

/**
 * @Route("/logout/keycloak", name="logout_keycloak")
 */
public function logout(Request $request): RedirectResponse
{
    // 1. Nettoyage de la session Symfony
    $this->container->get('security.token_storage')->setToken(null);
    $request->getSession()->invalidate();

    // 2. Récupération des variables depuis le .env
    $appUrl   = $_ENV['KEYCLOAK_APP_URL'];
    $realm    = $_ENV['KEYCLOAK_REALM'];
    $clientId = $_ENV['KEYCLOAK_CLIENTID'];

    // 3. Construction dynamique de l'URL
    // On s'assure qu'il n'y a pas de double slash entre l'URL de base et le reste
    $baseUrl = rtrim($appUrl, '/') . '/realms/' . $realm . '/protocol/openid-connect/logout';

    $params = [
        'post_logout_redirect_uri' => 'http://localhost:8000/',
        'client_id'                => $clientId
    ];

    return new RedirectResponse($baseUrl . '?' . http_build_query($params));
}




/**
 * @Route("/oauth/callback", name="oauth_check")
 */
public function connectCheckAction(Request $request): RedirectResponse
{
    // Si on arrive ici APRÈS une déconnexion
    // On redirige vers accueil
    return $this->redirectToRoute('accueil');
}



}