<?php

namespace Shoprenter\OauthJWTSecurity\Tests\Unit\User;

use PHPUnit\Framework\TestCase;
use Shoprenter\OauthJWTSecurity\User\OAuthAccessTokenUser;
use Shoprenter\OauthJWTSecurity\User\OAuthAccessTokenUserProvider;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class OAuthAccessTokenUserProviderTest extends TestCase
{
    /**
     * @dataProvider userDataProvider
     */
    public function testLoadUserByIdentifier(
        string $identifier,
        array $attributes,
        ?string $expectedUserIdentifier,
        ?string $expectedExceptionClass,
        string $scenario
    ): void {
        $provider = new OAuthAccessTokenUserProvider();
        
        if ($expectedExceptionClass) {
            $this->expectException($expectedExceptionClass);
            $provider->loadUserByIdentifier($identifier, $attributes);
        } else {
            $user = $provider->loadUserByIdentifier($identifier, $attributes);
            
            $this->assertInstanceOf(OAuthAccessTokenUser::class, $user, "Failed scenario: $scenario");
            $this->assertEquals($expectedUserIdentifier, $user->getUserIdentifier(), "Failed scenario: $scenario");
            $this->assertEquals($attributes['oauth_token_all_claims']['realm'], $user->getAuthorizedRealmName(), "Failed scenario: $scenario");
            $this->assertEquals($attributes['oauth_token_all_claims']['scopes'], $user->getScopes(), "Failed scenario: $scenario");
        }
    }
    
    public function testRefreshUser(): void
    {
        $provider = new OAuthAccessTokenUserProvider();
        
        // Test with an OAuthAccessTokenUser instance
        $user = new OAuthAccessTokenUser('test-user', 'test-realm');
        $refreshedUser = $provider->refreshUser($user);
        $this->assertSame($user, $refreshedUser, 'Should return the same user instance');
        
        // Test with a different UserInterface implementation
        $mockUser = $this->createMock(UserInterface::class);
        $this->expectException(UnsupportedUserException::class);
        $provider->refreshUser($mockUser);
    }
    
    public function testSupportsClass(): void
    {
        $provider = new OAuthAccessTokenUserProvider();
        
        $this->assertTrue($provider->supportsClass(OAuthAccessTokenUser::class), 'Should support OAuthAccessTokenUser class');
        $this->assertFalse($provider->supportsClass(UserInterface::class), 'Should not support other UserInterface implementations');
    }

    /**
     * Renamed data provider method to match PHPUnit naming convention
     */
    public static function userDataProvider(): array
    {
        return [
            'Valid user token' => [
                'test-identifier',
                [
                    'oauth_token_all_claims' => [
                        'jti' => 'token-id',
                        'aud' => 'client-id',
                        'realm' => 'test-realm',
                        'scopes' => ['read', 'write'],
                        'user' => [
                            'id' => 'user-123'
                        ]
                    ]
                ],
                'test-realm | user user-123 | client-id',
                null,
                'Valid user token should create user with composite identifier'
            ],
            'Valid client token' => [
                'test-identifier',
                [
                    'oauth_token_all_claims' => [
                        'jti' => 'token-id',
                        'aud' => 'client-id',
                        'realm' => 'test-realm',
                        'scopes' => ['read', 'write']
                    ]
                ],
                'client-id',
                null,
                'Valid client token should create user with client ID as identifier'
            ],
            'Token with audience array' => [
                'test-identifier',
                [
                    'oauth_token_all_claims' => [
                        'jti' => 'token-id',
                        'aud' => ['client-id', 'another-client'],
                        'realm' => 'test-realm',
                        'scopes' => ['read', 'write']
                    ]
                ],
                'client-id',
                null,
                'Token with audience as array should use first element as client ID'
            ],
            'Missing token claims' => [
                'test-identifier',
                [],
                null,
                UserNotFoundException::class,
                'Missing token claims should throw UserNotFoundException'
            ],
            'Empty token claims' => [
                'test-identifier',
                ['oauth_token_all_claims' => []],
                null,
                UserNotFoundException::class,
                'Empty token claims should throw UserNotFoundException'
            ],
            'Non-array token claims' => [
                'test-identifier',
                ['oauth_token_all_claims' => 'not-an-array'],
                null,
                UserNotFoundException::class,
                'Non-array token claims should throw UserNotFoundException'
            ],
        ];
    }
}
