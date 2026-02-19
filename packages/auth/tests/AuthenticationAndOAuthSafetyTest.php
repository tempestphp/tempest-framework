<?php

declare(strict_types=1);

namespace Tempest\Auth\Tests;

use League\OAuth2\Client\Provider\GenericProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Tempest\Auth\Authentication\DatabaseAuthenticatableResolver;
use Tempest\Auth\Exceptions\ModelWasNotAuthenticatable;
use Tempest\Auth\OAuth\GenericOAuthClient;

final class AuthenticationAndOAuthSafetyTest extends TestCase
{
    #[Test]
    public function database_authenticatable_resolver_rejects_non_authenticatable_classes(): void
    {
        $resolver = new DatabaseAuthenticatableResolver();
        $resolve = new ReflectionMethod($resolver, 'resolve');

        $this->expectException(ModelWasNotAuthenticatable::class);

        $resolve->invoke($resolver, 1, \stdClass::class);
    }

    #[Test]
    public function generic_oauth_client_state_is_null_before_generating_authorization_url(): void
    {
        $provider = new GenericProvider([
            'clientId' => 'client-id',
            'clientSecret' => 'client-secret', // @mago-expect lint:no-literal-password
            'redirectUri' => 'https://example.com/callback',
            'urlAuthorize' => 'https://provider.test/authorize',
            'urlAccessToken' => 'https://provider.test/token', // @mago-expect lint:no-literal-password
            'urlResourceOwnerDetails' => 'https://provider.test/user',
        ]);

        $reflection = new ReflectionClass(GenericOAuthClient::class);

        /** @var GenericOAuthClient $client */
        $client = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('provider')->setValue($client, $provider);

        $this->assertNull($client->getState());
    }
}
