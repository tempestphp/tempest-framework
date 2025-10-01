<?php

namespace Tests\Tempest\Integration\Auth\OAuth;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Auth\Exceptions\OAuthProviderWasMissing;
use Tempest\Auth\OAuth\Config\CustomOAuthConfig;
use Tempest\Auth\OAuth\OAuthClient;
use Tempest\Auth\OAuth\OAuthConfig;
use Tempest\Auth\OAuth\OAuthUser;
use Tempest\Mapper\ObjectFactory;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use UnitEnum;

final class OAuthClientTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function throws_exception_when_oauth_provider_is_missing(): void
    {
        $this->expectException(OAuthProviderWasMissing::class);
        $this->expectExceptionMessage('The `non-existing-provider` OAuth provider is missing.');

        $this->container->config(new class implements OAuthConfig {
            public string $provider = 'non-existing-provider';

            public array $scopes = [];

            public string $redirectTo = '';

            public string $clientId = '';

            public null|string|UnitEnum $tag = null;

            public function createProvider(): AbstractProvider
            {
                return new GenericProvider();
            }

            public function mapUser(ObjectFactory $factory, ResourceOwnerInterface $resourceOwner): OAuthUser
            {
                return new OAuthUser('user');
            }
        });

        $this->container->get(OAuthClient::class);
    }
}
