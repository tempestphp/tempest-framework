<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth;

use Tempest\Auth\OAuth\Providers\GithubProvider;
use Tempest\Auth\OAuth\Providers\GoogleProvider;
use Tempest\Support\IsEnumHelper;

enum BuiltInOAuthProvider: string
{
    use IsEnumHelper;

    case GITHUB = 'github';
    case GOOGLE = 'google';

    /**
     * @return class-string<OAuthProvider>
     */
    public function providerClass(): string
    {
        return match ($this) {
            self::GITHUB => GithubProvider::class,
            self::GOOGLE => GoogleProvider::class,
        };
    }

    public static function fromProviderClass(string $providerClass): ?self
    {
        return match ($providerClass) {
            GithubProvider::class => self::GITHUB,
            GoogleProvider::class => self::GOOGLE,
            default => null,
        };
    }
}
