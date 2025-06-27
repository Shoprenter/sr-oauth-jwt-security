<?php

namespace Shoprenter\OauthJWTSecurity\AccessToken;

use Exception;
use Psr\Log\LoggerInterface;
use SensitiveParameter;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class OAuthAccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private AccessTokenVerifier $tokenVerifier,
        private ?LoggerInterface    $logger = null
    )
    {
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
            if ($this->logger) {
                $this->logger->error('Failed to verify access token: ' . $exception->getMessage());
            }
            throw new BadCredentialsException();
        }
    }
}
