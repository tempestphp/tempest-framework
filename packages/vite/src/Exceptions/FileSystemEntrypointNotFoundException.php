<?php

declare(strict_types=1);

namespace Tempest\Vite\Exceptions;

final class FileSystemEntrypointNotFoundException extends EntrypointNotFoundException
{
    public function __construct(string $entrypoint)
    {
        parent::__construct("File `{$entrypoint}` does not exist and cannot be used as an entrypoint.");
    }
}
