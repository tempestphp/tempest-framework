<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\Testing;

use Tempest\Auth\OAuth\OAuthClient;
use Tempest\Auth\OAuth\OAuthUser;
use Tempest\Container\Container;
use Tempest\Support\Str;
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
        $this->container->singleton(
            className: OAuthClient::class,
            definition: $client = new TestingOAuthClient($user, match (true) {
                is_string($tag) => $tag,
                $tag instanceof UnitEnum => Str\to_kebab_case($tag->name),
                default => 'default',
            }),
            tag: $tag,
        );

        return $client;
    }
}
