<?php

declare(strict_types=1);

namespace Tempest\Auth\Tests\OAuth\Testing;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Auth\OAuth\OAuthClient;
use Tempest\Auth\OAuth\OAuthUser;
use Tempest\Auth\OAuth\Testing\OAuthTester;
use Tempest\Auth\OAuth\Testing\TestingOAuthClient;
use Tempest\Container\GenericContainer;

final class OAuthTestingTest extends TestCase
{
    private GenericContainer $container {
        get => $this->container ??= new GenericContainer();
    }

    private OAuthTester $oauth {
        get => new OAuthTester($this->container);
    }

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
        $client = $this->oauth->fake($this->user, $tag);

        $this->assertInstanceOf(TestingOAuthClient::class, $client);
        $this->assertSame($client, $this->container->get(OAuthClient::class, $tag));
    }

    #[Test]
    public function testing_oauth_client_generates_authorization_url(): void
    {
        $client = $this->oauth
            ->fake($this->user)
            ->withBaseUrl('https://provider.test')
            ->withRedirectUri('https://tempest.test/oauth/callback')
            ->withClientId('test');

        $url = $client->getAuthorizationUrl(['scope' => 'user:email']);

        $this->assertStringContainsString('https://tempest.test/oauth/callback', $url);
        $this->assertStringContainsString('https://provider.test', $url);
        $this->assertStringContainsString('client_id=test', $url);
        $this->assertNotEmpty($client->getState());

        $client->assertAuthorizationUrlGenerated(['scope' => 'user:email']);

        $this->assertEquals(1, $client->getAuthorizationUrlCount());
    }

    #[Test]
    public function testing_oauth_client_handles_callback(): void
    {
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
    public function integration(): void
    {
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
            ->withRedirectUri('https://tempest.test/oauth/callback')
            ->withBaseUrl('https://github.test/');

        // login step
        $url = $client->getAuthorizationUrl(scopes: ['user:email']);
        $client->assertAuthorizationUrlGenerated(scopes: ['user:email']);
        $this->assertStringContainsString('https://tempest.test/oauth/callback', $url);
        $this->assertStringContainsString('https://github.test', $url);

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
