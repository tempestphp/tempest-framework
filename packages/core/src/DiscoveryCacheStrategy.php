<?php

declare(strict_types=1);

namespace Tempest\Core;

enum DiscoveryCacheStrategy: string
{
    case FULL = 'all';
    case PARTIAL = 'partial';
    case NONE = 'none';
    case INVALID = 'invalid';

    public static function make(mixed $input): self
    {
        return match ($input) {
            true, 'true', '1', 1, 'all', 'full' => self::FULL,
            'partial' => self::PARTIAL,
            'invalid' => self::INVALID,
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
