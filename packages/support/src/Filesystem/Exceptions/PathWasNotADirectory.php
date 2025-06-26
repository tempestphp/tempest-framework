<?php

namespace Tempest\Support\Filesystem\Exceptions;

use Exception;

final class PathWasNotADirectory extends Exception implements FilesystemException
{
    public function __construct(string $path)
    {
        parent::__construct(sprintf('Path "%s" does not point to a directory.', $path));
    }
}
