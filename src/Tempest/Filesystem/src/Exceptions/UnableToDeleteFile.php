<?php

declare(strict_types=1);

namespace Tempest\Filesystem\Exceptions;

use RuntimeException;

final class UnableToDeleteFile extends RuntimeException
{
    public static function atPath(string $path): self
    {
        return new self("Unable to delete file '{$path}'.");
    }
}
