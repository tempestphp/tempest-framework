<?php

declare(strict_types=1);

namespace Tempest\Filesystem\Exceptions;

use RuntimeException;

final class FileDoesNotExist extends RuntimeException
{
    public static function atPath(string $path): FileDoesNotExist
    {
        return new self("File does not exist at path `{$path}`.");
    }
}
