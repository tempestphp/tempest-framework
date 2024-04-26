<?php

namespace Tempest\Filesystem\Exception;

use RuntimeException;

final class UnableToCloseStream extends RuntimeException
{
    public static function forLocation(string $location, string $because = ''): self
    {
        return new self(
            sprintf('Unable to close stream for location [%s]. %s', $location, $because)
        );
    }
}