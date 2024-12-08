<?php

declare(strict_types=1);

namespace Tempest\Filesystem\Exceptions;

use RuntimeException;
use Tempest\Filesystem\ErrorContext;

final class UnableToCopyFile extends RuntimeException
{
    public static function fromSourceToDestination(string $source, string $destination, ErrorContext $error): self
    {
        return new self(
            sprintf('Unable to copy file from `%s` to `%s`. %s', $source, $destination, $error->getMessage()),
        );
    }
}
