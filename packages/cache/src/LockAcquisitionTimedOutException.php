<?php

namespace Tempest\Cache;

use Exception;

final class LockAcquisitionTimedOutException extends Exception implements CacheException
{
    public function __construct(
        public readonly string $key,
    ) {
        parent::__construct(
            message: "Lock with key `{$key}` could not be acquired on time.",
        );
    }
}
