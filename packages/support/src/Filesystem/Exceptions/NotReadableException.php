<?php

declare(strict_types=1);

namespace Tempest\Support\Filesystem\Exceptions;

use Exception;

final class NotReadableException extends Exception implements FilesystemException
{
    public static function forFile(string $file): NotReadableException
    {
        return new self(sprintf('File "%s" is not readable.', $file));
    }

    public static function forDirectory(string $directory): NotReadableException
    {
        return new self(sprintf('Directory "%s" is not readable.', $directory));
    }
}
