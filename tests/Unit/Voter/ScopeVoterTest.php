<?php

namespace Shoprenter\OauthJWTSecurity\Tests\Unit\Voter;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Shoprenter\OauthJWTSecurity\User\OAuthAccessTokenUser;
use Shoprenter\OauthJWTSecurity\Voter\ScopeVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ScopeVoterTest extends TestCase
{
    /**
     * @dataProvider supportDataProvider
     */
    public function testSupports(string $attribute, bool $expectedResult, string $scenario): void
    {
        $voter = new ScopeVoter();
        
        // Use reflection to access the protected method
        $method = new ReflectionMethod(ScopeVoter::class, 'supports');
        $result = $method->invoke($voter, $attribute, null);
        
        $this->assertEquals($expectedResult, $result, "Failed scenario: $scenario");
    }
    
    /**
     * @dataProvider voteOnAttributeDataProvider
     */
    public function testVoteOnAttribute(
        string $attribute, 
        array $userScopes, 
        bool $expectedResult, 
        string $scenario
    ): void {
        $voter = new ScopeVoter();
        
        // Create a real OAuthAccessTokenUser which implements both UserInterface and AuthorizableUserInterface
        $user = new OAuthAccessTokenUser('test-user', 'test-realm');
        $user->setScopes($userScopes);
        
        // Create a mock token
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        
        // Use reflection to access the protected method
        $method = new ReflectionMethod(ScopeVoter::class, 'voteOnAttribute');
        $result = $method->invoke($voter, $attribute, null, $token);
        
        $this->assertEquals($expectedResult, $result, "Failed scenario: $scenario");
    }
    
    public function testVoteWithNonAuthorizableUser(): void
    {
        $voter = new ScopeVoter();
        
        // Create a mock for a UserInterface that is not AuthorizableUserInterface
        $nonAuthorizableUser = $this->createMock(UserInterface::class);
        
        // Create a mock token
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($nonAuthorizableUser);
        
        // Use reflection to access the protected method
        $method = new ReflectionMethod(ScopeVoter::class, 'voteOnAttribute');
        $result = $method->invoke($voter, 'any.scope', null, $token);
        
        $this->assertFalse($result, 'Should return false when user is not an AuthorizableUserInterface');
    }
    
    /**
     * Test the full voting process
     * 
     * @dataProvider voteDataProvider
     */
    public function testVote(
        string $attribute, 
        ?array $userScopes, 
        int $expectedVote, 
        string $scenario
    ): void {
        $voter = new ScopeVoter();
        
        // Create a token with an appropriate user
        $token = $this->createMock(TokenInterface::class);
        
        if ($userScopes !== null) {
            // Create a real OAuthAccessTokenUser which implements both UserInterface and AuthorizableUserInterface
            $user = new OAuthAccessTokenUser('test-user', 'test-realm');
            $user->setScopes($userScopes);
            $token->method('getUser')->willReturn($user);
        } else {
            // For the "non-authorizable user" case, return a standard UserInterface
            $nonAuthorizableUser = $this->createMock(UserInterface::class);
            $token->method('getUser')->willReturn($nonAuthorizableUser);
        }
        
        $result = $voter->vote($token, null, [$attribute]);
        
        $this->assertEquals($expectedVote, $result, "Failed scenario: $scenario");
    }

    // Renamed data provider methods to follow naming conventions
    public static function supportDataProvider(): array
    {
        return [
            'Valid scope format' => ['product.read', true, 'Valid scope attribute format should be supported'],
            'Valid scope with dots' => ['product.item.read', true, 'Scope with multiple dots should be supported'],
            'Valid scope with underscores' => ['product_catalog:read', true, 'Scope with underscores should be supported'],
            'Valid scope with numbers' => ['product123.read', true, 'Scope with numbers should be supported'],
            'Valid scope with mixed case' => ['productCatalog.Read', true, 'Scope with mixed case should be supported'],
            'Valid scope with colons' => ['product:read', true, 'Scope with colons should be supported'],
            'Invalid scope with spaces' => ['product read', false, 'Scope with spaces should not be supported'],
            'Invalid scope with special chars' => ['product@read', false, 'Scope with special characters should not be supported'],
            'Empty scope' => ['', false, 'Empty scope should not be supported'],
        ];
    }
    
    public static function voteOnAttributeDataProvider(): array
    {
        return [
            'User has exact scope' => [
                'product.read',
                ['product.read', 'product.write'],
                true,
                'User with the exact scope should be granted access'
            ],
            'User does not have scope' => [
                'product.delete',
                ['product.read', 'product.write'],
                false,
                'User without the required scope should be denied access'
            ],
            'User has multiple scopes' => [
                'product.write',
                ['product.read', 'product.write', 'user.read'],
                true,
                'User with multiple scopes including the required one should be granted access'
            ],
            'Empty scopes list' => [
                'product.read',
                [],
                false,
                'User with empty scopes list should be denied access'
            ],
            'Case-sensitive match' => [
                'Product.Read',
                ['product.read', 'Product.Read'],
                true,
                'Scope matching should be case-sensitive'
            ],
        ];
    }
    
    public static function voteDataProvider(): array
    {
        return [
            'Valid user with matching scope' => [
                'product.read',
                ['product.read', 'product.write'],
                VoterInterface::ACCESS_GRANTED,
                'Valid user with matching scope should be granted access'
            ],
            'Valid user without matching scope' => [
                'product.delete',
                ['product.read', 'product.write'],
                VoterInterface::ACCESS_DENIED,
                'Valid user without matching scope should be denied access'
            ],
            'Non-authorizable user' => [
                'product.read',
                null,
                VoterInterface::ACCESS_DENIED,
                'Non-authorizable user should be denied access'
            ],
            'Unsupported attribute format' => [
                'product read',
                ['product.read'],
                VoterInterface::ACCESS_ABSTAIN,
                'Unsupported attribute format should result in abstain'
            ],
        ];
    }
}
