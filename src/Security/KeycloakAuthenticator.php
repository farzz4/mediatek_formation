<?php
namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class KeycloakAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    private $clientRegistry;
    private $entityManager;
    private $router;

    public function __construct(
        ClientRegistry $clientRegistry, 
        EntityManagerInterface $entityManager, 
        RouterInterface $router
    ) {
        $this->clientRegistry = $clientRegistry;
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse(
            $this->router->generate('oauth_login'),
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }

   public function supports(Request $request): ?bool
{
    // 1) NE PAS activer l'authenticator pendant le logout Keycloak
    if ($request->attributes->get('_route') === 'logout_keycloak') {
        return false;
    }

    // 2) NE PAS activer sur la page d'accueil non connectée
    if ($request->attributes->get('_route') === 'accueil') {
        return false;
    }

    // 3) Authenticator actif UNIQUEMENT sur le callback OAuth
    return $request->attributes->get('_route') === 'oauth_check' 
        && $request->isMethod('GET');
}


    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('keycloak');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function() use ($accessToken, $client) {
                /** @var \KnpU\OAuth2ClientBundle\Client\Provider\KeycloakUser $keycloakUser */
                $keycloakUser = $client->fetchUserFromToken($accessToken);

                // Récupérer l'email
                $email = $keycloakUser->getEmail();
                
                if (!$email) {
                    throw new AuthenticationException('Email non fourni par Keycloak');
                }

                // Rechercher l'utilisateur par email
                $userRepository = $this->entityManager->getRepository(User::class);
                $existingUser = $userRepository->findOneBy(['email' => $email]);

                if ($existingUser) {
                    // Mettre à jour le keycloakId si nécessaire
                    if (!$existingUser->getKeycloakId()) {
                        $existingUser->setKeycloakId($keycloakUser->getId());
                        $this->entityManager->flush();
                    }
                    return $existingUser;
                }

                // Créer un nouvel utilisateur
                $user = new User();
                $user->setKeycloakId($keycloakUser->getId());
                $user->setEmail($email);
                $user->setPassword(''); // Pas de mot de passe local
                $user->setRoles(['ROLE_USER']); // Par défaut ROLE_USER, pas ROLE_ADMIN
                
                // Optionnel : définir le nom si disponible
                if (method_exists($keycloakUser, 'getFirstName')) {
                    $user->setFirstname($keycloakUser->getFirstName());
                }
                if (method_exists($keycloakUser, 'getLastName')) {
                    $user->setLastname($keycloakUser->getLastName());
                }

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $user;
            })
        );
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());
        
        // En développement, affichez l'erreur complète
        if ($_ENV['APP_ENV'] === 'dev') {
            return new Response($message . ' - ' . $exception->getMessage(), Response::HTTP_FORBIDDEN);
        }
        
        // En production, redirigez vers la page de login
        return new RedirectResponse($this->router->generate('oauth_login'));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $targetUrl = $this->router->generate('admin.formations');
        return new RedirectResponse($targetUrl);
    }
}