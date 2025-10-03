<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\Testing;

use Tempest\Auth\Authentication\Authenticator;
use Tempest\Auth\OAuth\OAuthClient;
use Tempest\Auth\OAuth\OAuthConfig;
use Tempest\Auth\OAuth\OAuthUser;
use Tempest\Container\Container;
use Tempest\Router\UriGenerator;
use UnitEnum;

final readonly class OAuthTester
{
    public function __construct(
        private Container $container,
    ) {}

    /**
     * Forces the usage of a testing OAuth client for the given provider.
     */
    public function fake(OAuthUser $user, null|string|UnitEnum $tag = null): TestingOAuthClient
    {
        $config = $this->container->get(OAuthConfig::class, $tag);
        $uri = $this->container->get(UriGenerator::class);
        $authenticator = $this->container->get(Authenticator::class);

        $this->container->singleton(
            className: OAuthClient::class,
            definition: $client = new TestingOAuthClient($user, $config, $authenticator, $uri),
            tag: $tag,
        );

        return $client;
    }
}
