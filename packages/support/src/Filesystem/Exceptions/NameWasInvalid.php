<?php

namespace Tempest\Support\Filesystem\Exceptions;

use InvalidArgumentException;

final class NameWasInvalid extends InvalidArgumentException implements FilesystemException
{
    private function __construct(string $newName)
    {
        parent::__construct(sprintf('Name "%s" is not a valid file or directory name.', $newName));
    }

    public static function forName(string $newName): self
    {
        return new self($newName);
    }
}
