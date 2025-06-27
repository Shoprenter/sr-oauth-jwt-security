<?php

namespace Shoprenter\OauthJWTSecurity\AccessToken;

interface AccesTokenVerifierInterface
{
    public function verifyToken(string $accessToken);
}
