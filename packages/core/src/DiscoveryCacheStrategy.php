<?php

declare(strict_types=1);

namespace Tempest\Core;

use function Tempest\env;

enum DiscoveryCacheStrategy: string
{
    /**
     * Discovery is completely cached and will not be re-run.
     */
    case FULL = 'full';

    /**
     * Vendors are cached, application discovery is re-run.
     */
    case PARTIAL = 'partial';

    /**
     * Discovery is not cached.
     */
    case NONE = 'none';

    /**
     * There is a mismatch between the stored strategy and the resolved strategy, discovery is considered as not cached.
     */
    case INVALID = 'invalid';

    public static function resolveFromEnvironment(): self
    {
        $environment = Environment::guessFromEnvironment();

        return static::resolveFromInput(env('DISCOVERY_CACHE', default: match (true) {
            $environment->requiresCaution() => true,
            $environment->isLocal() => 'partial',
            default => false,
        }));
    }

    public static function resolveFromInput(mixed $input): self
    {
        return match ($input) {
            true, 'true', '1', 1, 'all', 'full' => self::FULL,
            'partial' => self::PARTIAL,
            null, 'invalid' => self::INVALID,
            default => self::NONE,
        };
    }

    public function isEnabled(): bool
    {
        return match ($this) {
            self::FULL, self::PARTIAL => true,
            default => false,
        };
    }

    public function isValid(): bool
    {
        return $this !== self::INVALID;
    }
}
