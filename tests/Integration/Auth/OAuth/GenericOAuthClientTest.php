<?php

namespace Tests\Tempest\Integration\Auth\OAuth;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use LogicException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use Tempest\Auth\Exceptions\OAuthStateWasInvalid;
use Tempest\Auth\Exceptions\OAuthTokenCouldNotBeRetrieved;
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

    #[Test]
    public function can_refresh_access_token(): void
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
                Assert::assertSame('refresh_token', (string) $grant);
                Assert::assertSame('my-refresh-token', $options['refresh_token']);

                return new AccessToken([
                    'access_token' => 'new-access-token', // @mago-expect lint:no-literal-password
                    'refresh_token' => 'new-refresh-token', // @mago-expect lint:no-literal-password
                    'expires_in' => 3600,
                ]);
            }

            protected function getDefaultScopes(): array
            {
                return [];
            }

            protected function checkResponse(ResponseInterface $response, $data): void {}

            protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
            {
                throw new LogicException('Resource owner should not be created when refreshing a token.');
            }
        });

        $token = $oauth->refreshAccessToken('my-refresh-token');

        $this->assertSame('new-access-token', $token->getToken());
        $this->assertSame('new-refresh-token', $token->getRefreshToken());
    }

    #[Test]
    public function provider_exception_is_wrapped_when_refreshing(): void
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
                throw new IdentityProviderException(
                    message: 'The refresh token is invalid.',
                    code: 400,
                    response: [],
                );
            }

            protected function getDefaultScopes(): array
            {
                return [];
            }

            protected function checkResponse(ResponseInterface $response, $data): void {}

            protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
            {
                throw new LogicException('Resource owner should not be created when refreshing fails.');
            }
        });

        $this->expectException(OAuthTokenCouldNotBeRetrieved::class);

        $oauth->refreshAccessToken('my-refresh-token');
    }
}
