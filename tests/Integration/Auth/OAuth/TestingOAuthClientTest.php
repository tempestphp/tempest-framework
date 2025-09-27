<?php

namespace Tests\Tempest\Integration\Auth\OAuth;

use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Auth\OAuth\Config\GitHubOAuthConfig;
use Tempest\Auth\OAuth\OAuthClient;
use Tempest\Auth\OAuth\OAuthUser;
use Tempest\Auth\OAuth\Testing\TestingOAuthClient;
use Tempest\Core\AppConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class TestingOAuthClientTest extends FrameworkIntegrationTestCase
{
    private OAuthUser $user {
        get => new OAuthUser(
            id: 'frieren',
            email: 'frieren@mage.guild',
            name: 'Frieren',
        );
    }

    #[Test]
    #[TestWith(['github'])]
    #[TestWith([null])]
    public function can_fake_oauth_client(?string $tag): void
    {
        $this->container->config(new GitHubOAuthConfig(
            clientId: 'foo',
            clientSecret: 'bar', // @mago-expect lint:no-literal-password
            redirectTo: '/oauth/github',
            tag: $tag,
        ));

        $client = $this->oauth->fake($this->user, $tag);

        $this->assertInstanceOf(TestingOAuthClient::class, $client);
        $this->assertSame($client, $this->container->get(OAuthClient::class, $tag));
    }

    #[Test]
    public function can_generate_and_test_authorization_url(): void
    {
        $this->container->config(new GitHubOAuthConfig(
            clientId: 'foo',
            clientSecret: 'bar', // @mago-expect lint:no-literal-password
            redirectTo: '/oauth/github',
        ));

        $this->container->config(new AppConfig(
            baseUri: 'https://tempestphp.test',
        ));

        $client = $this->oauth->fake($this->user);

        $url = $client->getAuthorizationUrl(['scope' => 'user:email']);

        $this->assertStringContainsString('https://tempestphp.test/oauth/github', $url);
        $this->assertStringContainsString('https://github.com/login/oauth/authorize', $url);
        $this->assertStringContainsString('client_id=foo', $url);
        $this->assertNotEmpty($client->getState());

        $client->assertAuthorizationUrlGenerated(['scope' => 'user:email']);

        $this->assertEquals(1, $client->getAuthorizationUrlCount());
    }

    #[Test]
    public function can_fetch_user(): void
    {
        $this->container->config(new GitHubOAuthConfig(
            clientId: 'foo',
            clientSecret: 'bar', // @mago-expect lint:no-literal-password
            redirectTo: '/oauth/github',
        ));

        $client = $this->oauth->fake(new OAuthUser(
            id: 'jondoe-123',
            email: 'test@example.com',
            name: 'Jon Doe',
            nickname: 'jondoe',
            avatar: 'https://avatars.githubusercontent.com/u/jondoe-123',
            raw: [],
        ));

        $user = $client->fetchUser('jondoe-123');

        $this->assertEquals('jondoe-123', $user->id);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('Jon Doe', $user->name);
        $this->assertEquals('jondoe', $user->nickname);
        $this->assertEquals('default', $user->provider);

        $client->assertUserFetched('jondoe-123');
        $client->assertAccessTokenRetrieved('jondoe-123');

        $this->assertEquals(1, $client->getCallbackCount());
        $this->assertEquals(1, $client->getAccessTokenCount());
    }

    #[Test]
    public function can_override_client_id(): void
    {
        $this->container->config(new GitHubOAuthConfig(
            clientId: 'foo',
            clientSecret: 'bar', // @mago-expect lint:no-literal-password
            redirectTo: '/oauth/github',
        ));

        $client = $this->oauth
            ->fake($this->user)
            ->withClientId('test');

        $this->assertStringContainsString('client_id=test', $client->getAuthorizationUrl());
    }

    #[Test]
    public function can_override_base_url(): void
    {
        $this->container->config(new GitHubOAuthConfig(
            clientId: 'foo',
            clientSecret: 'bar', // @mago-expect lint:no-literal-password
            redirectTo: '/oauth/github',
        ));

        $client = $this->oauth
            ->fake($this->user)
            ->withBaseUrl('https://provider.test');

        $this->assertStringContainsString('https://provider.test', $client->getAuthorizationUrl());
    }

    #[Test]
    public function can_override_redirect_uri(): void
    {
        $this->container->config(new GitHubOAuthConfig(
            clientId: 'foo',
            clientSecret: 'bar', // @mago-expect lint:no-literal-password
            redirectTo: '/oauth/github',
        ));

        $client = $this->oauth
            ->fake($this->user)
            ->withRedirectUri('/oauth/redirect');

        $this->assertStringContainsString('/oauth/redirect', $client->getAuthorizationUrl());
    }

    #[Test]
    public function can_test_flow(): void
    {
        $this->container->config(new GitHubOAuthConfig(
            clientId: 'foo',
            clientSecret: 'bar', // @mago-expect lint:no-literal-password
            redirectTo: '/oauth/github',
            tag: 'github',
        ));

        $this->container->config(new AppConfig(
            baseUri: 'https://tempestphp.test',
        ));

        $client = $this->oauth
            ->fake(new OAuthUser(
                id: '12345',
                email: 'developer@company.com',
                name: 'Jane Developer',
                nickname: 'janedev',
                avatar: 'https://avatars.githubusercontent.com/u/12345',
                provider: 'github',
                raw: [],
            ), tag: 'github')
            ->withRedirectUri('/oauth/callback');

        // login step
        $url = $client->getAuthorizationUrl(scopes: ['user:email']);
        $client->assertAuthorizationUrlGenerated(scopes: ['user:email']);
        $this->assertStringContainsString('https://tempestphp.test/oauth/callback', $url);
        $this->assertStringContainsString('https://github.com/login/oauth/authorize', $url);

        // callback step
        $user = $client->fetchUser('authorization-code-from-github');
        $client->assertUserFetched('authorization-code-from-github');
        $client->assertAccessTokenRetrieved('authorization-code-from-github');

        // assertions
        $this->assertEquals('12345', $user->id);
        $this->assertEquals('developer@company.com', $user->email);
        $this->assertEquals('Jane Developer', $user->name);
        $this->assertEquals('janedev', $user->nickname);
        $this->assertEquals('github', $user->provider);
    }
}
