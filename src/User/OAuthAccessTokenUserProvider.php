<?php

namespace Shoprenter\OauthJWTSecurity\User;

use Shoprenter\OauthJWTSecurity\User\OAuthAccessTokenUser;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\AttributesBasedUserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @implements AttributesBasedUserProviderInterface<UserInterface>
 */
class OAuthAccessTokenUserProvider implements AttributesBasedUserProviderInterface
{
    public function refreshUser(UserInterface $user): UserInterface
    {
        if ($user instanceof OAuthAccessTokenUser) {
            return $user;
        }

        throw new UnsupportedUserException(
            sprintf('Instances of "%s" are not supported.', get_class($user))
        );
    }

    public function supportsClass(string $class): bool
    {
        return (OAuthAccessTokenUser::class === $class);
    }

    public function loadUserByIdentifier(string $identifier, array $attributes = []): UserInterface
    {
        $token = $attributes['oauth_token_all_claims'] ?? [];

        if (!is_array($token) || empty($token)) {
            throw new UserNotFoundException();
        }

        $clientId = is_array($token['aud']) ? $token['aud'][0] : $token['aud'];
        $realm = $token['realm'];
        $scopes = $token['scopes'];

        if (!empty($token['user'])) {
            $user = new OAuthAccessTokenUser(
                sprintf(
                    '%s | user %s | %s',
                    $realm,
                    $token['user']['id'],
                    $clientId
                ),
                $realm
            );
            $user->setScopes($scopes);

            return $user;
        }

        $user = new OAuthAccessTokenUser(
            $clientId,
            $realm
        );
        $user->setScopes($scopes);

        return $user;
    }
}
