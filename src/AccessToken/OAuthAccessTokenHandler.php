<?php

namespace Shoprenter\OauthJWTSecurity\AccessToken;

use Exception;
use SensitiveParameter;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class OAuthAccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private AccessTokenVerifier $tokenVerifier
    ) {
    }

    public function getUserBadgeFrom(#[SensitiveParameter] string $accessToken): UserBadge
    {
        try {
            $token = $this->tokenVerifier->verifyToken($accessToken);

            return new UserBadge(
                base64_encode(
                    serialize($token->jti)
                ),
                null,
                [
                    'oauth_token_all_claims' => json_decode(json_encode($token), true)
                ]
            );
        } catch (Exception $exception) {
            throw new BadCredentialsException();
        }
    }
}
