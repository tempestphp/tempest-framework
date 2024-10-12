<?php

declare(strict_types=1);

namespace Tempest\Core;

/** @phpstan-require-implements \Tempest\Core\Discovery */
trait HandlesDiscoveryCache
{
    public function getCachePath(): string
    {
        $parts = explode('\\', static::class);

        $name = array_pop($parts) . '.cache.php';

        return __DIR__ . '/../../../../.cache/tempest/' . $name;
    }
}
