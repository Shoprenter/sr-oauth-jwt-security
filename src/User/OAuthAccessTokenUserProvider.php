<?php

namespace Shoprenter\OauthJWTSecurity;

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
        $tokenId = unserialize(base64_decode($identifier));

        $token = $attributes['oauth_token_all_claims'] ?? [];

        if (!is_array($token) || empty($token)) {
            throw new UserNotFoundException();
        }

        $clientId = $token['aud'][0];
        $realm = $token['realm'];
        $scopes = $token['scopes'];

        $client = $this->clientProvider->getClient($clientId, $realm);
        if (!$client) {
            throw new UserNotFoundException();
        }

        if (!empty($token['user'])) {
            $user = new OAuthAccessTokenUser(
                sprintf(
                    '%s | user %s | %s (%s)',
                    $realm,
                    $token['user']['id'],
                    $clientId,
                    $client->getName()
                ),
                $realm
            );
            $user->setPermissions($scopes);

            return $user;
        }

        $user = new OAuthAccessTokenUser(
            sprintf('%s (%s)', $clientId, $client->getName()),
            $realm
        );
        $user->setPermissions($scopes);

        return $user;
    }
}
