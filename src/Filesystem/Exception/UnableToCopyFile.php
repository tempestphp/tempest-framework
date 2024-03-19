<?php

namespace Tempest\Filesystem\Exception;

use RuntimeException;

final class UnableToCopyFile extends RuntimeException
{
    public static function fromSourceToDestination(string $source, string $destination, string $because = ''): self
    {
        return new self(
            sprintf('Unable to copy file from %s to %s. %s', $source, $destination, $because)
        );
    }
}