<?php

namespace Tempest\Filesystem\Exceptions;

use RuntimeException;
use Tempest\Filesystem\ErrorContext;

final class UnableToMakeDirectory extends RuntimeException
{
    public static function atPath(string $path, ErrorContext $error): self
    {
        $message = sprintf('Unable to make direct at path [%s]. %s', $path, $error->getMessage());

        return new self($message);
    }
}