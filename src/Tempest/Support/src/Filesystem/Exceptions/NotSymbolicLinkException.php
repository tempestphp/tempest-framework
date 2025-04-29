<?php

namespace Tempest\Support\Filesystem\Exceptions;

use Exception;

final class NotSymbolicLinkException extends Exception implements FilesystemException
{
    public static function for(string $path): static
    {
        return new self(sprintf('Path "%s" does not point to a symbolic link.', $path));
    }
}
