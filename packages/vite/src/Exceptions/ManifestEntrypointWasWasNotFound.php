<?php

declare(strict_types=1);

namespace Tempest\Vite\Exceptions;

final class ManifestEntrypointWasWasNotFound extends EntrypointWasNotFound
{
    public function __construct(string $entrypoint)
    {
        parent::__construct("Entrypoint [{$entrypoint}] not found in manifest.");
    }
}
