<?php

namespace Shoprenter\OauthJWTSecurity\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use User\AuthorizableUserInterface;

class ScopeVoter extends Voter
{
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof AuthorizableUserInterface) {
            return false;
        }

        return in_array($attribute, $user->getScopes());
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return (bool)preg_match('/^[a-zA-Z0-9.:_]+$/', $attribute);
    }
}
