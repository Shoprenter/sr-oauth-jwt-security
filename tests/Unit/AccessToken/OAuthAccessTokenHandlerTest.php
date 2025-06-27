<?php

namespace Shoprenter\OauthJWTSecurity\Tests\Unit\AccessToken;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Shoprenter\OauthJWTSecurity\AccessToken\AccessTokenVerifier;
use Shoprenter\OauthJWTSecurity\AccessToken\OAuthAccessTokenHandler;
use stdClass;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class OAuthAccessTokenHandlerTest extends TestCase
{
    /**
     * @dataProvider tokenDataProvider
     */
    public function testGetUserBadgeFrom(
        ?stdClass $verifierResult, 
        bool $expectsException, 
        string $scenario
    ): void {
        // Create a mock for AccessTokenVerifier
        $tokenVerifier = $this->createMock(AccessTokenVerifier::class);
        
        if ($expectsException) {
            $tokenVerifier->method('verifyToken')
                ->willThrowException(new \Exception('Token verification failed'));
        } else {
            $tokenVerifier->method('verifyToken')
                ->willReturn($verifierResult);
        }
        
        $handler = new OAuthAccessTokenHandler($tokenVerifier);
        
        if ($expectsException) {
            $this->expectException(BadCredentialsException::class);
            $handler->getUserBadgeFrom('any-token');
        } else {
            $result = $handler->getUserBadgeFrom('any-token');
            
            // Test that the UserBadge identifier is correctly formatted
            $expectedIdentifier = base64_encode(serialize($verifierResult->jti));
            $this->assertEquals($expectedIdentifier, $result->getUserIdentifier(), "Failed scenario: $scenario");
            
            // Verify that user attributes contain the token claims
            $attributesProperty = new ReflectionProperty(UserBadge::class, 'attributes');
            $attributes = $attributesProperty->getValue($result);
            
            $this->assertArrayHasKey('oauth_token_all_claims', $attributes, "Failed scenario: $scenario");
            
            // Convert the stdClass to array for comparison
            $expectedClaims = json_decode(json_encode($verifierResult), true);
            $this->assertEquals($expectedClaims, $attributes['oauth_token_all_claims'], "Failed scenario: $scenario");
        }
    }

    /**
     * Renamed data provider method to follow naming conventions
     */
    public static function tokenDataProvider(): array
    {
        // Valid token scenario
        $validToken = new stdClass();
        $validToken->jti = 'unique-token-id';
        $validToken->sub = '1234567890';
        $validToken->name = 'John Doe';
        $validToken->scopes = ['read', 'write'];
        
        // Another valid token with different claims
        $validToken2 = new stdClass();
        $validToken2->jti = 'another-token-id';
        $validToken2->sub = '9876543210';
        $validToken2->name = 'Jane Smith';
        $validToken2->scopes = ['read'];
        
        return [
            'Valid token' => [$validToken, false, 'Valid token should create UserBadge correctly'],
            'Another valid token' => [$validToken2, false, 'Another valid token should create UserBadge correctly'],
            'Invalid token' => [null, true, 'Invalid token should throw BadCredentialsException'],
        ];
    }
}
