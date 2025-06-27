<?php

namespace Shoprenter\OauthJWTSecurity\AccessToken;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use stdClass;

class AccessTokenVerifier implements AccesTokenVerifierInterface
{
    private string $publicKeyPath;

    public function __construct(string $publicKeyPath)
    {
        $this->publicKeyPath = $publicKeyPath;
    }

    public function verifyToken(string $accessToken): false|stdClass
    {
        $this->checkFilePath();

        $publicKey = file_get_contents($this->publicKeyPath);

        return JWT::decode(
            $accessToken,
            new Key($publicKey, 'RS256')
        );
    }

    private function checkFilePath(): void
    {
        if (!is_file($this->publicKeyPath)) {
            throw new Exception(
                'Public key file not found at path: "' . $this->publicKeyPath . '"'
            );
        }
    }
}
