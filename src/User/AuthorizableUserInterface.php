<?php

namespace Shoprenter\OauthJWTSecurity\User;

interface AuthorizableUserInterface
{
    public function getScopes(): array;

    public function getAuthorizedRealmName(): string;
}
