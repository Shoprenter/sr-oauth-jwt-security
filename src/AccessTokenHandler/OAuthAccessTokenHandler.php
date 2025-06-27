<?php

namespace Shoprenter\OauthJWTSecurity\OAuthAccessTokenHandler;

use App\Infrastructure\LeagueOAuthServer\Exception\OAuthServerException;
use App\Infrastructure\LeagueOAuthServer\TokenVerifiers\AccessTokenVerifier;
use App\Infrastructure\Security\User\LegacyDefaultUser;
use Lcobucci\JWT\Token\Plain;
use League\OAuth2\Server\CryptKey;
use SensitiveParameter;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class OAuthAccessTokenHandler implements AccessTokenHandlerInterface
{
    private array $acceptedTokens;

    public function __construct(
        array $acceptedTokens,
        $publicKey,
        private AccessTokenVerifier $tokenVerifier
    ) {
        $this->acceptedTokens = $acceptedTokens;

        if (!$publicKey instanceof CryptKey) {
            $publicKey = new CryptKey($publicKey);
        }

        $this->tokenVerifier->setPublicKey($publicKey);
    }

    public function getUserBadgeFrom(#[SensitiveParameter] string $accessToken): UserBadge
    {
        if (in_array($accessToken, $this->acceptedTokens)) {
            return new UserBadge(
                base64_encode(
                    serialize(LegacyDefaultUser::USER_ID)
                )
            );
        }

        try {
            /** @var Plain $token */
            $token = $this->tokenVerifier->verifyToken($accessToken);

            return new UserBadge(
                base64_encode(
                    serialize($token->claims()->get('jti'))
                ),
                null,
                [
                    'oauth_token_all_claims' => $token->claims()->all()
                ]
            );
        } catch (OAuthServerException $exception) {
            throw new BadCredentialsException();
        }
    }
}
