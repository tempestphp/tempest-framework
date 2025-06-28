<?php

namespace Tempest\Cache;

use Exception;

final class CacheUsageWasForbidden extends Exception implements CacheException
{
    public function __construct(
        public readonly ?string $tag = null,
    ) {
        parent::__construct(
            message: $tag
                ? "Cache `{$tag}` is being used without a testing fake."
                : 'Cache is being used without a testing fake.',
        );
    }
}
