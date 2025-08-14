<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\Initializers;

use Tempest\Auth\OAuth\BuiltInOAuthProvider;
use Tempest\Auth\OAuth\Exceptions\OAuthException;
use Tempest\Auth\OAuth\OAuth2Provider;
use Tempest\Auth\OAuth\Providers\GithubOAuthProvider;
use Tempest\Auth\OAuth\Providers\GoogleOAuthProvider;
use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Reflection\ClassReflector;
use TypeError;
use UnitEnum;
use function \Tempest\env;

final class BuiltInOAuth2ProviderInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, UnitEnum|string|null $tag): bool
    {
        return ($class->implements(OAuth2Provider::class) || $class->is(OAuth2Provider::class)) && BuiltInOAuthProvider::hasValue((string) $tag);
    }

    public function initialize(ClassReflector $class, UnitEnum|string|null $tag, Container $container): OAuth2Provider
    {
        $tag = BuiltInOAuthProvider::from($tag);

        try {
            return match ($tag) {
                BuiltInOAuthProvider::GOOGLE => new GoogleOAuthProvider(
                    clientId: env('GOOGLE_CLIENT_ID'),
                    clientSecret: env('GOOGLE_CLIENT_SECRET'),
                    redirectUri: env('GOOGLE_REDIRECT_URI')
                ),
                BuiltInOAuthProvider::GITHUB => new GithubOAuthProvider(
                    clientId: env('GITHUB_CLIENT_ID'),
                    clientSecret: env('GITHUB_CLIENT_SECRET'),
                    redirectUri: env('GITHUB_REDIRECT_URI')
                ),
                default => throw new OAuthException("Unable to match tag with built-in OAuth2 provider: \"{$tag->value}\""),
            };
        } catch (TypeError $exception) {
            throw new OAuthException(
                "Failed to initialize OAuth2 provider for tag: \"{$tag->value}\". Ensure that the environment variables are set correctly.",
                previous: $exception
            );
        }
    }
}