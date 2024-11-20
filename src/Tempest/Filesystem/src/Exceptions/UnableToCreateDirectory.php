<?php

declare(strict_types=1);

namespace Tempest\Filesystem\Exceptions;

use RuntimeException;
use Tempest\Filesystem\ErrorContext;

final class UnableToCreateDirectory extends RuntimeException
{
    public static function atPath(string $path, ErrorContext $error): self
    {
        $message = sprintf('Unable to create directory at path [%s]. %s', $path, $error->getMessage());

        return new self($message);
    }
}
