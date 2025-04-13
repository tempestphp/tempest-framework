<?php

namespace Tempest\Support\Filesystem\Exceptions;

use Exception;

final class NotFoundException extends Exception implements FilesystemException
{
    public static function forNode(string $node): static
    {
        return new self(sprintf('Node "%s" is not found.', $node));
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
