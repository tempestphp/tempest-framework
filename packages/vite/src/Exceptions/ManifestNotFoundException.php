<?php

declare(strict_types=1);

namespace Tempest\Vite\Exceptions;

use Exception;

final class ManifestNotFoundException extends Exception implements ViteException
{
    public function __construct(string $path)
    {
        parent::__construct("Vite manifest not found at [{$path}].");
    }
}
