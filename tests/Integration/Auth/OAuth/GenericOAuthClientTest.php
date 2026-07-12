<?php

namespace Tests\Tempest\Integration\Auth\OAuth;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use Tempest\Auth\Exceptions\OAuthStateWasInvalid;
use Tempest\Auth\Exceptions\OAuthWasNotConfigured;
use Tempest\Auth\OAuth\Config\GitHubOAuthConfig;
use Tempest\Auth\OAuth\GenericOAuthClient;
use Tempest\Auth\OAuth\OAuthClient;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Session\Session;
use Tempest\Support\Uri;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class GenericOAuthClientTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function creates_oauth_client(): void
    {
        $this->container->config(new GitHubOAuthConfig(
            clientId: 'client-id',
            clientSecret: 'client-secret', // @mago-expect lint:no-literal-password
            redirectTo: '/oauth/callback',
            scopes: ['user:email'],
        ));

        $this->assertInstanceOf(GenericOAuthClient::class, $this->container->get(OAuthClient::class));
    }

    #[Test]
    public function throws_when_no_config(): void
    {
        $this->expectException(OAuthWasNotConfigured::class);

        $this->container->get(OAuthClient::class);
    }

    #[Test]
    public function state_is_set_when_redirect_is_created(): void
    {
        $this->container->config(new GitHubOAuthConfig(
            clientId: 'client-id',
            clientSecret: 'client-secret', // @mago-expect lint:no-literal-password
            redirectTo: '/oauth/callback',
            scopes: ['user:email'],
        ));

        /** @var GenericOAuthClient $oauth */
        $oauth = $this->container->get(OAuthClient::class);

        $oauth->createRedirect();

        $session = $this->container->get(Session::class);

        $this->assertNotNull($session->get($oauth->sessionKey));
    }

    #[Test]
    public function missing_session_state_is_rejected(): void
    {
        $this->container->config(new GitHubOAuthConfig(
            clientId: 'client-id',
            clientSecret: 'client-secret', // @mago-expect lint:no-literal-password
            redirectTo: '/oauth/callback',
            scopes: ['user:email'],
        ));

        /** @var GenericOAuthClient $oauth */
        $oauth = $this->container->get(OAuthClient::class);

        $reflection = new ReflectionClass($oauth);
        $reflection->getProperty('provider')->setValue($oauth, new class extends AbstractProvider {
            public function getBaseAuthorizationUrl(): string
            {
                return 'https://provider.test/authorize';
            }

            public function getBaseAccessTokenUrl(array $params): string
            {
                return 'https://provider.test/token';
            }

            public function getResourceOwnerDetailsUrl(AccessToken $token): string
            {
                return 'https://provider.test/user';
            }

            public function getAccessToken($grant, array $options = [])
            {
                throw new LogicException('Access token should not be requested when state is missing.');
            }

            protected function getDefaultScopes(): array
            {
                return [];
            }

            protected function checkResponse(ResponseInterface $response, $data): void {}

            protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
            {
                throw new LogicException('Resource owner should not be created when state is missing.');
            }
        });

        $this->expectException(OAuthStateWasInvalid::class);

        $oauth->authenticate(
            request: new GenericRequest(
                method: Method::GET,
                uri: Uri\set_query('/oauth/callback', code: 'auth-code'),
            ),
            map: static fn () => throw new LogicException('User should not be mapped when state is missing.'),
        );
    }
}
