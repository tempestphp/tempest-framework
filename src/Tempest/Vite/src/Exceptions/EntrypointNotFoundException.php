<?php

declare(strict_types=1);

namespace Tempest\Vite\Exceptions;

use Exception;

final class EntrypointNotFoundException extends Exception implements ViteException
{
    public function __construct(string $entrypoint)
    {
        parent::__construct("Entrypoint [{$entrypoint}] not found in manifest.");
    }
}
