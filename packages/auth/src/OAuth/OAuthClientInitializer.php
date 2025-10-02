<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth;

use Tempest\Auth\Authentication\Authenticator;
use Tempest\Auth\Exceptions\OAuthProviderWasMissing;
use Tempest\Auth\Exceptions\OAuthWasNotConfigured;
use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Singleton;
use Tempest\Http\Session\Session;
use Tempest\Mapper\ObjectFactory;
use Tempest\Reflection\ClassReflector;
use Tempest\Router\UriGenerator;
use UnitEnum;

final class OAuthClientInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, null|string|UnitEnum $tag): bool
    {
        return $class->getType()->matches(OAuthClient::class);
    }

    #[Singleton]
    public function initialize(ClassReflector $class, null|string|UnitEnum $tag, Container $container): OAuthClient
    {
        if (! $container->has(OAuthConfig::class, $tag)) {
            throw OAuthWasNotConfigured::configurationWasMissing($tag);
        }

        $config = $container->get(OAuthConfig::class, $tag);

        if (! class_exists($config->provider)) {
            throw new OAuthProviderWasMissing($config->provider);
        }

        return new GenericOAuthClient(
            config: $config,
            uri: $container->get(UriGenerator::class),
            factory: $container->get(ObjectFactory::class),
            session: $config->get(Session::class),
            authenticator: $container->get(Authenticator::class),
        );
    }
}
