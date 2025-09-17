<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\Initializers;

use Tempest\Auth\OAuth\BuiltInOAuthProvider;
use Tempest\Auth\OAuth\Exceptions\OAuthException;
use Tempest\Auth\OAuth\OAuthProvider;
use Tempest\Auth\OAuth\Providers\GithubProvider;
use Tempest\Auth\OAuth\Providers\GoogleProvider;
use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Http\Session\Session;
use Tempest\Reflection\ClassReflector;
use TypeError;
use UnitEnum;
use function \Tempest\env;

final class BuiltInOAuthProviderInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, UnitEnum|string|null $tag): bool
    {
        return ($class->implements(OAuthProvider::class) || $class->is(OAuthProvider::class)) && $tag === 'from_env_variables';
    }

    public function initialize(ClassReflector $class, UnitEnum|string|null $tag, Container $container): OAuthProvider
    {
        try {
            $providerType = BuiltInOAuthProvider::fromProviderClass($class->getName()) ?? throw new OAuthException(sprintf('No built-in OAuth2 provider found for class: "%s"', $class->getName()));

            return match ($providerType) {
                BuiltInOAuthProvider::GOOGLE => new GoogleProvider(
                        session: $container->get(Session::class),
                    )->configure(
                        clientId: env('GOOGLE_CLIENT_ID'),
                        clientSecret: env('GOOGLE_CLIENT_SECRET'),
                        redirectUri: env('GOOGLE_REDIRECT_URI')
                    ),
                BuiltInOAuthProvider::GITHUB => new GithubProvider(
                        session: $container->get(Session::class),
                    )->configure(
                        clientId: env('GITHUB_CLIENT_ID'),
                        clientSecret: env('GITHUB_CLIENT_SECRET'),
                        redirectUri: env('GITHUB_REDIRECT_URI')
                    ),
                default => throw new OAuthException(sprintf('Cannot initialize "%s" built-in OAuth2 provider', $providerType->name))
            };
        } catch (TypeError $exception) {
            throw new OAuthException(
                sprintf('Failed to initialize OAuth2 provider for "%s". Ensure that the environment variables are set correctly.', $class->getName()),
                previous: $exception
            );
        }
    }
}