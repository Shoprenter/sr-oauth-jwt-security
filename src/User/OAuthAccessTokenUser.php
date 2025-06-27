<?php

namespace Shoprenter\OauthJWTSecurity\User;

use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class OAuthAccessTokenUser implements UserInterface, EquatableInterface, AuthorizableUserInterface
{
    private string $userId;

    private array $scopes = [];

    private string $authorizedRealmName;

    public function __construct(string $userId, string $authorizedRealmName)
    {
        $this->userId = $userId;
        $this->authorizedRealmName = $authorizedRealmName;
    }


    public function getRoles(): array
    {
        return ['ROLE_JWT_AUTHENTICATED_USER'];
    }

    public function eraseCredentials(): void
    {
        // Not needed for JWT authentication
    }

    public function isEqualTo(UserInterface $user): bool
    {
        return $user->getUserIdentifier() === $this->getUserIdentifier();
    }

    public function getUserIdentifier(): string
    {
        return $this->userId;
    }

    public function getAuthorizedRealmName(): string
    {
        return $this->authorizedRealmName;
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }
    
    public function setScopes(array $scopes): void
    {
        $this->scopes = $scopes;
    }
}
