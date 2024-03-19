<?php

namespace Tempest\Filesystem\Exception;

use RuntimeException;

final class UnableToMoveFile extends RuntimeException
{
    public static function fromSourceToDestination(string $source, string $destination, string $because = ''): self
    {
        return new self(
            sprintf('Unable to move file from %s to %s. %s', $source, $destination, $because)
        );
    }
}