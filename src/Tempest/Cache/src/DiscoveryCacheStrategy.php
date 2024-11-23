<?php

declare(strict_types=1);

namespace Tempest\Cache;

enum DiscoveryCacheStrategy: string
{
    case ALL = 'all';
    case PARTIAL = 'partial';
    case NONE = 'none';
    case INVALID = 'invalid';

    public static function make(mixed $input): self
    {
        return match ($input) {
            true, 'true', '1', 1, 'all' => self::ALL,
            'partial' => self::PARTIAL,
            'invalid' => self::INVALID,
            default => self::NONE,
        };
    }

    public function isEnabled(): bool
    {
        return match ($this) {
            self::ALL, self::PARTIAL => true,
            default => false,
        };
    }

    public function isValid(): bool
    {
        return $this !== self::INVALID;
    }
}
