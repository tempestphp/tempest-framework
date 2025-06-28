<?php

declare(strict_types=1);

namespace Tempest\Vite\Exceptions;

use Exception;

final class FileSystemEntrypointWasNotFoundException extends Exception implements EntrypointNotFoundException
{
    public function __construct(string $entrypoint)
    {
        parent::__construct("File `{$entrypoint}` does not exist and cannot be used as an entrypoint.");
    }
}
