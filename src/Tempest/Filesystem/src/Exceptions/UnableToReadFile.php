<?php

declare(strict_types=1);

namespace Tempest\Filesystem\Exceptions;

use RuntimeException;
use Tempest\Filesystem\ErrorContext;

final class UnableToReadFile extends RuntimeException
{
    public static function atPath(string $path, ErrorContext $error): self
    {
        return new self(
            sprintf('Unable to read file at path `%s`. %s', $path, $error->getMessage()),
        );
    }
}
