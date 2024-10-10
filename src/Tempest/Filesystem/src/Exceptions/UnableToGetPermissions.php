<?php

declare(strict_types=1);

namespace Tempest\Filesystem\Exceptions;

use RuntimeException;
use Tempest\Filesystem\ErrorContext;

final class UnableToGetPermissions extends RuntimeException
{
    public static function forPath(string $path, ErrorContext $error): self
    {
        $message = sprintf('Unable to get the permissions for path: %s. %s', $path, $error->getMessage());

        return new self($message);
    }
}
