<?php

declare(strict_types=1);

namespace Tempest\Filesystem\Exceptions;

use RuntimeException;
use Tempest\Filesystem\ErrorContext;

final class UnableToWriteFile extends RuntimeException
{
    public static function atPath(string $path, ErrorContext $error): self
    {
        return new self(
            sprintf('Unable to write to file at path `%s`. %s', $path, $error->getMessage())
        );
    }
}
