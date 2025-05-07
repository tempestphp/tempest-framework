<?php

namespace Tempest\Support\Filesystem\Exceptions;

use Exception;

final class NotDirectoryException extends Exception implements FilesystemException
{
    public static function for(string $path): self
    {
        return new self(sprintf('Path "%s" does not point to a directory.', $path));
    }
}
