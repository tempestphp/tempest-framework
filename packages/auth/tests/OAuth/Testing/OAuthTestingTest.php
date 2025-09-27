<?php

declare(strict_types=1);

namespace Tempest\Auth\Tests\OAuth\Testing;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Auth\OAuth\Config\GitHubOAuthConfig;
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
