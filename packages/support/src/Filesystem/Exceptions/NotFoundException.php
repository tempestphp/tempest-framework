<?php

namespace Tempest\Support\Filesystem\Exceptions;

use Exception;

final class NotFoundException extends Exception implements FilesystemException
{
    public static function forPath(string $path): static
    {
        return new self(sprintf('Path "%s" is not found.', $path));
    }

    public static function forFile(string $file): static
    {
        return new self(sprintf('File "%s" is not found.', $file));
    }

    public static function forDirectory(string $directory): static
    {
        return new self(sprintf('Directory "%s" is not found.', $directory));
    }

    public static function forSymbolicLink(string $symbolic_link): static
    {
        return new self(sprintf('Symbolic link "%s" is not found.', $symbolic_link));
    }
}
