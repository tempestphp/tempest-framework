<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\Initializers;

use Tempest\Auth\OAuth\OAuth2Provider;
use Tempest\Auth\OAuth\OAuthManager;
use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Http\Session\Session;
use Tempest\HttpClient\HttpClient;
use Tempest\Reflection\ClassReflector;
use UnitEnum;

final class OAuthManagerInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, UnitEnum|string|null $tag): bool
    {
        // We don't check against built-in providers here, as the OAuthManager should be able to work with non-built-in OAuth2Provider.
        return $class->is(OAuthManager::class);
    }

    public function initialize(ClassReflector $class, UnitEnum|string|null $tag, Container $container): OAuthManager
    {
        return new OAuthManager(
            provider: $container->get(OAuth2Provider::class, $tag),
            httpClient: $container->get(HttpClient::class),
            session: $container->get(Session::class)
        );
    }
}