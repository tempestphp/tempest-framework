<?php

declare(strict_types=1);

namespace Tempest\Vite\Exceptions;

final class ManifestEntrypointNotFoundException extends EntrypointNotFoundException
{
    public function __construct(string $entrypoint)
    {
        parent::__construct("Entrypoint [{$entrypoint}] not found in manifest.");
    }
}
