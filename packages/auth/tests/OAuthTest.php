<?php

declare(strict_types=1);

namespace Tempest\Auth\Tests;

use League\OAuth2\Client\Provider\Apple;
use League\OAuth2\Client\Provider\Facebook;
use League\OAuth2\Client\Provider\Github;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Instagram;
use League\OAuth2\Client\Provider\LinkedIn;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Auth\OAuth\Config\AppleOAuthConfig;
use Tempest\Auth\OAuth\Config\DiscordOAuthConfig;
use Tempest\Auth\OAuth\Config\FacebookOAuthConfig;
use Tempest\Auth\OAuth\Config\GenericOAuthConfig;
use Tempest\Auth\OAuth\Config\GitHubOAuthConfig;
use Tempest\Auth\OAuth\Config\GoogleOAuthConfig;
use Tempest\Auth\OAuth\Config\InstagramOAuthConfig;
use Tempest\Auth\OAuth\Config\LinkedInOAuthConfig;
use Tempest\Auth\OAuth\OAuthClientInitializer;
use Tempest\Auth\OAuth\OAuthUser;
use Tempest\Container\GenericContainer;
use Tempest\Mapper\MapperConfig;
use Tempest\Mapper\Mappers\ArrayToObjectMapper;
use Tempest\Mapper\ObjectFactory;

final class OAuthTest extends TestCase
{
    private GenericContainer $container {
        get => $this->container ??= new GenericContainer()->addInitializer(OAuthClientInitializer::class);
    }

    private ObjectFactory $factory {
        get => $this->factory ??= new ObjectFactory(new MapperConfig([ArrayToObjectMapper::class]), $this->container);
    }

    #[Test]
    public function github_oauth_config(): void
    {
        $config = new GitHubOAuthConfig(
            clientId: 'github-123',
            clientSecret: 'github-secret', // @mago-expect lint:no-literal-password
            redirectTo: 'https://app.com/auth/github/callback',
            scopes: ['user', 'user:email'],
        );

        $provider = $config->createProvider();
        $url = $provider->getAuthorizationUrl();

        $this->assertInstanceOf(Github::class, $provider);
        $this->assertStringContainsString('github.com', $url);
        $this->assertStringContainsString('github-123', $url);
    }

    #[Test]
    public function google_oauth_config(): void
    {
        $config = new GoogleOAuthConfig(
            clientId: 'google-123',
            clientSecret: 'google-secret', // @mago-expect lint:no-literal-password
            redirectTo: 'https://app.com/auth/google/callback',
        );

        $provider = $config->createProvider();
        $url = $provider->getAuthorizationUrl();

        $this->assertInstanceOf(Google::class, $provider);
        $this->assertStringContainsString('google.com', $url);
        $this->assertStringContainsString('openid', $url);
        $this->assertStringContainsString('google-123', $url);
    }

    #[Test]
    public function facebook_oauth_config(): void
    {
        $config = new FacebookOAuthConfig(
            clientId: 'facebook-123',
            clientSecret: 'facebook-secret', // @mago-expect lint:no-literal-password
            redirectTo: 'https://app.com/auth/facebook/callback',
            scopes: ['email', 'public_profile'],
        );

        $provider = $config->createProvider();
        $url = $provider->getAuthorizationUrl();

        $this->assertInstanceOf(Facebook::class, $provider);
        $this->assertStringContainsString('facebook.com', $url);
        $this->assertStringContainsString('facebook-123', $url);
    }

    #[Test]
    public function instagram_oauth_config(): void
    {
        $config = new InstagramOAuthConfig(
            clientId: 'instagram-123',
            clientSecret: 'instagram-secret', // @mago-expect lint:no-literal-password
            redirectTo: 'https://app.com/auth/instagram/callback',
            scopes: ['user_profile', 'user_media'],
        );

        $provider = $config->createProvider();
        $url = $provider->getAuthorizationUrl();

        $this->assertInstanceOf(Instagram::class, $provider);
        $this->assertStringContainsString('instagram.com', $url);
        $this->assertStringContainsString('instagram-123', $url);
    }

    #[Test]
    public function linkedin_oauth_config(): void
    {
        $config = new LinkedInOAuthConfig(
            clientId: 'linkedin-123',
            clientSecret: 'linkedin-secret', // @mago-expect lint:no-literal-password
            redirectTo: 'https://app.com/auth/linkedin/callback',
            scopes: ['r_liteprofile', 'r_emailaddress'],
        );

        $provider = $config->createProvider();
        $url = $provider->getAuthorizationUrl();

        $this->assertInstanceOf(LinkedIn::class, $provider);
        $this->assertStringContainsString('linkedin.com', $url);
        $this->assertStringContainsString('linkedin-123', $url);
    }

    #[Test]
    public function discord_oauth_config(): void
    {
        $config = new DiscordOAuthConfig(
            clientId: 'discord-123',
            clientSecret: 'discord-secret', // @mago-expect lint:no-literal-password
            redirectTo: '/auth/discord/callback',
            scopes: ['identify', 'email'],
        );

        $provider = $config->createProvider();
        $url = $provider->getAuthorizationUrl();

        $this->assertStringContainsString('discord.com', $url);
        $this->assertStringContainsString('discord-123', $url);
    }

    #[Test]
    public function apple_oauth_config(): void
    {
        $config = new AppleOAuthConfig(
            clientId: 'apple-123',
            teamId: 'apple-team-id',
            keyId: 'apple-key-id',
            keyFile: 'apple-key-file',
            redirectTo: '/auth/apple/callback',
            scopes: ['email', 'name'],
        );

        $provider = $config->createProvider();
        $url = $provider->getAuthorizationUrl();

        $this->assertInstanceOf(Apple::class, $provider);
        $this->assertStringContainsString('apple.com', $url);
        $this->assertStringContainsString('apple-123', $url);
    }

    #[Test]
    public function oauth_user_creation(): void
    {
        $user = new OAuthUser(
            id: '123456',
            email: 'frieren@elven-mage.magic',
            name: 'Frieren the Mage',
            nickname: 'frieren',
            avatar: 'https://example.com/avatar.jpg',
            provider: 'github',
            raw: [
                'id' => 123456,
                'login' => 'frieren',
                'name' => 'Frieren the Mage',
                'email' => 'frieren@elven-mage.magic',
                'avatar_url' => 'https://example.com/avatar.jpg',
            ],
        );

        $this->assertSame('123456', $user->id);
        $this->assertSame('frieren@elven-mage.magic', $user->email);
        $this->assertSame('Frieren the Mage', $user->name);
        $this->assertSame('frieren', $user->nickname);
        $this->assertSame('https://example.com/avatar.jpg', $user->avatar);
        $this->assertSame('github', $user->provider);
        $this->assertArrayHasKey('login', $user->raw);
    }

    #[Test]
    public function generic_config_maps_user_data(): void
    {
        $config = new GenericOAuthConfig(
            clientId: 'client-123',
            clientSecret: 'secret-456', // @mago-expect lint:no-literal-password
            redirectTo: 'https://example.com/callback',
            urlAuthorize: 'https://provider.com/authorize',
            urlAccessToken: 'https://provider.com/token', // @mago-expect lint:no-literal-password
            urlResourceOwnerDetails: 'https://provider.com/user',
        );

        $user = $config->mapUser(
            factory: $this->factory,
            resourceOwner: new ResourceOwner([
                'id' => 789,
                'email' => 'himmel@hero-party.brave',
                'name' => 'Himmel the Hero',
                'username' => 'himmel_hero',
                'avatar_url' => 'https://example.com/himmel.jpg',
            ]),
        );

        $this->assertSame('789', $user->id);
        $this->assertSame('himmel@hero-party.brave', $user->email);
        $this->assertSame('Himmel the Hero', $user->name);
        $this->assertSame('himmel_hero', $user->nickname);
        $this->assertSame('https://example.com/himmel.jpg', $user->avatar);
        $this->assertSame('generic', $user->provider);
    }
}

final class ResourceOwner implements ResourceOwnerInterface
{
    public function __construct(
        private array $data,
    ) {}

    public function getId(): string
    {
        return (string) $this->data['id'];
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
