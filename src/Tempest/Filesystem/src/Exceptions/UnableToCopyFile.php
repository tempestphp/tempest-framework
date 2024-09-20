<?php

declare(strict_types=1);

namespace Tempest\Filesystem\Exceptions;

use RuntimeException;

final class UnableToCopyFile extends RuntimeException
{
    public static function fromSourceToDestination(string $source, string $destination): self
    {
        return new self("Unable to copy file from '{$source}' to '{$destination}'");
    }
}
