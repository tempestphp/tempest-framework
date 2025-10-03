<?php

namespace Tests\Tempest\Integration\Auth\OAuth;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Auth\Exceptions\OAuthWasNotConfigured;
use Tempest\Auth\OAuth\Config\GitHubOAuthConfig;
use Tempest\Auth\OAuth\GenericOAuthClient;
use Tempest\Auth\OAuth\OAuthClient;
use Tempest\Http\Session\Session;
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
}
