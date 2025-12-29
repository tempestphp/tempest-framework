<?php

declare(strict_types=1);

namespace Tempest\Core;

use function Tempest\env;

/**
 * Represents the environment in which the application is running.
 */
enum Environment: string
{
    case LOCAL = 'local';
    case STAGING = 'staging';
    case PRODUCTION = 'production';
    case CONTINUOUS_INTEGRATION = 'ci';
    case TESTING = 'testing';

    /**
     * Determines if this environment requires caution for destructive operations.
     */
    public function requiresCaution(): bool
    {
        return in_array($this, [self::PRODUCTION, self::STAGING], strict: true);
    }

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

    public function isContinuousIntegration(): bool
    {
        return $this === self::CONTINUOUS_INTEGRATION;
    }

    public function isTesting(): bool
    {
        return $this === self::TESTING;
    }

    /**
     * Guesses the environment from the `ENVIRONMENT` environment variable.
     */
    public static function guessFromEnvironment(): self
    {
        $value = env('ENVIRONMENT', default: 'local');

        // Can be removed after https://github.com/tempestphp/tempest-framework/pull/1836
        if (is_null($value)) {
            $value = 'local';
        }

        return self::tryFrom($value) ?? throw new EnvironmentValueWasInvalid($value);
    }
}
