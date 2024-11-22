<?php

declare(strict_types=1);

namespace Tempest\Cache;

enum DiscoveryCacheStrategy: string
{
    case ALL = 'all';
    case PARTIAL = 'partial';
    case NONE = 'none';

    public static function make(mixed $input): self
    {
        return match ($input) {
            true, 'true', '1', 1, 'all' => self::ALL,
            'partial' => self::PARTIAL,
            default => self::NONE,
        };
    }
}
