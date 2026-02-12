<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class KeycloakUserProvider implements UserProviderInterface
{
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = new User();
        $user->setEmail($identifier);
        $user->setRoles(['ROLE_USER']);

        if ($identifier === 'mediatek86@admin.com' || $identifier === 'mediatek86') {
            $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        }

        return $user;
    }
    
    // AJOUTEZ CETTE MÉTHODE ↓
    public function loadUserByUsername(string $username): UserInterface
    {
        // Simplement appeler la nouvelle méthode
        return $this->loadUserByIdentifier($username);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Invalid user class "%s".', get_class($user))
            );
        }

        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }
}