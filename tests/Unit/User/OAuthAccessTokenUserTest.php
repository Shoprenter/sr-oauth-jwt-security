<?php

namespace Shoprenter\OauthJWTSecurity\Tests\Unit\User;

use PHPUnit\Framework\TestCase;
use Shoprenter\OauthJWTSecurity\User\OAuthAccessTokenUser;
use Symfony\Component\Security\Core\User\UserInterface;

class OAuthAccessTokenUserTest extends TestCase
{
    /**
     * Test all the basic getter and setter methods in one test
     */
    public function testUserProperties(): void
    {
        $userId = 'test-user-123';
        $realmName = 'test-realm';
        $scopes = ['read', 'write', 'delete'];
        
        $user = new OAuthAccessTokenUser($userId, $realmName);
        $user->setScopes($scopes);
        
        // Test getters
        $this->assertEquals($userId, $user->getUserIdentifier(), 'User identifier should match');
        $this->assertEquals($realmName, $user->getAuthorizedRealmName(), 'Realm name should match');
        $this->assertEquals($scopes, $user->getScopes(), 'Scopes should match');
        $this->assertEquals(['ROLE_JWT_AUTHENTICATED_USER'], $user->getRoles(), 'Roles should be correct');
    }
    
    /**
     * @dataProvider equalityDataProvider
     */
    public function testIsEqualTo(
        string $thisUserId, 
        string $otherUserId, 
        bool $expectedResult, 
        string $scenario
    ): void {
        $thisUser = new OAuthAccessTokenUser($thisUserId, 'realm');
        
        // Create a mock for the other user
        $otherUser = $this->createMock(UserInterface::class);
        $otherUser->method('getUserIdentifier')->willReturn($otherUserId);
        
        $result = $thisUser->isEqualTo($otherUser);
        
        $this->assertEquals($expectedResult, $result, "Failed scenario: $scenario");
    }

    /**
     * Renamed data provider method to follow naming conventions
     */
    public static function equalityDataProvider(): array
    {
        return [
            'Same user identifier' => [
                'user-123',
                'user-123',
                true,
                'Users with the same identifier should be considered equal'
            ],
            'Different user identifier' => [
                'user-123',
                'user-456',
                false,
                'Users with different identifiers should not be considered equal'
            ],
            'Empty identifiers' => [
                '',
                '',
                true,
                'Users with empty identifiers should still be compared correctly'
            ],
        ];
    }
}
