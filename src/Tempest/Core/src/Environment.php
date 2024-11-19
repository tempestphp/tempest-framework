<?php

declare(strict_types=1);

namespace Tempest\Core;

use function Tempest\env;

enum Environment: string
{
    case LOCAL = 'local';
    case STAGING = 'staging';
    case PRODUCTION = 'production';
    case CI = 'ci';
    case TESTING = 'testing';
    case OTHER = 'other';

    public function isProduction(): bool
    {
        return $this === self::PRODUCTION;
    }

    public function isStaging(): bool
    {
        return $this === self::STAGING;
    }

    public function isLocal(): bool
    {
        return $this === self::LOCAL;
    }

    public function isCI(): bool
    {
        return $this === self::CI;
    }

    public function isTesting(): bool
    {
        return $this === self::TESTING;
    }

    public function isOther(): bool
    {
        return $this === self::OTHER;
    }

    public static function fromEnv(): self
    {
        $value = env('ENVIRONMENT', 'production');

        return self::tryFrom($value) ?? throw new InvalidEnvironment($value);
    }
}
