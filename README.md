# OAuth JWT Security Bundle for Symfony 6.4

This Symfony bundle provides JWT-based OAuth token verification and user authentication for securing your API endpoints.

## Installation

1. Install the bundle using Composer:

```bash
composer require shoprenter/sr-oauth-jwt-security
```

2. Enable the bundle in your `config/bundles.php`:

```php
return [
    // ...
    Shoprenter\OauthJWTSecurity\OauthJWTSecurityBundle::class => ['all' => true],
];
```

3. Configure the bundle in `config/packages/shoprenter.yaml`:

```yaml
shoprenter:
    oauth_jwt_security:
        public_key_path: '%kernel.project_dir%/config/jwt/public.pem'
```

4. Configure security in `config/packages/security.yaml`:

```yaml
security:
    providers:
        jwt_users:
            id: Shoprenter\OauthJWTSecurity\User\OAuthAccessTokenUserProvider

    firewalls:
      jwt_bearer:
        pattern: ^/api
        stateless: true
        access_token:
          provider: jwt_users
          token_handler: App\Infrastructure\Security\AccessTokenHandler\OAuthAccessTokenHandler

    access_control:
        - { path: ^/api, roles: ROLE_JWT_AUTHENTICATED_USER }
```

## Usage

### Securing Endpoints with Scopes

Use voter attributes to check for specific OAuth scopes:

```php
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends AbstractController
{
    public function getProducts(AuthorizationCheckerInterface $authChecker): Response
    {
        // Check if the user has the 'read_products' scope
        if (!$authChecker->isGranted('SCOPE_read_products')) {
            throw $this->createAccessDeniedException('Missing required scope: read_products');
        }
        
        // Your protected code here...
    }
}
```

### Using Annotations/Attributes

With Symfony 6.4, you can use PHP attributes to secure controllers:

```php
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProductController extends AbstractController
{
    #[IsGranted('SCOPE_read_products')]
    public function getProducts(): Response
    {
        // This endpoint requires the 'read_products' scope
        // ...
    }
    
    #[IsGranted('SCOPE_write_products')]
    public function createProduct(): Response
    {
        // This endpoint requires the 'write_products' scope
        // ...
    }
}
```

## Client Authentication

Clients must include a Bearer token in the Authorization header:

```
Authorization: Bearer eyJhbGciOiJSUzI1NiIsInR5...
```

## Error Handling

The authenticator will return a JSON response with a 401 status code if authentication fails.

## Technical Implementation Details

### Service Configuration

This bundle follows Symfony's best practices for service configuration:

- Services are defined in `src/Resources/config/services.yaml`
- The service configuration is loaded by the bundle's extension class (`OauthJWTSecurityExtension`)
- When the bundle is enabled in your application, all services are automatically registered with the Symfony container

This approach ensures that services are properly loaded and configured without requiring manual setup in your application.
