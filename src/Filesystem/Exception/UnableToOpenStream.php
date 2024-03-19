<?php

namespace Tempest\Filesystem\Exception;

use RuntimeException;
use Tempest\Filesystem\StreamAccess;
use Tempest\Filesystem\StreamMode;

final class UnableToOpenStream extends RuntimeException
{
    public static function forLocation(string $location, StreamMode $mode, StreamAccess $access, string $because = ''): self
    {
        return new self(
            sprintf('Unable to open stream for location [%s]. %s', $location, $because)
        );
    }
}