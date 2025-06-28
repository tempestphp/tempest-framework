<?php

namespace Tempest\Cache;

use Exception;

final class CacheKeyCouldNotBeIncremented extends Exception implements CacheException
{
    public function __construct(
        public readonly string $key,
    ) {
        parent::__construct(
            message: "Cache key `{$key}` was not a number and could not be incremented or decremented.",
        );
    }
}
